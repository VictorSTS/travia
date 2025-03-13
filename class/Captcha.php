<?php

class Captcha
{
    private $config;
    private $host;
    private $debug;

    /**
     * Captcha constructor.
     * @param bool $debug Enable debug mode
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
        
        // Load configuration
        $configFile = __DIR__ . '/../config/captcha.json';
        if (file_exists($configFile)) {
            $this->config = json_decode(file_get_contents($configFile), true);
        } else {
            // Default configuration if file doesn't exist
            $this->config = [
                'simple_captcha' => [
                    'enabled' => true,
                    'min_number' => 1,
                    'max_number' => 10
                ],
                'recaptcha' => [
                    'enabled' => false
                ]
            ];
        }

        // Determine host
        $this->host = $this->determineHost();
        
        // Initialize debug log if debug mode is enabled
        if ($this->debug) {
            $this->logDebug('Captcha initialized with host: ' . $this->host);
            $this->logDebug('Session ID: ' . session_id());
            $this->logDebug('Simple CAPTCHA enabled: ' . ($this->config['simple_captcha']['enabled'] ? 'true' : 'false'));
            $this->logDebug('reCAPTCHA enabled: ' . ($this->config['recaptcha']['enabled'] ? 'true' : 'false'));
        }
    }

    /**
     * Log debug message
     * @param string $message Debug message
     */
    private function logDebug($message)
    {
        if (!$this->debug) {
            return;
        }
        
        $logFile = __DIR__ . '/../logs/captcha_debug.log';
        $logDir = dirname($logFile);
        
        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Determine the current host to use appropriate reCAPTCHA keys
     * @return string Host identifier ('localhost' or 'univ-eiffel')
     */
    private function determineHost()
    {
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        
        if (strpos($serverName, 'localhost') !== false || $serverName === '127.0.0.1') {
            return 'localhost';
        } elseif (strpos($serverName, 'univ-eiffel.fr') !== false) {
            return 'univ-eiffel';
        }
        
        // Default to localhost if can't determine
        return 'localhost';
    }

    /**
     * Generate simple CAPTCHA
     * @return array CAPTCHA data
     */
    public function generateSimpleCaptcha()
    {
        if (!$this->config['simple_captcha']['enabled']) {
            return ['enabled' => false];
        }

        $min = $this->config['simple_captcha']['min_number'];
        $max = $this->config['simple_captcha']['max_number'];
        
        // Only generate new CAPTCHA if one doesn't exist in session
        if (!isset($_SESSION['simple_captcha_result']) || !isset($_SESSION['simple_captcha_num1']) || !isset($_SESSION['simple_captcha_num2'])) {
            $num1 = mt_rand($min, $max);
            $num2 = mt_rand($min, $max);
            $result = $num1 + $num2;
            
            $_SESSION['simple_captcha_result'] = $result;
            $_SESSION['simple_captcha_num1'] = $num1;
            $_SESSION['simple_captcha_num2'] = $num2;
            
            if ($this->debug) {
                $this->logDebug("Generated new simple CAPTCHA: {$num1} + {$num2} = {$result}");
            }
        } else if ($this->debug) {
            $this->logDebug("Using existing simple CAPTCHA: {$_SESSION['simple_captcha_num1']} + {$_SESSION['simple_captcha_num2']} = {$_SESSION['simple_captcha_result']}");
        }
        
        return [
            'enabled' => true,
            'num1' => $_SESSION['simple_captcha_num1'],
            'num2' => $_SESSION['simple_captcha_num2']
        ];
    }

    /**
     * Verify simple CAPTCHA
     * @param int $answer User's answer
     * @return bool True if CAPTCHA is valid
     */
    public function verifySimpleCaptcha($answer)
    {
        if (!$this->config['simple_captcha']['enabled']) {
            if ($this->debug) {
                $this->logDebug("Simple CAPTCHA validation skipped (disabled)");
            }
            return true; // Skip validation if disabled
        }

        if (!isset($_SESSION['simple_captcha_result'])) {
            if ($this->debug) {
                $this->logDebug("Simple CAPTCHA validation failed: No result in session");
            }
            return false;
        }

        $result = (int)$_SESSION['simple_captcha_result'];
        $answer = (int)$answer;
        
        $isValid = ($result === $answer);
        
        if ($this->debug) {
            $this->logDebug("Simple CAPTCHA validation: Expected {$result}, Got {$answer}, Valid: " . ($isValid ? 'true' : 'false'));
        }
        
        // Only generate new CAPTCHA if the answer was correct or after 3 failed attempts
        if ($isValid || (isset($_SESSION['simple_captcha_attempts']) && $_SESSION['simple_captcha_attempts'] >= 3)) {
            // Reset attempts counter
            $_SESSION['simple_captcha_attempts'] = 0;
            
            // Generate new CAPTCHA for next attempt
            $min = $this->config['simple_captcha']['min_number'];
            $max = $this->config['simple_captcha']['max_number'];
            
            $num1 = mt_rand($min, $max);
            $num2 = mt_rand($min, $max);
            $result = $num1 + $num2;
            
            $_SESSION['simple_captcha_result'] = $result;
            $_SESSION['simple_captcha_num1'] = $num1;
            $_SESSION['simple_captcha_num2'] = $num2;
            
            if ($this->debug) {
                $this->logDebug("Generated new simple CAPTCHA after " . ($isValid ? "successful validation" : "3 failed attempts") . ": {$num1} + {$num2} = {$result}");
            }
        } else {
            // Increment attempts counter
            $_SESSION['simple_captcha_attempts'] = (isset($_SESSION['simple_captcha_attempts']) ? $_SESSION['simple_captcha_attempts'] : 0) + 1;
            
            if ($this->debug) {
                $this->logDebug("Simple CAPTCHA attempts: {$_SESSION['simple_captcha_attempts']}");
            }
        }
        
        return $isValid;
    }

    /**
     * Get reCAPTCHA site key
     * @return string|null Site key or null if disabled
     */
    public function getRecaptchaSiteKey()
    {
        if (!$this->config['recaptcha']['enabled']) {
            return null;
        }
        
        return $this->config['recaptcha']['site_key'][$this->host] ?? null;
    }

    /**
     * Get reCAPTCHA HTML
     * @return string HTML for reCAPTCHA
     */
    public function getRecaptchaHtml()
    {
        if (!$this->config['recaptcha']['enabled']) {
            return '';
        }
        
        $siteKey = $this->getRecaptchaSiteKey();
        if (!$siteKey) {
            return '';
        }
        
        $version = $this->config['recaptcha']['version'] ?? 'v2';
        
        if ($version === 'v2') {
            return '<div class="g-recaptcha mb-3" data-sitekey="' . htmlspecialchars($siteKey) . '"></div>';
        } elseif ($version === 'v3') {
            return '<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">';
        }
        
        return '';
    }

    /**
     * Verify reCAPTCHA
     * @param string $response reCAPTCHA response token
     * @return bool True if reCAPTCHA is valid
     */
    public function verifyRecaptcha($response)
    {
        if (!$this->config['recaptcha']['enabled']) {
            if ($this->debug) {
                $this->logDebug("reCAPTCHA validation skipped (disabled)");
            }
            return true; // Skip validation if disabled
        }
        
        if (empty($response)) {
            if ($this->debug) {
                $this->logDebug("reCAPTCHA validation failed: Empty response");
            }
            return false;
        }
        
        $secretKey = $this->config['recaptcha']['secret_key'][$this->host] ?? null;
        if (!$secretKey) {
            if ($this->debug) {
                $this->logDebug("reCAPTCHA validation failed: No secret key for host {$this->host}");
            }
            return false;
        }
        
        // Make request to Google reCAPTCHA API
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === false) {
            if ($this->debug) {
                $this->logDebug("reCAPTCHA validation failed: Error contacting Google API");
            }
            return false;
        }
        
        $json = json_decode($result, true);
        $isValid = isset($json['success']) && $json['success'] === true;
        
        if ($this->debug) {
            $this->logDebug("reCAPTCHA validation: " . ($isValid ? 'success' : 'failed') . ", Response: " . json_encode($json));
        }
        
        return $isValid;
    }

