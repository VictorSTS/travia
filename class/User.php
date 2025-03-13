<?php

class User
{
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $password;
    private $homePlanet;
    private $workPlanet;
    private $isVerified;
    private $verificationToken;
    private $tokenExpiry;
    private $pdo;

    /**
     * User constructor.
     * @param PDO $pdo Database connection
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return array Success status and message
     */
    public function setPassword($password): array
    {
        // Validate password complexity
        if (!$this->validatePasswordComplexity($password)) {
            return [
                'success' => false,
                'message' => 'Password must contain at least 8 characters, including at least one digit and one special character.'
            ];
        }
        
        // Hash the password before storing
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        return ['success' => true];
    }

    /**
     * Validate password complexity
     * @param string $password Password to validate
     * @return bool True if password meets complexity requirements
     */
    private function validatePasswordComplexity($password): bool
    {
        // Check length (at least 8 characters)
        if (strlen($password) < 8) {
            return false;
        }
        
        // Check for at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Check for at least one special character
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }

    /**
     * @return mixed
     */
    public function getHomePlanet()
    {
        return $this->homePlanet;
    }

    /**
     * @param mixed $homePlanet
     * @return array Success status and message
     */
    public function setHomePlanet($homePlanet): array
    {
        // Validate planet exists in database
        if (!$this->validatePlanet($homePlanet)) {
            return [
                'success' => false,
                'message' => 'Invalid home planet. Please select a planet from the dropdown menu.'
            ];
        }
        
        $this->homePlanet = $homePlanet;
        return ['success' => true];
    }

    /**
     * @return mixed
     */
    public function getWorkPlanet()
    {
        return $this->workPlanet;
    }

    /**
     * @param mixed $workPlanet
     * @return array Success status and message
     */
    public function setWorkPlanet($workPlanet): array
    {
        // Validate planet exists in database
        if (!$this->validatePlanet($workPlanet)) {
            return [
                'success' => false,
                'message' => 'Invalid work planet. Please select a planet from the dropdown menu.'
            ];
        }
        
        $this->workPlanet = $workPlanet;
        return ['success' => true];
    }

