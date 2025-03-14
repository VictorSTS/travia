<?php
// Start session
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Initialize userName with default value
$userName = '';

// Only access session variables if user is logged in
if ($isLoggedIn) {
    $userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart totals
$cartTotal = 0;
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
    $cartCount += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travia Tour - Cart</title>
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
        .cart-item {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #1e1e1e;
            transition: all 0.3s ease;
        }
        .cart-item:hover {
            background-color: #2a2a2a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .cart-item img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .quantity-control button {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #333;
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
        }
        .quantity-control input {
            width: 50px;
            text-align: center;
            background-color: #333;
            color: white;
            border: 1px solid #444;
        }
        .cart-summary {
            background-color: #1e1e1e;
            border-radius: 8px;
            padding: 20px;
        }
        .empty-cart {
            text-align: center;
            padding: 50px 0;
        }
        .empty-cart i {
            font-size: 5em;
            color: #444;
            margin-bottom: 20px;
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
            <li class="nav-item active">
                <a class="nav-link" href="cart.php">
                    Cart
                    <?php if ($cartCount > 0): ?>
                    <span id="cartCount" class="badge badge-pill badge-primary ml-1"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php if ($isLoggedIn): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo htmlspecialchars($userName); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <button class="btn btn-link text-white" id="toggleFont">Translate to Aurebesh</button>
</nav>

<div class="container">
    <h1 class="mt-5 mb-4">Your Cart</h1>
    
    <?php if (!$isLoggedIn): ?>
        <div class="alert alert-warning">
            <strong>Warning!</strong> You are not logged in. <a href="login.php" class="alert-link">Log in</a> to save your cart and complete your order.
        </div>
    <?php endif; ?>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any tickets to your cart yet.</p>
            <a href="index.php" class="btn btn-primary mt-3">Find Tickets</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <h4>
                                    <i class="fas fa-rocket mr-2"></i>
                                    <?php echo htmlspecialchars($item['departure']); ?> 
                                    <i class="fas fa-arrow-right mx-2"></i> 
                                    <?php echo htmlspecialchars($item['arrival']); ?>
                                </h4>
                                <p>Distance: <?php echo number_format($item['distance'], 2); ?> light-years</p>
                                <p>Est. travel time: <?php echo ceil($item['distance']/10); ?> days</p>
                                <p>Price per ticket: <?php echo number_format($item['price']); ?> credits</p>
                            </div>
                            <div class="col-md-4">
                                <div class="text-right mb-3">
                                    <h5>Total: <?php echo number_format($item['price'] * $item['quantity']); ?> credits</h5>
                                </div>
                                <div class="quantity-control">
                                    <button class="decrease-quantity" data-index="<?php echo $index; ?>">-</button>
                                    <input type="number" min="1" max="10" value="<?php echo $item['quantity']; ?>" 
                                           class="quantity-input" data-index="<?php echo $index; ?>" readonly>
                                    <button class="increase-quantity" data-index="<?php echo $index; ?>">+</button>
                                </div>
                                <div class="text-right mt-3">
                                    <button class="btn btn-sm btn-danger remove-item" data-index="<?php echo $index; ?>">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-4">
                <div class="cart-summary">
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
                    <button id="checkoutBtn" class="btn btn-primary btn-block btn-lg">
                        <?php if ($isLoggedIn): ?>
                            Proceed to Checkout
                        <?php else: ?>
                            Login to Checkout
                        <?php endif; ?>
                    </button>
                    <button id="clearCartBtn" class="btn btn-outline-secondary btn-block mt-2">
                        Clear Cart
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="footer">
    <p>&copy; 2025 Travia Tour. All rights reserved.</p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        // Increase quantity
        $('.increase-quantity').on('click', function() {
            const index = $(this).data('index');
            updateQuantity(index, 1);
        });
        
        // Decrease quantity
        $('.decrease-quantity').on('click', function() {
            const index = $(this).data('index');
            updateQuantity(index, -1);
        });
        
        // Remove item
        $('.remove-item').on('click', function() {
            const index = $(this).data('index');
            removeItem(index);
        });
        
        // Clear cart
        $('#clearCartBtn').on('click', function() {
            if (confirm('Are you sure you want to clear your cart?')) {
                $.post('update_cart.php', { action: 'clear' }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error clearing cart: ' + response.error);
                    }
                }, 'json');
            }
        });
        
        // Checkout button
        $('#checkoutBtn').on('click', function() {
            <?php if ($isLoggedIn): ?>
                // Proceed to checkout
                window.location.href = 'checkout.php';
            <?php else: ?>
                // Redirect to login
                window.location.href = 'login.php?redirect=cart.php';
            <?php endif; ?>
        });
        
        // Update quantity function
        function updateQuantity(index, change) {
            $.post('update_cart.php', {
                action: 'update',
                index: index,
                change: change
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error updating cart: ' + response.error);
                }
            }, 'json');
        }
        
        // Remove item function
        function removeItem(index) {
            $.post('update_cart.php', {
                action: 'remove',
                index: index
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error removing item: ' + response.error);
                }
            }, 'json');
        }
    });
    
    $('#toggleFont').on('click', function() {
        $('body').toggleClass('aurebesh');
    });

    $('head').append('<style>.aurebesh { font-family: "Aurebesh", sans-serif; }</style>');
</script>
</body>
</html>
