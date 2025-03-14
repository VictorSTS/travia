<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php?redirect=checkout.php');
    exit;
}

// Initialize variables
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Redirect to cart page if cart is empty
    header('Location: cart.php');
    exit;
}

// Calculate cart totals
$cartTotal = 0;
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
    $cartCount += $item['quantity'];
}

// Process order submission
$orderComplete = false;
$orderNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // Generate order number (no database insertion needed)
    $orderNumber = 'TRV-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    
    // Store order details in session for receipt
    $_SESSION['last_order'] = [
        'order_number' => $orderNumber,
        'user_name' => $userName,
        'user_email' => $userEmail,
        'items' => $_SESSION['cart'],
        'total' => $cartTotal,
        'tax' => $cartTotal * 0.05,
        'grand_total' => $cartTotal * 1.05,
        'date' => date('Y-m-d H:i:s')
    ];
    
    // Clear the cart
    $_SESSION['cart'] = [];
    
    // Set order complete flag
    $orderComplete = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Travia Tour</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        @font-face {
            font-family: Aurebesh;
            src: url("/fonts/Aurebesh.otf") format("opentype")
        }
        body {
            padding-top: 56px;
            background-color: #121212;
            color: #ffffff;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .navbar {
            background-color: #1e1e1e;
        }
        .navbar-brand, .nav-link {
            color: #ffffff;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .checkout-item {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            background-color: #1e1e1e;
        }
        .checkout-summary {
            background-color: #1e1e1e;
            border-radius: 8px;
            padding: 20px;
        }
        .order-success {
            text-align: center;
            padding: 30px 0;
        }
        .order-success i {
            font-size: 5em;
            color: #28a745;
            margin-bottom: 20px;
        }
        .receipt {
            background-color: #1e1e1e;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .receipt-header {
            border-bottom: 1px solid #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .receipt-item {
            padding: 10px 0;
            border-bottom: 1px dashed #333;
        }
        .receipt-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #333;
        }
        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }
            .navbar, .footer, .no-print {
                display: none !important;
            }
            .receipt {
                background-color: white !important;
                color: black !important;
                border: 1px solid #ddd;
            }
            .receipt-item {
                border-bottom: 1px dashed #ddd;
            }
            .receipt-total {
                border-top: 1px solid #ddd;
            }
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-print, .btn-continue {
            padding: 0 20px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 4px;
            min-width: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 50px;
            text-decoration: none;
        }
        .btn-print {
            background-color: #333;
            border-color: #333;
            color: white;
            transition: all 0.2s ease;
        }
        .btn-print:hover {
            background-color: #444;
            border-color: #444;
            color: white;
            text-decoration: none;
        }
        .btn-continue {
            background-color: #0095ff;
            border-color: #0095ff;
            color: white;
            transition: all 0.2s ease;
        }
        .btn-continue:hover {
            background-color: #0085e5;
            border-color: #0085e5;
            color: white;
            text-decoration: none;
        }
        .btn-print i, .btn-continue i {
            color: white;
            font-size: 1.2em;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">Travia Tour</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cart.php">Cart</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo htmlspecialchars($userName); ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="logout.php">Logout</a>
                </div>
            </li>
        </ul>
    </div>
    <button class="btn btn-link text-white" id="toggleFont">Translate to Aurebesh</button>
</nav>

<div class="container">
    <?php if ($orderComplete): ?>
        <!-- Order success message -->
        <div class="order-success">
            <i class="fas fa-check-circle"></i>
            <h2>Order Successful!</h2>
            <p>Your order has been placed successfully.</p>
            <p>Order Number: <strong><?php echo htmlspecialchars($orderNumber); ?></strong></p>
            <p>A confirmation email will be sent to <strong><?php echo htmlspecialchars($userEmail); ?></strong>.</p>
            
            <!-- Receipt -->
            <div class="receipt">
                <div class="receipt-header text-center">
                    <h3>Travia Tour</h3>
                    <p>Intergalactic Travel Receipt</p>
                    <p>Order #<?php echo htmlspecialchars($orderNumber); ?></p>
                    <p><?php echo date('F j, Y, g:i a'); ?></p>
                </div>
                
                <div class="receipt-customer">
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($userName); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
                </div>
                
                <div class="receipt-items mt-4">
                    <h5>Order Items:</h5>
                    <?php foreach ($_SESSION['last_order']['items'] as $item): ?>
                        <div class="receipt-item">
                            <div class="row">
                                <div class="col-7">
                                    <p>
                                        <strong><?php echo htmlspecialchars($item['departure']); ?> to <?php echo htmlspecialchars($item['arrival']); ?></strong><br>
                                        <small>Distance: <?php echo number_format($item['distance'], 2); ?> light-years</small>
                                    </p>
                                </div>
                                <div class="col-2 text-right">
                                    <p><?php echo $item['quantity']; ?> x</p>
                                </div>
                                <div class="col-3 text-right">
                                    <p><?php echo number_format($item['price'] * $item['quantity']); ?> credits</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="receipt-total">
                    <div class="row">
                        <div class="col-8 text-right">
                            <p>Subtotal:</p>
                            <p>Tax (5%):</p>
                            <p><strong>Total:</strong></p>
                        </div>
                        <div class="col-4 text-right">
                            <p><?php echo number_format($_SESSION['last_order']['total']); ?> credits</p>
                            <p><?php echo number_format($_SESSION['last_order']['tax']); ?> credits</p>
                            <p><strong><?php echo number_format($_SESSION['last_order']['grand_total']); ?> credits</strong></p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p>Thank you for choosing Travia Tour for your intergalactic travel needs!</p>
                </div>
            </div>
            
            <div class="action-buttons no-print">
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print" aria-hidden="true"></i><span>Print Receipt</span>
                </button>
                <a href="index.php" class="btn btn-continue">
                    <i class="fas fa-home" aria-hidden="true"></i><span>Continue Shopping</span>
                </a>
            </div>
        </div>
    <?php else: ?>
        <h1 class="mt-5 mb-4">Checkout</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card bg-dark mb-4">
                    <div class="card-header">
                        <h4>Order Review</h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="checkout-item">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5>
                                            <i class="fas fa-rocket mr-2"></i>
                                            <?php echo htmlspecialchars($item['departure']); ?> 
                                            <i class="fas fa-arrow-right mx-2"></i> 
                                            <?php echo htmlspecialchars($item['arrival']); ?>
                                        </h5>
                                        <p>Distance: <?php echo number_format($item['distance'], 2); ?> light-years</p>
                                        <p>Est. travel time: <?php echo ceil($item['distance']/10); ?> days</p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <p>Price: <?php echo number_format($item['price']); ?> credits</p>
                                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                                        <h5>Total: <?php echo number_format($item['price'] * $item['quantity']); ?> credits</h5>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="card bg-dark mb-4">
                    <div class="card-header">
                        <h4>Customer Information</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="checkout-summary">
                    <h3>Order Summary</h3>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tickets (<?php echo $cartCount; ?>):</span>
                        <span><?php echo number_format($cartTotal); ?> credits</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (5%):</span>
                        <span><?php echo number_format($cartTotal * 0.05); ?> credits</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong><?php echo number_format($cartTotal * 1.05); ?> credits</strong>
                    </div>
                    <form method="post" action="checkout.php">
                        <button type="submit" name="confirm_order" class="btn btn-success btn-block btn-lg">
                            Confirm Order
                        </button>
                    </form>
                    <a href="cart.php" class="btn btn-outline-secondary btn-block mt-2">
                        Back to Cart
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="footer">
    <p>&copy; 2025 Travia Tour. All rights reserved. <a href="https://github.com/victorsts/travia" target="_blank" class="text-light" title="View on GitHub">View on GitHub</a></p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $('#toggleFont').on('click', function() {
        $('body').toggleClass('aurebesh');
    });

    $('head').append('<style>.aurebesh { font-family: "Aurebesh", sans-serif; }</style>');
</script>
</body>
</html> 