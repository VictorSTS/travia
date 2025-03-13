<?php
// Start session
session_start();

// Include necessary files
require_once 'class/User.php';
require_once 'class/Mailer.php';

// Initialize variables
$errors = [];
$step = isset($_SESSION['recover_step']) ? $_SESSION['recover_step'] : 1;
$email = isset($_SESSION['recover_email']) ? $_SESSION['recover_email'] : '';
$userId = isset($_SESSION['recover_user_id']) ? $_SESSION['recover_user_id'] : '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Email submission
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        
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
                
                // Generate recovery code
                $result = $user->generateRecoveryCode($email);
                
                // For security, we always show the same message whether the email exists or not
                if ($result['success']) {
                    // Send recovery code email
                    $mailer = new Mailer();
                    $emailResult = $mailer->sendRecoveryCode(
                        $result['user']['email'],
                        $user->getFirstName(),
                        $result['user']['code']
                    );
                    
                    if ($emailResult['success']) {
                        // Move to step 2
                        $step = 2;
                        $_SESSION['recover_step'] = 2;
                        $_SESSION['recover_email'] = $email;
                        $_SESSION['recover_user_id'] = $result['user']['id'];
                    }
                }
                
                // Always show the same message for security
                $step = 2;
                $_SESSION['recover_step'] = 2;
                $_SESSION['recover_email'] = $email;
                
            } catch (PDOException $e) {
                $errors['general'] = 'An error occurred. Please try again later.';
            }
        }
    }
    
    // Step 2: Verification code submission
    if (isset($_POST['code'])) {
        $code = $_POST['code'];
        $email = $_SESSION['recover_email'] ?? '';
        
        // Validate code
        if (empty($code)) {
            $errors['code'] = 'Verification code is required.';
        } elseif (!preg_match('/^\d{6}$/', $code)) {
            $errors['code'] = 'Verification code must contain 6 digits.';
        } elseif (empty($email)) {
            $errors['general'] = 'Session expired. Please try again.';
            $step = 1;
            $_SESSION['recover_step'] = 1;
        } else {
            try {
                // Connect to database
                $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create user object
                $user = new User($pdo);
                
                // Verify recovery code
                $result = $user->verifyRecoveryCode($email, $code);
                
                if ($result['success']) {
                    // Move to step 3
                    $step = 3;
                    $_SESSION['recover_step'] = 3;
                    $_SESSION['recover_user_id'] = $result['user']['id'];
                } else {
                    $errors['code'] = 'Invalid or expired verification code.';
                }
            } catch (PDOException $e) {
                $errors['general'] = 'An error occurred. Please try again later.';
            }
        }
    }
    
    // Step 3: New password submission
    if (isset($_POST['password']) && isset($_POST['confirm_password'])) {
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        $userId = $_SESSION['recover_user_id'] ?? '';
        
        // Validate password
        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must contain at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        } elseif (empty($userId)) {
            $errors['general'] = 'Session expired. Please try again.';
            $step = 1;
            $_SESSION['recover_step'] = 1;
        } else {
            try {
                // Connect to database
                $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create user object
                $user = new User($pdo);
                
                // Update password
                $result = $user->updatePassword($userId, $password);
                
                if ($result['success']) {
                    // Clear session variables
                    unset($_SESSION['recover_step']);
                    unset($_SESSION['recover_email']);
                    unset($_SESSION['recover_user_id']);
                    
                    // Redirect to login page with success message
                    $_SESSION['login_message'] = 'Your password has been successfully updated. You can now log in.';
                    header('Location: login.php');
                    exit;
                } else {
                    $errors['general'] = $result['message'];
                }
            } catch (PDOException $e) {
                $errors['general'] = 'An error occurred. Please try again later.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery - Travia Tour</title>
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
                <li class="nav-item">
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
                        <h2 class="card-title text-center mb-4">Account Recovery</h2>
                        
                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($step == 1): ?>
                            <!-- Step 1: Email input -->
                            <form id="emailForm" method="POST" action="recover.php" novalidate>
                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Receive verification code</button>
                            </form>
                        <?php elseif ($step == 2): ?>
                            <!-- Step 2: Verification code input -->
                            <div class="alert alert-info">
                                If the email address exists in our database, a verification code has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>.
                                <br>This code will expire in 15 minutes.
                            </div>
                            <form id="codeForm" method="POST" action="recover.php" novalidate>
                                <div class="form-group">
                                    <label for="code">Verification code</label>
                                    <input type="text" class="form-control code-input <?php echo isset($errors['code']) ? 'is-invalid' : ''; ?>" id="code" name="code" maxlength="6" placeholder="------" required>
                                    <?php if (isset($errors['code'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['code']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Verify</button>
                                <a href="recover.php" class="btn btn-link btn-block">Use another email address</a>
                            </form>
                        <?php elseif ($step == 3): ?>
                            <!-- Step 3: New password input -->
                            <form id="passwordForm" method="POST" action="recover.php" novalidate>
                                <div class="form-group">
                                    <label for="password">New password</label>
                                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Password must contain at least 8 characters.</small>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm password</label>
                                    <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Update password</button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="mt-3 text-center">
                            <p>Remember your password? <a href="login.php">Log in</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 Travia Tour. All rights reserved.</p>
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
        
        // Client-side validation for password form
        document.getElementById('passwordForm')?.addEventListener('submit', function(event) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password.value.trim() === '') {
                showError(password, 'Password is required.');
                event.preventDefault();
            } else if (password.value.length < 8) {
                showError(password, 'Password must contain at least 8 characters.');
                event.preventDefault();
            } else {
                clearError(password);
            }
            
            if (confirmPassword.value.trim() === '') {
                showError(confirmPassword, 'Password confirmation is required.');
                event.preventDefault();
            } else if (confirmPassword.value !== password.value) {
                showError(confirmPassword, 'Passwords do not match.');
                event.preventDefault();
            } else {
                clearError(confirmPassword);
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