<?php

// Connect to the database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            home_planet VARCHAR(100) NOT NULL,
            work_planet VARCHAR(100) NOT NULL,
            is_verified TINYINT(1) DEFAULT 0,
            verification_token VARCHAR(255) DEFAULT NULL,
            token_expiry DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Create login_codes table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            code VARCHAR(10) NOT NULL,
            expiry DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create user_logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            details TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    // API changes
    $pdo->exec("
        ALTER TABLE users 
        ADD COLUMN api_token VARCHAR(255) NULL,
        ADD COLUMN api_token_expiry DATETIME NULL,
        ADD INDEX idx_api_token (api_token);
    ");
    
    echo "Tables created successfully!";
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} 