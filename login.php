<?php
// Start session
session_start();

// Include necessary files
require_once 'class/User.php';
require_once 'class/Mailer.php';
require_once 'class/Captcha.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$errors = [];
$step = 1; // Step 1: Email input, Step 2: Verification code input
$email = '';
$userId = '';

// Create captcha object and generate simple CAPTCHA
$captcha = new Captcha(false); // Enable debug mode
$simpleCaptcha = $captcha->generateSimpleCaptcha();

// Restore form values from session if available
if (isset($_SESSION['login_form_email'])) {
    $email = $_SESSION['login_form_email'];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Email submission
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $simpleCaptchaAnswer = isset($_POST['captcha_answer']) ? (int)$_POST['captcha_answer'] : 0;
        $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        
        // Store the submitted values in session to handle reloads
        $_SESSION['login_form_email'] = $email;
        $_SESSION['login_form_captcha_answer'] = $simpleCaptchaAnswer;
        
        // Verify CAPTCHA
        $captchaResult = $captcha->verify($simpleCaptchaAnswer, $recaptchaResponse);
        
        if (!$captchaResult['success']) {
            $errors['captcha'] = $captchaResult['message'];
            
            // Don't regenerate simple CAPTCHA on error - it's handled in the Captcha class
        } else {
            // Clear stored form values on success
            unset($_SESSION['login_form_email']);
            unset($_SESSION['login_form_captcha_answer']);
            
            // Validate email
            if (empty($email)) {
                $errors['email'] = 'Email address is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email address is not valid.';
            } else {
                try {
                    // Connect to database
                    $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Create user object
                    $user = new User($pdo);
                    
                    // Generate login code
                    $result = $user->generateLoginCode($email);
                    
                    if ($result['success']) {
                        // Send verification code email
                        $mailer = new Mailer();
                        $emailResult = $mailer->sendLoginCode(
                            $result['user']['email'],
                            $user->getFirstName(),
                            $result['user']['code']
                        );
                        
                        if ($emailResult['success']) {
                            // Move to step 2
                            $step = 2;
                            $userId = $result['user']['id'];
                            $_SESSION['login_email'] = $email;
                            $_SESSION['login_user_id'] = $userId;
                        } else {
                            $errors['email'] = $emailResult['message'];
                        }
                    } else {
                        $errors['email'] = $result['message'];
                    }
                } catch (PDOException $e) {
                    $errors['general'] = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
    
    // Step 2: Verification code submission
    if (isset($_POST['code'])) {
        $code = $_POST['code'];
        $userId = $_SESSION['login_user_id'] ?? '';
        
        // Validate code
        if (empty($code)) {
            $errors['code'] = 'Verification code is required.';
        } elseif (!preg_match('/^\d{6}$/', $code)) {
            $errors['code'] = 'Verification code must contain 6 digits.';
        } elseif (empty($userId)) {
            $errors['general'] = 'Session expired. Please try again.';
            $step = 1;
        } else {
            try {
                // Connect to database
                $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create user object
                $user = new User($pdo);
                
                // Verify login code
                $result = $user->verifyLoginCode($userId, $code);
                
                if ($result['success']) {
                    // Set session variables
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['user_name'] = $result['user']['firstName'] . ' ' . $result['user']['lastName'];
                    $_SESSION['user_email'] = $result['user']['email'];
                    $_SESSION['home_planet'] = $result['user']['homePlanet'];
                    $_SESSION['work_planet'] = $result['user']['workPlanet'];
                    
                    // Clear login session variables
                    unset($_SESSION['login_email']);
                    unset($_SESSION['login_user_id']);
                    
                    // Redirect to home page
                    header('Location: index.php');
                    exit;
                } else {
                    $errors['code'] = $result['message'];
                }
            } catch (PDOException $e) {
                $errors['general'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get email from session if available
if ($step == 2 && isset($_SESSION['login_email'])) {
    $email = $_SESSION['login_email'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Travia Tour</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @font-face {
            font-family: Aurebesh;
            src: url("/fonts/Aurebesh.otf") format("opentype")
        }
        body {
            padding-top: 56px;
            background-color: #121212;
            color: #ffffff;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .navbar {
            background-color: #1e1e1e;
        }
        .navbar-brand, .nav-link {
            color: #ffffff;
        }
        .form-control {
            background-color: #333333;
            color: #ffffff;
            border: 1px solid #555555;
        }
        .form-control:focus {
            background-color: #444444;
            color: #ffffff;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .card {
            background-color: #1e1e1e;
            border: 1px solid #333333;
        }
        .invalid-feedback {
            display: block;
        }
        .code-input {
            letter-spacing: 0.5em;
            font-size: 1.5em;
            text-align: center;
        }
    </style>
    <?php if ($captcha->getRecaptchaSiteKey()): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="index.php">Travia Tour</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Register</a>
                </li>
            </ul>
        </div>
        <button class="btn btn-link text-white" id="toggleFont">Translate to Aurebesh</button>
    </nav>

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Login</h2>
                        
                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($step == 1): ?>
                            <!-- Step 1: Email input -->
                            <form id="emailForm" method="POST" action="login.php" novalidate>
                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="captcha_answer">CAPTCHA: <?php echo $simpleCaptcha['num1']; ?> + <?php echo $simpleCaptcha['num2']; ?> = ?</label>
                                    <input type="number" class="form-control <?php echo isset($errors['captcha']) ? 'is-invalid' : ''; ?>" id="captcha_answer" name="captcha_answer" required>
                                    <?php if (isset($errors['captcha'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['captcha']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php echo $captcha->getRecaptchaHtml(); ?>
                                <button type="submit" class="btn btn-primary btn-block">Receive verification code</button>
                            </form>
                        <?php else: ?>
                            <!-- Step 2: Verification code input -->
                            <div class="alert alert-info">
                                A verification code has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>.
                                <br>This code will expire in 1 minute.
                            </div>
                            <form id="codeForm" method="POST" action="login.php" novalidate>
                                <div class="form-group">
                                    <label for="code">Verification code</label>
                                    <input type="text" class="form-control code-input <?php echo isset($errors['code']) ? 'is-invalid' : ''; ?>" id="code" name="code" maxlength="6" placeholder="------" required>
                                    <?php if (isset($errors['code'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['code']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Login</button>
                                <a href="login.php" class="btn btn-link btn-block">Use another email address</a>
                            </form>
                        <?php endif; ?>
                        
                        <div class="mt-3 text-center">
                            <p>Don't have an account? <a href="register.php">Register</a></p>
                            <p>Forgot password? <a href="forgot_password.php">Recover your password</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Travia Tour. All rights reserved.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Client-side validation for email form
        document.getElementById('emailForm')?.addEventListener('submit', function(event) {
            const email = document.getElementById('email');
            if (email.value.trim() === '') {
                showError(email, 'Email address is required.');
                event.preventDefault();
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                showError(email, 'Email address is not valid.');
                event.preventDefault();
            } else {
                clearError(email);
            }
        });
        
        // Client-side validation for code form
        document.getElementById('codeForm')?.addEventListener('submit', function(event) {
            const code = document.getElementById('code');
            if (code.value.trim() === '') {
                showError(code, 'Verification code is required.');
                event.preventDefault();
            } else if (!/^\d{6}$/.test(code.value)) {
                showError(code, 'Verification code must contain 6 digits.');
                event.preventDefault();
            } else {
                clearError(code);
            }
        });
        
        // Format code input to only allow numbers
        document.getElementById('code')?.addEventListener('input', function(event) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        function showError(input, message) {
            input.classList.add('is-invalid');
            
            let errorElement = input.nextElementSibling;
            if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
                errorElement = document.createElement('div');
                errorElement.className = 'invalid-feedback';
                input.parentNode.insertBefore(errorElement, input.nextElementSibling);
            }
            
            errorElement.textContent = message;
        }
        
        function clearError(input) {
            input.classList.remove('is-invalid');
            
            let errorElement = input.nextElementSibling;
            if (errorElement && errorElement.classList.contains('invalid-feedback')) {
                errorElement.textContent = '';
            }
        }
        
        $('#toggleFont').on('click', function() {
            $('body').toggleClass('aurebesh');
        });
        
        $('head').append('<style>.aurebesh { font-family: "Aurebesh", sans-serif; }</style>');
    </script>
</body>
</html> 