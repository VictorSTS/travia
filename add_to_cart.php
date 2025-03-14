<?php
// Start session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get POST data
$departure = $_POST['departure'] ?? '';
$arrival = $_POST['arrival'] ?? '';
$distance = floatval($_POST['distance'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

// Validate data
if (empty($departure) || empty($arrival)) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing departure or arrival planet'
    ]);
    exit;
}

if ($quantity < 1 || $quantity > 10) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid quantity. Must be between 1 and 10.'
    ]);
    exit;
}

// Create a unique ID for this route
$routeId = md5($departure . '-' . $arrival);

// Check if this route is already in the cart
$routeExists = false;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] === $routeId) {
        // Update quantity instead of adding a new item
        $_SESSION['cart'][$key]['quantity'] += $quantity;
        
        // Cap at maximum of 10 tickets per route
        if ($_SESSION['cart'][$key]['quantity'] > 10) {
            $_SESSION['cart'][$key]['quantity'] = 10;
        }
        
        $routeExists = true;
        break;
    }
}

// If route doesn't exist in cart, add it
if (!$routeExists) {
    // Get current timestamp for the order date
    $orderDate = date('Y-m-d H:i:s');
    
    // Add to cart
    $_SESSION['cart'][] = [
        'id' => $routeId,
        'departure' => $departure,
        'arrival' => $arrival,
        'distance' => $distance,
        'price' => $price,
        'quantity' => $quantity,
        'total' => $price * $quantity,
        'date_added' => $orderDate
    ];
}

// Calculate total items in cart
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartCount += $item['quantity'];
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Item added to cart',
    'cartCount' => $cartCount
]); 