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
$formData = [
    'firstName' => '',
    'lastName' => '',
    'email' => '',
    'password' => '',
    'confirmPassword' => '',
    'homePlanet' => '',
    'workPlanet' => ''
];

$captcha = new Captcha(false);
$simpleCaptcha = $captcha->generateSimpleCaptcha();

// Restore form data from session if available
if (isset($_SESSION['register_form_data'])) {
    $formData = $_SESSION['register_form_data'];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $formData = [
        'firstName' => $_POST['firstName'] ?? '',
        'lastName' => $_POST['lastName'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirmPassword' => $_POST['confirmPassword'] ?? '',
        'homePlanet' => $_POST['homePlanet'] ?? '',
        'workPlanet' => $_POST['workPlanet'] ?? ''
    ];
    
    // Store form data in session to handle reloads
    $_SESSION['register_form_data'] = $formData;
    
    // Verify CAPTCHA
    $simpleCaptchaAnswer = isset($_POST['captcha_answer']) ? (int)$_POST['captcha_answer'] : 0;
    $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    
    $captchaResult = $captcha->verify($simpleCaptchaAnswer, $recaptchaResponse);
    
    if (!$captchaResult['success']) {
        $errors['captcha'] = $captchaResult['message'];
        
        // Don't regenerate simple CAPTCHA on error - it's handled in the Captcha class
    } else {
        // Clear stored form data on success
        unset($_SESSION['register_form_data']);
        
        // Validate form data
        if (empty($formData['firstName'])) {
            $errors['firstName'] = 'First name is required.';
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $formData['firstName'])) {
            $errors['firstName'] = 'First name contains unauthorized characters.';
        }
        
        if (empty($formData['lastName'])) {
            $errors['lastName'] = 'Last name is required.';
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $formData['lastName'])) {
            $errors['lastName'] = 'Last name contains unauthorized characters.';
        }
        
        if (empty($formData['email'])) {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email address is not valid.';
        }
        
        if (empty($formData['password'])) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($formData['password']) < 8) {
            $errors['password'] = 'Password must contain at least 8 characters.';
        } elseif (!preg_match('/[0-9]/', $formData['password'])) {
            $errors['password'] = 'Password must contain at least one digit.';
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $formData['password'])) {
            $errors['password'] = 'Password must contain at least one special character.';
        }
        
        if ($formData['password'] !== $formData['confirmPassword']) {
            $errors['confirmPassword'] = 'Passwords do not match.';
        }
        
        if (empty($formData['homePlanet'])) {
            $errors['homePlanet'] = 'Home planet is required.';
        }
        
        if (empty($formData['workPlanet'])) {
            $errors['workPlanet'] = 'Work planet is required.';
        }
        
        // If no errors, register user
        if (empty($errors)) {
            try {
                // Connect to database
                $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create user object
                $user = new User($pdo);
                $user->setFirstName($formData['firstName']);
                $user->setLastName($formData['lastName']);
                $user->setEmail($formData['email']);
                
                // Set password with validation
                $passwordResult = $user->setPassword($formData['password']);
                if (!$passwordResult['success']) {
                    $errors['password'] = $passwordResult['message'];
                    throw new Exception($passwordResult['message']);
                }
                
                // Set home planet with validation
                $homePlanetResult = $user->setHomePlanet($formData['homePlanet']);
                if (!$homePlanetResult['success']) {
                    $errors['homePlanet'] = $homePlanetResult['message'];
                    throw new Exception($homePlanetResult['message']);
                }
                
                // Set work planet with validation
                $workPlanetResult = $user->setWorkPlanet($formData['workPlanet']);
                if (!$workPlanetResult['success']) {
                    $errors['workPlanet'] = $workPlanetResult['message'];
                    throw new Exception($workPlanetResult['message']);
                }
                
                // Register user
                $result = $user->register();
                
                if ($result['success']) {
                    // Send verification email
                    $mailer = new Mailer();
                    $emailResult = $mailer->sendVerificationEmail(
                        $user->getEmail(),
                        $user->getFirstName(),
                        $user->getVerificationToken()
                    );
                    
                    if ($emailResult['success']) {
                        // Redirect to registration success page
                        $_SESSION['registration_email'] = $user->getEmail();
                        header('Location: register_success.php');
                        exit;
                    } else {
                        $errors['email'] = $emailResult['message'];
                    }
                } else {
                    $errors['general'] = $result['message'];
                }
            } catch (PDOException $e) {
                $errors['general'] = 'Database error: ' . $e->getMessage();
            } catch (Exception $e) {
                $errors['general'] = $e->getMessage();
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
    <title>Register - Travia Tour</title>
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
        .autocomplete-dropdown {
            position: absolute;
            background-color: #333333;
            border: 1px solid #555555;
            z-index: 1000;
            max-height: 150px;
            overflow-y: auto;
            width: 100%;
        }
        .autocomplete-dropdown li {
            list-style: none;
            padding: 8px;
            cursor: pointer;
            color: #ffffff;
        }
        .autocomplete-dropdown li:hover {
            background-color: #444444;
        }
        .invalid-feedback {
            display: block;
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
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
            </ul>
        </div>
        <button class="btn btn-link text-white" id="toggleFont">Translate to Aurebesh</button>
    </nav>

    <div class="container">
        <h1 class="mt-5">Register</h1>
        <p>Create your account to access all features of Travia Tour.</p>

        <div class="card mt-4">
            <div class="card-body">
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                <?php endif; ?>

                <form id="registerForm" method="POST" action="register.php" novalidate>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="firstName">First name</label>
                            <input type="text" class="form-control <?php echo isset($errors['firstName']) ? 'is-invalid' : ''; ?>" id="firstName" name="firstName" value="<?php echo htmlspecialchars($formData['firstName']); ?>" required>
                            <?php if (isset($errors['firstName'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['firstName']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="lastName">Last name</label>
                            <input type="text" class="form-control <?php echo isset($errors['lastName']) ? 'is-invalid' : ''; ?>" id="lastName" name="lastName" value="<?php echo htmlspecialchars($formData['lastName']); ?>" required>
                            <?php if (isset($errors['lastName'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['lastName']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="password">Password</label>
                            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="confirmPassword">Confirm password</label>
                            <input type="password" class="form-control <?php echo isset($errors['confirmPassword']) ? 'is-invalid' : ''; ?>" id="confirmPassword" name="confirmPassword" required>
                            <?php if (isset($errors['confirmPassword'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['confirmPassword']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6 position-relative">
                            <label for="homePlanet">Home planet</label>
                            <input type="text" class="form-control <?php echo isset($errors['homePlanet']) ? 'is-invalid' : ''; ?>" id="homePlanet" name="homePlanet" value="<?php echo htmlspecialchars($formData['homePlanet']); ?>" required>
                            <ul class="autocomplete-dropdown" id="homePlanetDropdown"></ul>
                            <?php if (isset($errors['homePlanet'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['homePlanet']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group col-md-6 position-relative">
                            <label for="workPlanet">Work planet</label>
                            <input type="text" class="form-control <?php echo isset($errors['workPlanet']) ? 'is-invalid' : ''; ?>" id="workPlanet" name="workPlanet" value="<?php echo htmlspecialchars($formData['workPlanet']); ?>" required>
                            <ul class="autocomplete-dropdown" id="workPlanetDropdown"></ul>
                            <?php if (isset($errors['workPlanet'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['workPlanet']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($simpleCaptcha['enabled']): ?>
                    <div class="form-group">
                        <label for="captcha_answer">Simple CAPTCHA: <?php echo $simpleCaptcha['num1']; ?> + <?php echo $simpleCaptcha['num2']; ?> = ?</label>
                        <input type="number" class="form-control <?php echo isset($errors['captcha']) ? 'is-invalid' : ''; ?>" id="captcha_answer" name="captcha_answer" required>
                        <?php if (isset($errors['captcha'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['captcha']; ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php echo $captcha->getRecaptchaHtml(); ?>
                    
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                <div class="mt-3">
                    <p>Already have an account? <a href="login.php">Log in</a></p>
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
        // Client-side validation
        document.getElementById('registerForm').addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validate first name
            const firstName = document.getElementById('firstName');
            if (firstName.value.trim() === '') {
                showError(firstName, 'First name is required.');
                isValid = false;
            } else if (!/^[a-zA-ZÀ-ÿ\s\-]+$/.test(firstName.value)) {
                showError(firstName, 'First name contains unauthorized characters.');
                isValid = false;
            } else {
                clearError(firstName);
            }
            
            // Validate last name
            const lastName = document.getElementById('lastName');
            if (lastName.value.trim() === '') {
                showError(lastName, 'Last name is required.');
                isValid = false;
            } else if (!/^[a-zA-ZÀ-ÿ\s\-]+$/.test(lastName.value)) {
                showError(lastName, 'Last name contains unauthorized characters.');
                isValid = false;
            } else {
                clearError(lastName);
            }
            
            // Validate email
            const email = document.getElementById('email');
            if (email.value.trim() === '') {
                showError(email, 'Email address is required.');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                showError(email, 'Email address is not valid.');
                isValid = false;
            } else {
                clearError(email);
            }
            
            // Validate password
            const password = document.getElementById('password');
            if (password.value === '') {
                showError(password, 'Password is required.');
                isValid = false;
            } else if (password.value.length < 8) {
                showError(password, 'Password must contain at least 8 characters.');
                isValid = false;
            } else if (!/[0-9]/.test(password.value)) {
                showError(password, 'Password must contain at least one digit.');
                isValid = false;
            } else if (!/[^a-zA-Z0-9]/.test(password.value)) {
                showError(password, 'Password must contain at least one special character.');
                isValid = false;
            } else {
                clearError(password);
            }
            
            // Validate confirm password
            const confirmPassword = document.getElementById('confirmPassword');
            if (confirmPassword.value === '') {
                showError(confirmPassword, 'Password confirmation is required.');
                isValid = false;
            } else if (confirmPassword.value !== password.value) {
                showError(confirmPassword, 'Passwords do not match.');
                isValid = false;
            } else {
                clearError(confirmPassword);
            }
            
            // Validate home planet
            const homePlanet = document.getElementById('homePlanet');
            if (homePlanet.value.trim() === '') {
                showError(homePlanet, 'Home planet is required.');
                isValid = false;
            } else {
                // Check if planet is in dropdown
                let validPlanet = false;
                const homePlanetDropdown = document.getElementById('homePlanetDropdown');
                const planetOptions = homePlanetDropdown.querySelectorAll('li');
                
                // If no options are loaded yet, we'll let the server validate
                if (planetOptions.length === 0) {
                    validPlanet = true;
                } else {
                    for (let i = 0; i < planetOptions.length; i++) {
                        if (planetOptions[i].textContent === homePlanet.value) {
                            validPlanet = true;
                            break;
                        }
                    }
                }
                
                if (!validPlanet) {
                    showError(homePlanet, 'Please select a planet from the dropdown menu.');
                    isValid = false;
                } else {
                    clearError(homePlanet);
                }
            }
            
            // Validate work planet
            const workPlanet = document.getElementById('workPlanet');
            if (workPlanet.value.trim() === '') {
                showError(workPlanet, 'Work planet is required.');
                isValid = false;
            } else {
                // Check if planet is in dropdown
                let validPlanet = false;
                const workPlanetDropdown = document.getElementById('workPlanetDropdown');
                const planetOptions = workPlanetDropdown.querySelectorAll('li');
                
                // If no options are loaded yet, we'll let the server validate
                if (planetOptions.length === 0) {
                    validPlanet = true;
                } else {
                    for (let i = 0; i < planetOptions.length; i++) {
                        if (planetOptions[i].textContent === workPlanet.value) {
                            validPlanet = true;
                            break;
                        }
                    }
                }
                
                if (!validPlanet) {
                    showError(workPlanet, 'Please select a planet from the dropdown menu.');
                    isValid = false;
                } else {
                    clearError(workPlanet);
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
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
        
        // Autocomplete for planets
        function showAutocomplete(input, dropdown, query) {
            if (query.length >= 2) {
                $.get('autocomplete.php', { query: query }, function(data) {
                    if (data.error) {
                        console.error('Error fetching autocomplete suggestions:', data.error);
                        return;
                    }
                    if (data.suggestions && data.suggestions.length > 0) {
                        var suggestions = data.suggestions;
                        dropdown.empty();
                        suggestions.forEach(function(planet) {
                            dropdown.append('<li>' + planet + '</li>');
                        });
                        dropdown.show();
                    } else {
                        dropdown.hide();
                    }
                });
            } else {
                dropdown.hide();
            }
        }
        
        $('#homePlanet').on('input', function() {
            var query = $(this).val();
            showAutocomplete($(this), $('#homePlanetDropdown'), query);
        });
        
        $('#workPlanet').on('input', function() {
            var query = $(this).val();
            showAutocomplete($(this), $('#workPlanetDropdown'), query);
        });
        
        $('body').on('click', '.autocomplete-dropdown li', function() {
            var input = $(this).parent().prev('input');
            input.val($(this).text());
            $(this).parent().hide();
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.form-group').length) {
                $('.autocomplete-dropdown').hide();
            }
        });
        
        $('#toggleFont').on('click', function() {
            $('body').toggleClass('aurebesh');
        });
        
        $('head').append('<style>.aurebesh { font-family: "Aurebesh", sans-serif; }</style>');
    </script>
</body>
</html> 