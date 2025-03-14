<?php
// Start session
session_start();

// Check if registration email exists in session
if (!isset($_SESSION['registration_email'])) {
    header('Location: register.php');
    exit;
}

$email = $_SESSION['registration_email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Travia Tour</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
        .card {
            background-color: #1e1e1e;
            border: 1px solid #333333;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="index.php">Travia Tour</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
            </ul>
        </div>
        <button class="btn btn-link text-white" id="toggleFont">Translate to Aurebesh</button>
    </nav>

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="card-title">Registration Successful!</h2>
                        <div class="my-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <p class="card-text">A verification email has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>.</p>
                        <p class="card-text">Please click on the verification link in the email to activate your account.</p>
                        <p class="card-text"><small class="text-muted">The verification link will expire in 1 minute.</small></p>
                        <div class="mt-4">
                            <a href="login.php" class="btn btn-primary">Go to login page</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Travia Tour. All rights reserved. <a href="https://github.com/victorsts/travia" target="_blank" class="text-light" title="View on GitHub">View on GitHub</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
        $('#toggleFont').on('click', function() {
            $('body').toggleClass('aurebesh');
        });
        
        $('head').append('<style>.aurebesh { font-family: "Aurebesh", sans-serif; }</style>');
    </script>
</body>
</html> 