<?php
// Start session
session_start();

// Clear CAPTCHA-related session variables
unset($_SESSION['simple_captcha_result']);
unset($_SESSION['simple_captcha_num1']);
unset($_SESSION['simple_captcha_num2']);
unset($_SESSION['simple_captcha_attempts']);
unset($_SESSION['captcha_verified']);
unset($_SESSION['login_form_email']);
unset($_SESSION['login_form_captcha_answer']);
unset($_SESSION['recovery_form_email']);
unset($_SESSION['recovery_form_captcha_answer']);
unset($_SESSION['register_form_data']);

// Display success message
echo "CAPTCHA session data cleared successfully.";
echo "<br><br>";
echo "<a href='captcha_test.php'>Go to CAPTCHA Test Page</a>";
echo "<br>";
echo "<a href='login.php'>Go to Login Page</a>";
echo "<br>";
echo "<a href='forgot_password.php'>Go to Password Recovery Page</a>";
echo "<br>";
echo "<a href='register.php'>Go to Registration Page</a>";