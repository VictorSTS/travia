<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    private $mail;
    private $config;
    
    /**
     * Mailer constructor.
     */
    public function __construct()
    {
        // Include Composer autoloader
        require_once __DIR__ . '/../vendor/autoload.php';
        
        // Load mail configuration
        $this->loadConfig();
        
        // Initialize PHPMailer
        $this->mail = new PHPMailer(true);
        
        $this->mail->CharSet = 'UTF-8';
        // Configure SMTP settings from config
        $this->mail->isSMTP();
        $this->mail->Host = $this->config['smtp']['host'];
        $this->mail->SMTPAuth = $this->config['smtp']['auth'];
        $this->mail->Username = $this->config['smtp']['username'];
        $this->mail->Password = $this->config['smtp']['password'];
        $this->mail->SMTPSecure = $this->config['smtp']['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = $this->config['smtp']['port'];
        
        // Set default sender
        $this->mail->setFrom($this->config['from']['email'], $this->config['from']['name']);
        
        // Enable debug output if configured
        if ($this->config['debug']) {
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
    }
    
    /**
     * Load mail configuration from JSON file
     */
    private function loadConfig()
    {
        $configFile = __DIR__ . '/../config/mail.json';
        
        if (!file_exists($configFile)) {
            throw new Exception('Mail configuration file not found: ' . $configFile);
        }
        
        $configJson = file_get_contents($configFile);
        $config = json_decode($configJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid mail configuration JSON: ' . json_last_error_msg());
        }
        
        // Set default values if not provided
        $config['smtp'] = $config['smtp'] ?? [];
        $config['smtp']['host'] = $config['smtp']['host'] ?? 'localhost';
        $config['smtp']['port'] = $config['smtp']['port'] ?? 25;
        $config['smtp']['encryption'] = $config['smtp']['encryption'] ?? '';
        $config['smtp']['auth'] = $config['smtp']['auth'] ?? false;
        $config['smtp']['username'] = $config['smtp']['username'] ?? '';
        $config['smtp']['password'] = $config['smtp']['password'] ?? '';
        
        $config['from'] = $config['from'] ?? [];
        $config['from']['email'] = $config['from']['email'] ?? 'noreply@example.com';
        $config['from']['name'] = $config['from']['name'] ?? 'Travia Tour';
        
        $config['debug'] = $config['debug'] ?? false;
        
        $this->config = $config;
    }
    
    /**
     * Send verification email
     * @param string $email Recipient email
     * @param string $firstName Recipient first name
     * @param string $token Verification token
     * @return array Success status and message
     */
    public function sendVerificationEmail($email, $firstName, $token)
    {
        try {
            // Clear previous recipients
            $this->mail->clearAddresses();
            
            // Set recipient
            $this->mail->addAddress($email);
            
            // Set email content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Verify your Travia Tour account';
            
            // Generate verification link
            $verificationLink = 'http://' . $_SERVER['HTTP_HOST'] . '/verify.php?token=' . $token;
            
            // Email body
            $this->mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #1e1e1e; color: #ffffff; padding: 10px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; }
                        .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Travia Tour</h1>
                        </div>
                        <div class='content'>
                            <h2>Hello $firstName,</h2>
                            <p>Thank you for registering with Travia Tour. To activate your account, please click the button below:</p>
                            <p style='text-align: center;'>
                                <a href='$verificationLink' class='button'>Verify my account</a>
                            </p>
                            <p>This link will expire in 1 minute.</p>
                            <p>If you did not create an account on Travia Tour, please ignore this email.</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Travia Tour. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Plain text alternative
            $this->mail->AltBody = "
                Hello $firstName,
                
                Thank you for registering with Travia Tour. To activate your account, please click the link below:
                
                $verificationLink
                
                This link will expire in 1 minute.
                
                If you did not create an account on Travia Tour, please ignore this email.
                
                © 2024 Travia Tour. All rights reserved.
            ";
            
            // Send email
            $this->mail->send();
            
            return ['success' => true, 'message' => 'Verification email sent successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error sending email: ' . $this->mail->ErrorInfo];
        }
    }
    
    /**
     * Send account validation confirmation email
     * @param string $email Recipient email
     * @param string $firstName Recipient first name
     * @return array Success status and message
     */
    public function sendValidationConfirmationEmail($email, $firstName)
    {
        try {
            // Clear previous recipients
            $this->mail->clearAddresses();
            
            // Set recipient
            $this->mail->addAddress($email);
            
            // Set email content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Your Travia Tour account has been validated';
            
            // Login link
            $loginLink = 'http://' . $_SERVER['HTTP_HOST'] . '/login.php';
            
            // Email body
            $this->mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #1e1e1e; color: #ffffff; padding: 10px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; }
                        .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Travia Tour</h1>
                        </div>
                        <div class='content'>
                            <h2>Hello $firstName,</h2>
                            <p>Your Travia Tour account has been successfully validated. You can now log in and start exploring the galaxy!</p>
                            <p style='text-align: center;'>
                                <a href='$loginLink' class='button'>Log in</a>
                            </p>
                            <p>Thank you for being part of our intergalactic community.</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Travia Tour. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Plain text alternative
            $this->mail->AltBody = "
                Hello $firstName,
                
                Your Travia Tour account has been successfully validated. You can now log in and start exploring the galaxy!
                
                Log in: $loginLink
                
                Thank you for being part of our intergalactic community.
                
                © 2024 Travia Tour. All rights reserved.
            ";
            
            // Send email
            $this->mail->send();
            
            return ['success' => true, 'message' => 'Confirmation email sent successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error sending email: ' . $this->mail->ErrorInfo];
        }
    }
    
    /**
     * Send login verification code
     * @param string $email Recipient email
     * @param string $firstName Recipient first name
     * @param string $code Verification code
     * @return array Success status and message
     */
    public function sendLoginCode($email, $firstName, $code)
    {
        try {
            // Clear previous recipients
            $this->mail->clearAddresses();
            
            // Set recipient
            $this->mail->addAddress($email);
            
            // Set email content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Travia Tour Verification Code';
            
            // Email body
            $this->mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #1e1e1e; color: #ffffff; padding: 10px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; }
                        .code { font-size: 24px; font-weight: bold; text-align: center; padding: 10px; background-color: #f0f0f0; border-radius: 5px; margin: 20px 0; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Travia Tour</h1>
                        </div>
                        <div class='content'>
                            <h2>Hello $firstName,</h2>
                            <p>Here is your verification code to log in to Travia Tour:</p>
                            <div class='code'>$code</div>
                            <p>This code will expire in 1 minute.</p>
                            <p>If you did not request this code, please ignore this email.</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Travia Tour. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Plain text alternative
            $this->mail->AltBody = "
                Hello $firstName,
                
                Here is your verification code to log in to Travia Tour:
                
                $code
                
                This code will expire in 1 minute.
                
                If you did not request this code, please ignore this email.
                
                © 2024 Travia Tour. All rights reserved.
            ";
            
            // Send email
            $this->mail->send();
            
            return ['success' => true, 'message' => 'Verification code sent successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error sending email: ' . $this->mail->ErrorInfo];
        }
    }
    
    /**
     * Send password recovery code
     * @param string $email Recipient email
     * @param string $firstName Recipient first name
     * @param string $code Recovery code
     * @return array Success status and message
     */
    public function sendRecoveryCode($email, $firstName, $code)
    {
        try {
            // Clear previous recipients
            $this->mail->clearAddresses();
            
            // Set recipient
            $this->mail->addAddress($email);
            
            // Set email content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Travia Tour Account Recovery';
            
            // Email body
            $this->mail->Body = "
                <html>
                <head>
                    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #1e1e1e; color: #ffffff; padding: 10px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; }
                        .code { font-size: 24px; font-weight: bold; text-align: center; padding: 10px; background-color: #f0f0f0; border-radius: 5px; margin: 20px 0; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Travia Tour</h1>
                        </div>
                        <div class='content'>
                            <h2>Hello $firstName,</h2>
                            <p>We received an account recovery request for your email address. Here is your verification code:</p>
                            <div class='code'>$code</div>
                            <p>This code will expire in 15 minutes.</p>
                            <p>If you did not request this account recovery, please ignore this email.</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Travia Tour. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Plain text alternative
            $this->mail->AltBody = "
                Hello $firstName,
                
                We received an account recovery request for your email address. Here is your verification code:
                
                $code
                
                This code will expire in 15 minutes.
                
                If you did not request this account recovery, please ignore this email.
                
                © 2024 Travia Tour. All rights reserved.
            ";
            
            // Send email
            $this->mail->send();
            
            return ['success' => true, 'message' => 'Recovery code sent successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error sending email: ' . $this->mail->ErrorInfo];
        }
    }
    
    /**
     * Get SMTP host
     * @return string SMTP host
     */
    public function getHost()
    {
        return $this->config['smtp']['host'];
    }
    
    /**
     * Get SMTP port
     * @return int SMTP port
     */
    public function getPort()
    {
        return $this->config['smtp']['port'];
    }
    
    /**
     * Get SMTP secure
     * @return string SMTP secure
     */
    public function getSMTPSecure()
    {
        return $this->config['smtp']['encryption'];
    }
    
    /**
     * Get from email
     * @return string From email
     */
    public function getFromEmail()
    {
        return $this->config['from']['email'];
    }
    
    /**
     * Get from name
     * @return string From name
     */
    public function getFromName()
    {
        return $this->config['from']['name'];
    }
} 