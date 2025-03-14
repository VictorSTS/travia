<?php
// Start session
session_start();

// Include necessary files
require_once 'class/User.php';
require_once 'class/Mailer.php';
require_once 'class/Captcha.php';

// Connect to database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

// Initialize variables
$message = '';
$messageType = '';
$step = isset($_GET['step']) ? $_GET['step'] : '1';
$email = isset($_SESSION['recovery_email']) ? $_SESSION['recovery_email'] : '';

// Create user, mailer, and captcha objects
$user = new User($pdo);
$mailer = new Mailer();
$captcha = new Captcha(false);

// Generate simple CAPTCHA
$simpleCaptcha = $captcha->generateSimpleCaptcha();

// Restore form values from session if available
if (isset($_SESSION['recovery_form_email']) && $step == '1') {
    $email = $_SESSION['recovery_form_email'];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Request recovery code
    if (isset($_POST['request_code'])) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $simpleCaptchaAnswer = isset($_POST['captcha_answer']) ? (int)$_POST['captcha_answer'] : 0;
        $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        
        // Store the submitted values in session to handle reloads
        $_SESSION['recovery_form_email'] = $email;
        $_SESSION['recovery_form_captcha_answer'] = $simpleCaptchaAnswer;
        
        // Verify CAPTCHA
        $captchaResult = $captcha->verify($simpleCaptchaAnswer, $recaptchaResponse);
        
        if (!$captchaResult['success']) {
            $message = $captchaResult['message'];
            $messageType = 'danger';
            
            // Don't regenerate simple CAPTCHA on error - it's handled in the Captcha class
        } else {
            // Clear stored form values on success
            unset($_SESSION['recovery_form_email']);
            unset($_SESSION['recovery_form_captcha_answer']);
            
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Generate recovery code
                $result = $user->generateRecoveryCode($email);
                
                if ($result['success']) {
                    // Store email in session for next steps
                    $_SESSION['recovery_email'] = $email;
                    
                    // Send recovery code email if not a fake response
                    if (!isset($result['fake_response']) || !$result['fake_response']) {
                        $firstName = $result['user']['first_name'] ?? 'User';
                        $code = $result['user']['code'];
                        
                        $mailResult = $mailer->sendRecoveryCode($email, $firstName, $code);
                        
                        if (!$mailResult['success']) {
                            $message = 'Error sending email: ' . $mailResult['message'];
                            $messageType = 'danger';
                        } else {
                            // Redirect to step 2
                            header('Location: forgot_password.php?step=2');
                            exit;
                        }
                    } else {
                        // For security, we still show success message even if email doesn't exist
                        // Redirect to step 2
                        header('Location: forgot_password.php?step=2');
                        exit;
                    }
                } else {
                    $message = $result['message'];
                    $messageType = 'danger';
                }
            } else {
                $message = 'Please enter a valid email address.';
                $messageType = 'danger';
            }
        }
    }
    
    // Step 2: Verify recovery code
    if (isset($_POST['verify_code'])) {
        $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
        $email = $_SESSION['recovery_email'];
        
        if (!empty($code) && !empty($email)) {
            // Verify recovery code
            $result = $user->verifyRecoveryCode($email, $code);
            
            if ($result['success']) {
                // Store user ID in session for next step
                $_SESSION['recovery_user_id'] = $result['user']['id'];
                
                // Redirect to step 3
                header('Location: forgot_password.php?step=3');
                exit;
            } else {
                $message = $result['message'];
                $messageType = 'danger';
            }
        } else {
            $message = 'Please enter the verification code.';
            $messageType = 'danger';
        }
    }
    
    // Step 3: Update password
    if (isset($_POST['update_password'])) {
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $confirmPassword = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);
        $userId = $_SESSION['recovery_user_id'];
        
        if (!empty($password) && !empty($confirmPassword) && !empty($userId)) {
            if ($password === $confirmPassword) {
                // Validate password complexity
                if (strlen($password) < 12) {
                    $message = 'Password must contain at least 12 characters.';
                    $messageType = 'danger';
                } elseif (!preg_match('/[0-9]/', $password)) {
                    $message = 'Password must contain at least one digit.';
                    $messageType = 'danger';
                } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
                    $message = 'Password must contain at least one special character.';
                    $messageType = 'danger';
                } else {
                    // Update password
                    $result = $user->updatePassword($userId, $password);
                    
                    if ($result['success']) {
                        // Clear recovery session data
                        unset($_SESSION['recovery_email']);
                        unset($_SESSION['recovery_user_id']);
                        
                        $message = 'Your password has been successfully updated. You can now log in with your new password.';
                        $messageType = 'success';
                        
                        // Redirect to login page after 3 seconds
                        header('Refresh: 3; URL=login.php');
                    } else {
                        $message = $result['message'];
                        $messageType = 'danger';
                    }
                }
            } else {
                $message = 'Passwords do not match.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Please fill in all fields.';
            $messageType = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery - Travia Tour</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #343a40;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: #343a40;
            border-color: #343a40;
        }
        .btn-primary:hover {
            background-color: #23272b;
            border-color: #23272b;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .step.active {
            background-color: #343a40;
            color: white;
        }
        .step-line {
            flex-grow: 1;
            height: 2px;
            background-color: #e9ecef;
            margin: 15px 10px 0;
        }
        .step-line.active {
            background-color: #343a40;
        }
    </style>
    <?php if ($captcha->getRecaptchaSiteKey()): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h4>Password Recovery</h4>
                    </div>
                    <div class="card-body">
                        <!-- Step indicator -->
                        <div class="step-indicator">
                            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">1</div>
                            <div class="step-line <?php echo $step >= 2 ? 'active' : ''; ?>"></div>
                            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">2</div>
                            <div class="step-line <?php echo $step >= 3 ? 'active' : ''; ?>"></div>
                            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">3</div>
                        </div>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($step == '1'): ?>
                            <!-- Step 1: Request recovery code -->
                            <p class="text-center mb-4">Enter your email address to receive a recovery code.</p>
                            <form method="post" action="forgot_password.php">
                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                                </div>
                                
                                <?php if ($simpleCaptcha['enabled']): ?>
                                <div class="form-group">
                                    <label for="captcha_answer">Simple CAPTCHA: <?php echo $simpleCaptcha['num1']; ?> + <?php echo $simpleCaptcha['num2']; ?> = ?</label>
                                    <input type="number" class="form-control" id="captcha_answer" name="captcha_answer" required>
                                </div>
                                <?php endif; ?>
                                
                                <?php echo $captcha->getRecaptchaHtml(); ?>
                                
                                <button type="submit" name="request_code" class="btn btn-primary btn-block">Send Code</button>
                            </form>
                            <div class="text-center mt-3">
                                <a href="login.php">Back to Login</a>
                            </div>
                        <?php elseif ($step == '2'): ?>
                            <!-- Step 2: Verify recovery code -->
                            <p class="text-center mb-4">A recovery code has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>. Please enter this code below.</p>
                            <form method="post" action="forgot_password.php?step=2">
                                <div class="form-group">
                                    <label for="code">Recovery code</label>
                                    <input type="text" class="form-control" id="code" name="code" required>
                                </div>
                                <button type="submit" name="verify_code" class="btn btn-primary btn-block">Verify Code</button>
                            </form>
                            <div class="text-center mt-3">
                                <a href="forgot_password.php">Request a new code</a>
                            </div>
                        <?php elseif ($step == '3'): ?>
                            <!-- Step 3: Update password -->
                            <p class="text-center mb-4">Please enter your new password.</p>
                            <form method="post" action="forgot_password.php?step=3">
                                <div class="form-group">
                                    <label for="password">New password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="form-text text-muted">Password must contain at least 8 characters, including at least one digit and one special character.</small>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="update_password" class="btn btn-primary btn-block">Update Password</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Client-side validation for password update form
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForm = document.querySelector('form[action="forgot_password.php?step=3"]');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(event) {
                    const password = document.getElementById('password');
                    const confirmPassword = document.getElementById('confirm_password');
                    let isValid = true;
                    
                    // Clear previous error messages
                    clearErrors();
                    
                    // Validate password
                    if (password.value.length < 8) {
                        showError(password, 'Password must contain at least 8 characters.');
                        isValid = false;
                    } else if (!/[0-9]/.test(password.value)) {
                        showError(password, 'Password must contain at least one digit.');
                        isValid = false;
                    } else if (!/[^a-zA-Z0-9]/.test(password.value)) {
                        showError(password, 'Password must contain at least one special character.');
                        isValid = false;
                    }
                    
                    // Validate password confirmation
                    if (password.value !== confirmPassword.value) {
                        showError(confirmPassword, 'Passwords do not match.');
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        event.preventDefault();
                    }
                });
            }
            
            function showError(input, message) {
                // Remove existing error message if any
                const existingError = input.parentNode.querySelector('.invalid-feedback');
                if (existingError) {
                    existingError.remove();
                }
                
                // Add error class to input
                input.classList.add('is-invalid');
                
                // Create error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = message;
                
                // Insert error message after input
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
            
            function clearErrors() {
                const inputs = document.querySelectorAll('.is-invalid');
                const errorMessages = document.querySelectorAll('.invalid-feedback');
                
                inputs.forEach(input => input.classList.remove('is-invalid'));
                errorMessages.forEach(error => error.remove());
            }
        });
    </script>
</body>
</html> 