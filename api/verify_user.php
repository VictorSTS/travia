<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set headers for REST API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Custom error handler to ensure JSON output
function handleError($errno, $errstr, $errfile, $errline) {
    $response = [
        'connexion' => false,
        'error' => 'Server error: ' . $errstr
    ];
    echo json_encode($response);
    exit;
}

// Set custom error handler
set_error_handler('handleError');

try {
    // Include API configuration
    require_once '../config/api_config.php';

    // Function to generate a new token for third-party applications
    function generateToken($userId) {
        return bin2hex(random_bytes(32));
    }

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get raw data from the request
        $data = json_decode(file_get_contents('php://input'), true);
        
        // If data is not provided via JSON, check POST variables
        if (!$data) {
            $data = [
                'email' => $_POST['email'] ?? null,
                'password_hash' => $_POST['password_hash'] ?? null,
                'token' => $_POST['token'] ?? null
            ];
        }
        
        // Validate required fields
        if (!isset($data['email']) || !isset($data['password_hash']) || !isset($data['token'])) {
            echo json_encode([
                'connexion' => false,
                'error' => 'Missing required fields: email, password_hash, and token are required'
            ]);
            exit;
        }
        
        // Sanitize inputs
        $email = htmlspecialchars(strip_tags($data['email']));
        $passwordHash = htmlspecialchars(strip_tags($data['password_hash']));
        $token = htmlspecialchars(strip_tags($data['token']));
        
        // Check if the token is valid using the API_TOKEN from config
        if ($token !== API_TOKEN) {
            echo json_encode([
                'connexion' => false,
                'error' => 'Invalid API token'
            ]);
            exit;
        }
        
        try {
            // Create database connection directly like in getPlanets.php
            $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // First, get the user by email only
            $query = "SELECT id, first_name, last_name, email, password, is_verified, created_at FROM users 
                    WHERE email = :email";
            
            $stmt = $pdo->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':email', $email);
            
            // Execute query
            $stmt->execute();
            
            // Check if user exists
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Check if user is verified
                if ($row['is_verified'] != 1) {
                    echo json_encode([
                        'connexion' => false,
                        'error' => 'Account not verified'
                    ]);
                    exit;
                }
                
                // For bcrypt passwords, we need to use password_verify with the raw password
                // The client sends password_hash but for bcrypt we need the original password
                if (password_verify($passwordHash, $row['password'])) {
                    // Generate a new token for third-party applications
                    $userToken = generateToken($row['id']);
                    
                    // Store the token in the database
                    $updateTokenQuery = "UPDATE users SET api_token = :token, api_token_expiry = DATE_ADD(NOW(), INTERVAL :expiry SECOND) WHERE id = :id";
                    $updateStmt = $pdo->prepare($updateTokenQuery);
                    $updateStmt->bindParam(':token', $userToken);
                    $updateStmt->bindParam(':id', $row['id']);
                    $tokenExpiration = TOKEN_EXPIRATION;
                    $updateStmt->bindParam(':expiry', $tokenExpiration, PDO::PARAM_INT);
                    $updateStmt->execute();
                    
                    // Return successful response
                    echo json_encode([
                        'connexion' => true,
                        'user_id' => $row['id'],
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'email' => $row['email'],
                        'verified_account' => true,
                        'creation_datetime' => $row['created_at'],
                        'token' => $userToken
                    ]);
                } else {
                    // Password doesn't match
                    echo json_encode([
                        'connexion' => false,
                        'error' => 'Invalid credentials'
                    ]);
                }
            } else {
                // User doesn't exist
                echo json_encode([
                    'connexion' => false,
                    'error' => 'User does not exist'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'connexion' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    } else {
        // If not a POST request
        echo json_encode([
            'connexion' => false,
            'error' => 'Only POST method is allowed'
        ]);
    }
} catch (Exception $e) {
    // Catch any other exceptions
    echo json_encode([
        'connexion' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 