    /**
     * Verify both CAPTCHAs
     * @param int $simpleCaptchaAnswer User's answer to simple CAPTCHA
     * @param string $recaptchaResponse reCAPTCHA response token
     * @return array Result with success status and message
     */
    public function verify($simpleCaptchaAnswer, $recaptchaResponse)
    {
        $simpleCaptchaEnabled = $this->config['simple_captcha']['enabled'];
        $recaptchaEnabled = $this->config['recaptcha']['enabled'];
        
        if ($this->debug) {
            $this->logDebug("Verifying CAPTCHAs - Simple: " . ($simpleCaptchaEnabled ? 'enabled' : 'disabled') . ", reCAPTCHA: " . ($recaptchaEnabled ? 'enabled' : 'disabled'));
            $this->logDebug("Simple CAPTCHA answer: {$simpleCaptchaAnswer}, reCAPTCHA response length: " . strlen($recaptchaResponse));
        }
        
        // Check if form was already successfully submitted (to handle page reloads)
        if (isset($_SESSION['captcha_verified']) && $_SESSION['captcha_verified'] === true) {
            // Clear the verification flag to prevent session fixation attacks
            unset($_SESSION['captcha_verified']);
            
            if ($this->debug) {
                $this->logDebug("CAPTCHA already verified in this session");
            }
            
            return [
                'success' => true,
                'message' => 'CAPTCHA verification successful.'
            ];
        }
        
        // Verify simple CAPTCHA if enabled
        if ($simpleCaptchaEnabled && !$this->verifySimpleCaptcha($simpleCaptchaAnswer)) {
            if ($this->debug) {
                $this->logDebug("CAPTCHA verification failed: Simple CAPTCHA incorrect");
            }
            
            return [
                'success' => false,
                'message' => 'The simple CAPTCHA answer is incorrect.'
            ];
        }
        
        // Verify reCAPTCHA if enabled
        if ($recaptchaEnabled && !$this->verifyRecaptcha($recaptchaResponse)) {
            if ($this->debug) {
                $this->logDebug("CAPTCHA verification failed: reCAPTCHA verification failed");
            }
            
            return [
                'success' => false,
                'message' => 'The reCAPTCHA verification failed. Please try again.'
            ];
        }
        
        // Set verification flag for this session
        $_SESSION['captcha_verified'] = true;
        
        if ($this->debug) {
            $this->logDebug("CAPTCHA verification successful");
        }
        
        return [
            'success' => true,
            'message' => 'CAPTCHA verification successful.'
        ];
    }
} 