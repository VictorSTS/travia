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
$action = $_POST['action'] ?? '';

// Handle different actions
switch ($action) {
    case 'update':
        // Update item quantity
        $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
        $change = isset($_POST['change']) ? intval($_POST['change']) : 0;
        
        // Validate data
        if ($index < 0 || $index >= count($_SESSION['cart']) || $change === 0) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid parameters'
            ]);
            exit;
        }
        
        // Update quantity
        $newQuantity = $_SESSION['cart'][$index]['quantity'] + $change;
        
        // Ensure quantity is within valid range
        if ($newQuantity < 1) {
            // If quantity would be less than 1, remove the item
            array_splice($_SESSION['cart'], $index, 1);
        } elseif ($newQuantity > 10) {
            // Cap at maximum of 10 tickets per route
            $_SESSION['cart'][$index]['quantity'] = 10;
        } else {
            // Set the new quantity
            $_SESSION['cart'][$index]['quantity'] = $newQuantity;
            
            // Update the total price
            $_SESSION['cart'][$index]['total'] = $_SESSION['cart'][$index]['price'] * $newQuantity;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);
        break;
        
    case 'remove':
        // Remove item from cart
        $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
        
        // Validate data
        if ($index < 0 || $index >= count($_SESSION['cart'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid index'
            ]);
            exit;
        }
        
        // Remove the item
        array_splice($_SESSION['cart'], $index, 1);
        
        echo json_encode([
            'success' => true,
            'message' => 'Item removed successfully'
        ]);
        break;
        
    case 'clear':
        // Clear the entire cart
        $_SESSION['cart'] = [];
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
        break;
} 