<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travia Tour - Panier</title>
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
        .cart-item {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            background-color: #1e1e1e;
        }
        .cart-item img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
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
                <a class="nav-link" href="index.php">Retour</a>
            </li>
        </ul>
    </div>
    <button class="btn btn-link text-white" id="toggleFont">Traduire en Aurebesh</button>
</nav>

<div class="container">
    <h1 class="mt-5">Votre Panier</h1>
    <div id="cartContainer"></div>
    <button class="btn btn-primary" onclick="order()">Commander</button>
</div>

<div class="footer">
    <p>&copy; 2024 Travia Tour. Tous droits réservés.</p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    function loadCart() {
        var departure = new URLSearchParams(window.location.search).get('departure');
        var arrival = new URLSearchParams(window.location.search).get('arrival');

        if (departure && arrival) {
            $.get('search.php', { departure: departure, arrival: arrival }, function(data) {
                if (data.error) {
                    alert('Error loading cart: ' + data.error);
                    return;
                }
                if (data.success) {
                    var cartContainer = $('#cartContainer');
                    cartContainer.empty();

                    var departureImageUrl = 'https://static.wikia.nocookie.net/starwars/images/placeholder.png';
                    var arrivalImageUrl = 'https://static.wikia.nocookie.net/starwars/images/placeholder.png';
                    cartContainer.append(`
                        <div class="cart-item row">
                            <div class="col-md-6 text-center">
                                <h5>Planète de départ</h5>
                                <img src="${departureImageUrl}" alt="${data.departure.name}" class="img-thumbnail mb-2">
                                <p><strong>${data.departure.name}</strong></p>
                                <p>Coordonnées : (${data.departure.x}, ${data.departure.y})</p>
                            </div>
                            <div class="col-md-6 text-center">
                                <h5>Planète d'arrivée</h5>
                                <img src="${arrivalImageUrl}" alt="${data.arrival.name}" class="img-thumbnail mb-2">
                                <p><strong>${data.arrival.name}</strong></p>
                                <p>Coordonnées : (${data.arrival.x}, ${data.arrival.y})</p>
                            </div>
                        </div>
                    `);

                    window.cartData = data;
                } else {
                    $('#cartContainer').html('<p>Votre panier est vide.</p>');
                }
            }, 'json');
        } else {
            $('#cartContainer').html('<p>Votre panier est vide.</p>');
        }
    }

    function order() {
        if (window.cartData) {
            var data = `DEPARTURE=${window.cartData.departure.name}; ARRIVAL=${window.cartData.arrival.name};`;
            $.post('log.php', { type: 'order', data: data }, function(response) {
                if (response.success) {
                    alert('Commande réussie !');
                    window.location.href = 'index.php';
                } else {
                    alert('Erreur lors de la commande : ' + response.error);
                }
            }, 'json');
        } else {
            alert('Votre panier est vide.');
        }
    }

    $('#toggleFont').on('click', function() {
        $('body').toggleClass('aurebesh');
    });

    $('head').append('<style>.aurebesh { font-family: "Aurebesh", sans-serif; }</style>');

    $(document).ready(function() {
        loadCart();
    });
</script>
</body>
</html>