    /**
     * Validate planet exists in database
     * @param string $planet Planet name to validate
     * @return bool True if planet exists
     */
    private function validatePlanet($planet): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM planets WHERE name = :name");
            $stmt->execute(['name' => $planet]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getIsVerified()
    {
        return $this->isVerified;
    }

    /**
     * @param mixed $isVerified
     */
    public function setIsVerified($isVerified): void
    {
        $this->isVerified = $isVerified;
    }

    /**
     * @return mixed
     */
    public function getVerificationToken()
    {
        return $this->verificationToken;
    }

    /**
     * @param mixed $verificationToken
     */
    public function setVerificationToken($verificationToken): void
    {
        $this->verificationToken = $verificationToken;
    }

    /**
     * @return mixed
     */
    public function getTokenExpiry()
    {
        return $this->tokenExpiry;
    }

    /**
     * @param mixed $tokenExpiry
     */
    public function setTokenExpiry($tokenExpiry): void
    {
        $this->tokenExpiry = $tokenExpiry;
    }

    /**
     * Register a new user
     * @return array Success status and message
     */
    public function register()
    {
        try {
            // Check if email already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $this->email]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'This email address is already in use.'];
            }
            
            // Validate planets
            if (!$this->validatePlanet($this->homePlanet)) {
                return ['success' => false, 'message' => 'Invalid home planet. Please select a planet from the dropdown menu.'];
            }
            
            if (!$this->validatePlanet($this->workPlanet)) {
                return ['success' => false, 'message' => 'Invalid work planet. Please select a planet from the dropdown menu.'];
            }
            
            // Generate verification token
            $this->verificationToken = bin2hex(random_bytes(32));
            $this->tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 minute'));
            $this->isVerified = 0;
            
            // Insert user into database
            $sql = "INSERT INTO users (first_name, last_name, email, password, home_planet, work_planet, is_verified, verification_token, token_expiry, verification_expiry, created_at) 
                    VALUES (:firstName, :lastName, :email, :password, :homePlanet, :workPlanet, :isVerified, :verificationToken, :tokenExpiry, :verificationExpiry, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'email' => $this->email,
                'password' => $this->password,
                'homePlanet' => $this->homePlanet,
                'workPlanet' => $this->workPlanet,
                'isVerified' => $this->isVerified,
                'verificationToken' => $this->verificationToken,
                'tokenExpiry' => $this->tokenExpiry,
                'verificationExpiry' => $this->tokenExpiry // Use the same value for both columns
            ]);
            
            if ($result) {
                $this->id = $this->pdo->lastInsertId();
                $this->logAction('register', 'User registered');
                return ['success' => true, 'message' => 'Registration successful. Please check your email.'];
            } else {
                return ['success' => false, 'message' => 'Error during registration.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Verify user account with token
     * @param string $token Verification token
     * @return array Success status and message
     */
    public function verifyAccount($token)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, email, token_expiry, verification_expiry FROM users WHERE verification_token = :token AND is_verified = 0");
            $stmt->execute(['token' => $token]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Invalid verification token.'];
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $now = new DateTime();
            
            // Use token_expiry if available, otherwise use verification_expiry
            $expiryField = !empty($user['token_expiry']) ? 'token_expiry' : 'verification_expiry';
            $expiry = new DateTime($user[$expiryField]);
            
            if ($now > $expiry) {
                return ['success' => false, 'message' => 'The verification token has expired.'];
            }
            
            // Update user as verified
            $updateStmt = $this->pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id");
            $result = $updateStmt->execute(['id' => $user['id']]);
            
            if ($result) {
                $this->id = $user['id'];
                $this->email = $user['email'];
                $this->isVerified = 1;
                $this->logAction('verify', 'Account verified');
                return ['success' => true, 'message' => 'Your account has been successfully verified.'];
            } else {
                return ['success' => false, 'message' => 'Error verifying account.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Generate login verification code
     * @param string $email User email
     * @return array Success status, message and user data if successful
     */
    public function generateLoginCode($email)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, first_name, last_name, email, password, is_verified FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Email address not found.'];
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user['is_verified'] == 0) {
                return ['success' => false, 'message' => 'Your account is not yet verified. Please check your email.'];
            }
            
            // Generate 6-digit verification code
            $verificationCode = sprintf("%06d", mt_rand(1, 999999));
            $codeExpiry = date('Y-m-d H:i:s', strtotime('+1 minute'));
            
            // Store verification code in login_codes table
            $sql = "INSERT INTO login_codes (user_id, code, expiry) VALUES (:userId, :code, :expiry)";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'userId' => $user['id'],
                'code' => $verificationCode,
                'expiry' => $codeExpiry
            ]);
            
            // Also update the login_code and login_code_expiry columns in the users table for backward compatibility
            $updateSql = "UPDATE users SET login_code = :code, login_code_expiry = :expiry WHERE id = :userId";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([
                'code' => $verificationCode,
                'expiry' => $codeExpiry,
                'userId' => $user['id']
            ]);
            
            if ($result) {
                $this->id = $user['id'];
                $this->firstName = $user['first_name'];
                $this->lastName = $user['last_name'];
                $this->email = $user['email'];
                $this->isVerified = $user['is_verified'];
                $this->logAction('login_code_generated', 'Login verification code generated');
                
                return [
                    'success' => true, 
                    'message' => 'Verification code sent to your email.',
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'code' => $verificationCode,
                        'expiry' => $codeExpiry
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Error generating verification code.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Verify login code
     * @param int $userId User ID
     * @param string $code Verification code
     * @return array Success status and message
     */
    public function verifyLoginCode($userId, $code)
    {
        try {
            // First try to find the code in the login_codes table
            $stmt = $this->pdo->prepare("SELECT * FROM login_codes WHERE user_id = :userId AND code = :code ORDER BY created_at DESC LIMIT 1");
            $stmt->execute(['userId' => $userId, 'code' => $code]);
            
            $loginCodeFound = false;
            $loginCodeExpired = false;
            
            if ($stmt->rowCount() > 0) {
                $loginCodeFound = true;
                $loginCode = $stmt->fetch(PDO::FETCH_ASSOC);
                $now = new DateTime();
                $expiry = new DateTime($loginCode['expiry']);
                
                if ($now > $expiry) {
                    $loginCodeExpired = true;
                }
            }
            
            // If not found or expired, try to find the code in the users table
            if (!$loginCodeFound || $loginCodeExpired) {
                $userStmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :userId AND login_code = :code");
                $userStmt->execute(['userId' => $userId, 'code' => $code]);
                
                if ($userStmt->rowCount() === 0) {
                    return ['success' => false, 'message' => 'Invalid verification code.'];
                }
                
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);
                $now = new DateTime();
                
                if (!empty($user['login_code_expiry'])) {
                    $expiry = new DateTime($user['login_code_expiry']);
                    
                    if ($now > $expiry) {
                        return ['success' => false, 'message' => 'The verification code has expired.'];
                    }
                }
                
                // Set user properties
                $this->id = $user['id'];
                $this->firstName = $user['first_name'];
                $this->lastName = $user['last_name'];
                $this->email = $user['email'];
                $this->homePlanet = $user['home_planet'];
                $this->workPlanet = $user['work_planet'];
                $this->isVerified = $user['is_verified'];
                
                // Clear login code
                $clearStmt = $this->pdo->prepare("UPDATE users SET login_code = NULL, login_code_expiry = NULL WHERE id = :id");
                $clearStmt->execute(['id' => $user['id']]);
            } else {
                // Code found in login_codes table and not expired
                // Get user data
                $userStmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
                $userStmt->execute(['id' => $userId]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                // Set user properties
                $this->id = $user['id'];
                $this->firstName = $user['first_name'];
                $this->lastName = $user['last_name'];
                $this->email = $user['email'];
                $this->homePlanet = $user['home_planet'];
                $this->workPlanet = $user['work_planet'];
                $this->isVerified = $user['is_verified'];
                
                // Delete used verification code
                $deleteStmt = $this->pdo->prepare("DELETE FROM login_codes WHERE id = :id");
                $deleteStmt->execute(['id' => $loginCode['id']]);
                
                // Also clear login code in users table
                $clearStmt = $this->pdo->prepare("UPDATE users SET login_code = NULL, login_code_expiry = NULL WHERE id = :id");
                $clearStmt->execute(['id' => $user['id']]);
            }
            
            // Log successful login
            $this->logAction('login', 'User logged in');
            
            return [
                'success' => true, 
                'message' => 'Login successful.',
                'user' => [
                    'id' => $this->id,
                    'firstName' => $this->firstName,
                    'lastName' => $this->lastName,
                    'email' => $this->email,
                    'homePlanet' => $this->homePlanet,
                    'workPlanet' => $this->workPlanet
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get user by ID
     * @param int $id User ID
     * @return bool Success status
     */
    public function getUserById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                return false;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $user['id'];
            $this->firstName = $user['first_name'];
            $this->lastName = $user['last_name'];
            $this->email = $user['email'];
            $this->homePlanet = $user['home_planet'];
            $this->workPlanet = $user['work_planet'];
            $this->isVerified = $user['is_verified'];
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Log user actions
     * @param string $action Action type
     * @param string $details Action details
     * @return bool Success status
     */
    public function logAction($action, $details)
    {
        try {
            $sql = "INSERT INTO user_logs (user_id, action, details, ip_address) VALUES (:userId, :action, :details, :ipAddress)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'userId' => $this->id,
                'action' => $action,
                'details' => $details,
                'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Generate recovery code for password reset
     * @param string $email User email
     * @return array Success status, message and user data if successful
     */
    public function generateRecoveryCode($email)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, first_name, last_name, email, is_verified FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            
            if ($stmt->rowCount() === 0) {
                // For security, we don't want to reveal if the email exists or not
                // So we return a fake success response
                return [
                    'success' => true,
                    'message' => 'Recovery code sent to your email.',
                    'fake_response' => true
                ];
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user['is_verified'] == 0) {
                // For security, we don't want to reveal if the account is verified or not
                // So we return a fake success response
                return [
                    'success' => true,
                    'message' => 'Recovery code sent to your email.',
                    'fake_response' => true
                ];
            }
            
            // Generate 6-digit recovery code
            $recoveryCode = sprintf("%06d", mt_rand(1, 999999));
            $codeExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Store recovery code in recovery_codes table
            $sql = "INSERT INTO recovery_codes (user_id, code, expiry) VALUES (:userId, :code, :expiry)";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'userId' => $user['id'],
                'code' => $recoveryCode,
                'expiry' => $codeExpiry
            ]);
            
            if ($result) {
                $this->id = $user['id'];
                $this->firstName = $user['first_name'];
                $this->lastName = $user['last_name'];
                $this->email = $user['email'];
                $this->logAction('recovery_code_generated', 'Password recovery code generated');
                
                return [
                    'success' => true, 
                    'message' => 'Recovery code sent to your email.',
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'code' => $recoveryCode,
                        'expiry' => $codeExpiry
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Error generating recovery code.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verify recovery code
     * @param string $email User email
     * @param string $code Recovery code
     * @return array Success status and message
     */
    public function verifyRecoveryCode($email, $code)
    {
        try {
            // Get user by email
            $userStmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
            $userStmt->execute(['email' => $email]);
            
            if ($userStmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Invalid or expired verification code.'];
            }
            
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            $userId = $user['id'];
            
            // Check if recovery code exists and is valid
            $stmt = $this->pdo->prepare("
                SELECT * FROM recovery_codes 
                WHERE user_id = :userId AND code = :code AND expiry > NOW() 
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute(['userId' => $userId, 'code' => $code]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Invalid or expired verification code.'];
            }
            
            $recoveryCode = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get user data
            $userDataStmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $userDataStmt->execute(['id' => $userId]);
            $userData = $userDataStmt->fetch(PDO::FETCH_ASSOC);
            
            // Set user properties
            $this->id = $userData['id'];
            $this->firstName = $userData['first_name'];
            $this->lastName = $userData['last_name'];
            $this->email = $userData['email'];
            
            // Log action
            $this->logAction('recovery_code_verified', 'Password recovery code verified');
            
            return [
                'success' => true, 
                'message' => 'Verification code validated.',
                'user' => [
                    'id' => $this->id,
                    'email' => $this->email
                ]
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update user password
     * @param int $userId User ID
     * @param string $newPassword New password (plain text)
     * @return array Success status and message
     */
    public function updatePassword($userId, $newPassword)
    {
        try {
            // Get user data
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'User not found.'];
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Set user properties
            $this->id = $user['id'];
            $this->firstName = $user['first_name'];
            $this->lastName = $user['last_name'];
            $this->email = $user['email'];
            
            // Validate password complexity
            if (!$this->validatePasswordComplexity($newPassword)) {
                return [
                    'success' => false,
                    'message' => 'Password must contain at least 8 characters, including at least one digit and one special character.'
                ];
            }
            
            // Check if new password is different from current password
            if (password_verify($newPassword, $user['password'])) {
                return ['success' => false, 'message' => 'The new password must be different from the current one.'];
            }
            
            // Check if password history table exists
            $tableCheckStmt = $this->pdo->query("SHOW TABLES LIKE 'password_history'");
            if ($tableCheckStmt->rowCount() === 0) {
                // Create password history table if it doesn't exist
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS password_history (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        password_hash VARCHAR(255) NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    )
                ");
            }
            
            // Check if new password matches any of the last 3 passwords
            $historyStmt = $this->pdo->prepare("
                SELECT password_hash FROM password_history 
                WHERE user_id = :userId 
                ORDER BY created_at DESC LIMIT 3
            ");
            $historyStmt->execute(['userId' => $userId]);
            
            $passwordHistory = $historyStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Add current password to history check
            array_unshift($passwordHistory, $user['password']);
            
            foreach ($passwordHistory as $oldPassword) {
                if (password_verify($newPassword, $oldPassword)) {
                    return ['success' => false, 'message' => 'The new password cannot be the same as any of your last 3 passwords.'];
                }
            }
            
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Add current password to history
            $historyInsertStmt = $this->pdo->prepare("
                INSERT INTO password_history (user_id, password_hash) 
                VALUES (:userId, :password)
            ");
            $historyInsertStmt->execute([
                'userId' => $userId,
                'password' => $user['password']
            ]);
            
            // Update user password
            $updateStmt = $this->pdo->prepare("
                UPDATE users 
                SET password = :password 
                WHERE id = :id
            ");
            $updateResult = $updateStmt->execute([
                'password' => $hashedPassword,
                'id' => $userId
            ]);
            
            // Delete all recovery codes for this user
            $deleteStmt = $this->pdo->prepare("
                DELETE FROM recovery_codes 
                WHERE user_id = :userId
            ");
            $deleteStmt->execute(['userId' => $userId]);
            
            // Commit transaction
            $this->pdo->commit();
            
            // Log action
            $this->logAction('password_updated', 'Password updated');
            
            return ['success' => true, 'message' => 'Password successfully updated.'];
        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
} 