<?php
// scripts/create_recovery_tables.php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting database setup...\n";

// Connect to the database
try {
    echo "Connecting to database...\n";
    $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if recovery_codes table exists
    echo "Checking if recovery_codes table exists...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'recovery_codes'");
    if ($stmt->rowCount() === 0) {
        echo "The recovery_codes table does not exist.\n";
        echo "Creating the recovery_codes table...\n";
        
        // Create recovery_codes table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS recovery_codes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                code VARCHAR(10) NOT NULL,
                expiry DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        echo "The recovery_codes table has been created successfully.\n";
    } else {
        echo "The recovery_codes table already exists.\n";
    }
    
    // Check if password_history table exists
    echo "Checking if password_history table exists...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'password_history'");
    if ($stmt->rowCount() === 0) {
        echo "The password_history table does not exist.\n";
        echo "Creating the password_history table...\n";
        
        // Create password_history table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS password_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        echo "The password_history table has been created successfully.\n";
    } else {
        echo "The password_history table already exists.\n";
    }
    
    echo "Database setup completed successfully.\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} 