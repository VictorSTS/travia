<?php
// Set headers for JSON output
header('Content-Type: application/json');

// Include API configuration
require_once '../config/api_config.php';

// Test database connection
$db_connection = false;
$db_error = null;
$users_table_structure = null;

try {
    // Create database connection directly like in getPlanets.php
    $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simple query to test connection
    $stmt = $pdo->query("SELECT 1");
    $db_connection = true;
    
    // Check users table structure
    try {
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Check for required fields
        $required_fields = [
            'id', 'first_name', 'last_name', 'email', 'password', 
            'is_verified', 'api_token', 'api_token_expiry'
        ];
        
        $missing_fields = array_diff($required_fields, $columns);
        
        $users_table_structure = [
            'exists' => true,
            'columns' => $columns,
            'missing_required_fields' => $missing_fields,
            'has_all_required_fields' => empty($missing_fields)
        ];
    } catch (PDOException $e) {
        $users_table_structure = [
            'exists' => false,
            'error' => $e->getMessage()
        ];
    }
} catch (PDOException $e) {
    $db_error = $e->getMessage();
}

// Test response
$response = [
    'status' => 'success',
    'message' => 'API is working correctly',
    'api_version' => API_VERSION,
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'database_connection' => $db_connection,
    'database_error' => $db_error,
    'users_table' => $users_table_structure
];

// Output the response
echo json_encode($response);
?> 