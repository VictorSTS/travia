<?php
// Set headers for JSON output
header('Content-Type: application/json');

// Include API configuration
require_once '../config/api_config.php';

// Collect system information
$info = [
    'api_version' => API_VERSION,
    'php_version' => phpversion(),
    'php_extensions' => get_loaded_extensions(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'server_protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
    'request_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'headers' => getallheaders(),
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'display_errors' => ini_get('display_errors'),
    'error_reporting' => ini_get('error_reporting'),
    'timezone' => date_default_timezone_get(),
    'json_support' => function_exists('json_encode') && function_exists('json_decode'),
    'curl_support' => function_exists('curl_init'),
    'pdo_support' => extension_loaded('pdo'),
    'mysql_support' => extension_loaded('pdo_mysql'),
    'database_test' => false,
    'database_error' => null
];

// Test database connection
try {
    // Create database connection directly like in getPlanets.php
    $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simple query to test connection
    $stmt = $pdo->query("SELECT 1");
    $info['database_test'] = true;
    
    // Get database information
    $info['database_info'] = [
        'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'client_version' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
        'connection_status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
        'driver_name' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME)
    ];
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $info['users_table_exists'] = $stmt->rowCount() > 0;
    
    if ($info['users_table_exists']) {
        // Get users table structure
        $stmt = $pdo->query("DESCRIBE users");
        $info['users_table_columns'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Check if the API token columns exist
        $info['api_token_column_exists'] = in_array('api_token', $info['users_table_columns']);
        $info['api_token_expiry_column_exists'] = in_array('api_token_expiry', $info['users_table_columns']);
        
        // Check if there are any users with API tokens
        if ($info['api_token_column_exists']) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE api_token IS NOT NULL");
            $info['users_with_api_tokens'] = (int)$stmt->fetchColumn();
        }
    }
} catch (PDOException $e) {
    $info['database_error'] = $e->getMessage();
}

// Output the information
echo json_encode($info, JSON_PRETTY_PRINT);
?> 