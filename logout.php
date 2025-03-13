<?php
// Start session
session_start();

// Include necessary files
require_once 'class/User.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        // Connect to database
        $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create user object
        $user = new User($pdo);
        $user->setId($_SESSION['user_id']);
        
        // Log logout action
        $user->logAction('logout', 'User logged out');
    } catch (PDOException $e) {
        // Silently fail
    }
}

// Destroy session
session_unset();
session_destroy();

// Redirect to home page
header('Location: index.php');
exit; 