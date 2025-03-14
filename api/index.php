<?php
// Include API configuration
require_once '../config/api_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travia Tour API Documentation</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #121212;
            color: #ffffff;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            margin-bottom: 20px;
            background-color: #1e1e1e;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #333;
            color: white;
        }
        pre {
            background-color: #2a2a2a;
            color: #e0e0e0;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .method-badge {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 10px;
        }
        .method-post {
            background-color: #28a745;
        }
        .method-get {
            background-color: #007bff;
        }
        .endpoint-url {
            background-color: #2a2a2a;
            padding: 8px 15px;
            border-radius: 4px;
            font-family: monospace;
            margin-bottom: 15px;
            display: inline-block;
        }
        .api-version {
            font-size: 14px;
            color: #aaa;
            margin-left: 10px;
        }
        .client-link {
            margin-right: 15px;
            margin-bottom: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">
            Travia Tour API Documentation
            <span class="api-version">v<?php echo API_VERSION; ?></span>
        </h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">API Overview</h5>
            </div>
            <div class="card-body">
                <p>The Travia Tour API provides programmatic access to user verification and authentication services. This API follows REST principles and returns data in JSON format.</p>
                
                <h5 class="mt-4">Test Clients</h5>
                <div>
                    <a href="api/js_client.html" class="btn btn-outline-info client-link">JavaScript Client</a>
                    <a href="api/test.php" class="btn btn-outline-success client-link">API Status Test</a>
                </div>
                
                <h5 class="mt-4">Authentication</h5>
                <p>All API requests require an API token for authentication. This token should be included in the request parameters.</p>
            </div>
        </div>
    </div>
</body>
</html> 