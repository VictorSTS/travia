<?php
// Set headers for JSON output
header('Content-Type: application/json');

// Detailed error reporting for diagnostics
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test results array
$results = [
    'status' => 'running',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
        'http_host' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    ],
    'file_access' => [],
    'config' => [],
    'database' => []
];

// Test file access
$apiFiles = [
    '../config/api_config.php' => 'API Configuration',
    'test.php' => 'API Test Script',
    'verify_user.php' => 'User Verification API'
];

foreach ($apiFiles as $file => $description) {
    $results['file_access'][$file] = [
        'description' => $description,
        'exists' => file_exists($file),
        'readable' => is_readable($file),
        'path' => realpath($file) ?: 'Not found'
    ];
}

// Test config loading
try {
    // Check if config file exists and is readable
    if (file_exists('../config/api_config.php') && is_readable('../config/api_config.php')) {
        // Include API configuration
        require_once '../config/api_config.php';
        
        $results['config']['loaded'] = true;
        $results['config']['api_token_defined'] = defined('API_TOKEN');
        $results['config']['api_version_defined'] = defined('API_VERSION');
        $results['config']['token_expiration_defined'] = defined('TOKEN_EXPIRATION');
        
        if (defined('API_VERSION')) {
            $results['config']['api_version'] = API_VERSION;
        }
    } else {
        $results['config']['loaded'] = false;
        $results['config']['error'] = 'Config file not found or not readable';
    }
} catch (Exception $e) {
    $results['config']['loaded'] = false;
    $results['config']['error'] = 'Exception loading config: ' . $e->getMessage();
}

// Test database connection
try {
    $results['database']['connection_attempt'] = true;
    
    // Create database connection
    $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test connection
    $stmt = $pdo->query("SELECT 1");
    $results['database']['connected'] = true;
    
    // Get database info
    $results['database']['server_info'] = [
        'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'client_version' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
        'connection_status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
        'driver_name' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME)
    ];
    
    // Check users table
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        $results['database']['users_table_exists'] = $stmt->rowCount() > 0;
        
        if ($results['database']['users_table_exists']) {
            $stmt = $pdo->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $results['database']['users_table_columns'] = $columns;
            
            // Check for required fields
            $required_fields = [
                'id', 'first_name', 'last_name', 'email', 'password', 
                'is_verified', 'api_token', 'api_token_expiry'
            ];
            
            $results['database']['missing_fields'] = array_diff($required_fields, $columns);
            $results['database']['has_all_required_fields'] = empty($results['database']['missing_fields']);
        }
    } catch (PDOException $e) {
        $results['database']['users_table_error'] = $e->getMessage();
    }
} catch (PDOException $e) {
    $results['database']['connected'] = false;
    $results['database']['error'] = $e->getMessage();
}

// Set final status
$results['status'] = 'completed';

// Output the results
echo json_encode($results, JSON_PRETTY_PRINT);
?> 