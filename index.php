<?php
// Start session
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Initialize variables with default values
$userName = '';
$homePlanet = '';
$workPlanet = '';

// Only access session variables if user is logged in
if ($isLoggedIn) {
    $userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
    $homePlanet = isset($_SESSION['home_planet']) ? $_SESSION['home_planet'] : '';
    $workPlanet = isset($_SESSION['work_planet']) ? $_SESSION['work_planet'] : '';
} else {
    header('Location: login.php');
    exit();
}

// Calculate cart count
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travia Tour</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
            #map {
                height: 500px;
                width: 100%;
                margin-top: 20px;
                background-color: #000000;
            }
            .footer {
                margin-top: 50px;
                text-align: center;
            }
            .autocomplete-dropdown {
                position: absolute;
                background-color: #333333;
                border: 1px solid #555555;
                z-index: 1000;
                max-height: 150px;
                overflow-y: auto;
                width: 100%;
            }
            .autocomplete-dropdown li {
                list-style: none;
                padding: 8px;
                cursor: pointer;
            }
            .autocomplete-dropdown li:hover {
                background-color: #444444;
            }
            .navbar {
                background-color: #1e1e1e;
            }
            .navbar-brand, .nav-link {
                color: #ffffff;
            }
            .form-control {
                background-color: #333333;
                color: #ffffff;
                border: 1px solid #555555;
            }
            .btn-primary {
                background-color: #007bff;
                border-color: #007bff;
            }
            .btn-primary:hover {
                background-color: #0056b3;
                border-color: #0056b3;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">Travia Tour</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon">
        </span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
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
    <h1 class="mt-5">Welcome to Travia Tour</h1>
    <p>Search and book tickets for intergalactic commercial transport ships.</p>
    
    <?php if ($isLoggedIn): ?>
        <div class="alert alert-info">
            Hello <?php echo htmlspecialchars($userName); ?>! You can use your default planets to make your search easier.
        </div>
    <?php endif; ?>

    <form id="searchForm" class="mt-5">
        <div class="form-row">
            <div class="form-group col-md-6 position-relative">
                <label for="departure">Departure planet</label>
                <input type="text" class="form-control" id="departure" name="departure" placeholder="Coruscant" value="<?php echo htmlspecialchars($homePlanet); ?>" required>
                <ul class="autocomplete-dropdown" id="departureDropdown"></ul>
                <?php if ($isLoggedIn): ?>
                    <small class="form-text text-muted">Your home planet is pre-filled.</small>
                <?php endif; ?>
            </div>
            <div class="form-group col-md-6 position-relative">
                <label for="arrival">Arrival planet</label>
                <input type="text" class="form-control" id="arrival" name="arrival" placeholder="Tatooine" value="<?php echo htmlspecialchars($workPlanet); ?>" required>
                <ul class="autocomplete-dropdown" id="arrivalDropdown"></ul>
                <?php if ($isLoggedIn): ?>
                    <small class="form-text text-muted">Your work planet is pre-filled.</small>
                <?php endif; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($isLoggedIn): ?>
            <button type="button" id="swapPlanets" class="btn btn-secondary">Swap planets</button>
        <?php endif; ?>
    </form>

    <div id="map"></div>
    <div id="planetsContainer" class="row mt-4"></div>

<div class="footer">
    <p>&copy; 2025 Travia Tour. All rights reserved. <a href="https://github.com/victorsts/travia" target="_blank" class="text-light" title="View on GitHub">View on GitHub</a></p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map', {
        crs: L.CRS.Simple
    });
    var bounds = [[0,0], [1300,1300]];
    map.fitBounds(bounds);

    var regionColors = {
        "Colonies": "#FF0000",
        "Core": "#00FF00",
        "Deep Core": "#0000FF",
        "Expansion Region": "#FFFF00",
        "Extragalactic": "#FF00FF",
        "Hutt Space": "#00FFFF",
        "Inner Rim Territories": "#800000",
        "Mid Rim Territories": "#808000",
        "Outer Rim Territories": "#008080",
        "Talcene Sector": "#800080",
        "The Centrality": "#C0C0C0",
        "Tingel Arm": "#FF8000",
        "Wild Space": "#8000FF"
    };

    $.get('getPlanets.php', function(data) {
        data.forEach(function(planet) {
            if (planet.diameter && planet.diameter == 0){
                var radius = 1;
            } else {
                var radius = planet.diameter;
            }
            var color = regionColors[planet.region] || '#FFFFFF';
            L.circle([planet.y*10, planet.x*10], {
                color: color,
                fillColor: color,
                fillOpacity: 0.5,
                radius: planet.diameter / 100_000
            }).addTo(map).bindPopup(planet.name + " | Region: " + planet.region).on('click', function() {
                $('#departure').val(planet.name);
                $('#arrival').val('');
            });
        });

    });

    function showAutocomplete(input, dropdown, query) {
        if (query.length >= 2) {
            $.get('autocomplete.php', { query: query }, function(data) {
                if (data.error) {
                    alert('Error fetching autocomplete suggestions: ' + data.error);
                    return;
                }
                if (data.suggestions && data.suggestions.length > 0) {
                    var suggestions = data.suggestions;
                    dropdown.empty();
                    suggestions.forEach(function(planet) {
                        dropdown.append('<li>' + planet + '</li>');
                    });
                    dropdown.show();
                } else {
                    dropdown.hide();
                }
            });
        } else {
            dropdown.hide();
        }
    }

    $('#departure').on('input', function() {
        var query = $(this).val();
        showAutocomplete($(this), $('#departureDropdown'), query);
    });

    $('#arrival').on('input', function() {
        var query = $(this).val();
        showAutocomplete($(this), $('#arrivalDropdown'), query);
    });

    $('body').on('click', '.autocomplete-dropdown li', function() {
        var input = $(this).parent().prev('input');
        input.val($(this).text());
        $(this).parent().hide();
    });

    $('#searchForm').on('submit', function(event) {
        event.preventDefault();
        var departure = $('#departure').val();
        var arrival = $('#arrival').val();

        $.get('search.php', { departure: departure, arrival: arrival }, function(data) {
            if (data.error) {
                alert('Error performing search: ' + data.error);
                return;
            }
            if (data.success) {
                console.log(data);
                map.eachLayer(function (layer) {
                    if (layer instanceof L.Marker || layer instanceof L.Polyline) {
                        map.removeLayer(layer);
                    }
                });

                L.marker([data.departure.y*10, data.departure.x*10]).addTo(map)
                    .bindPopup('Departure: ' + data.departure.name)
                    .openPopup();
                L.marker([data.arrival.y*10, data.arrival.x*10]).addTo(map)
                    .bindPopup('Arrival: ' + data.arrival.name)
                    .openPopup();

                L.polyline([
                    [data.departure.y*10, data.departure.x*10],
                    [data.arrival.y*10, data.arrival.x*10]
                ], { color: 'red' }).addTo(map);

                map.fitBounds([
                    [data.departure.y*10, data.departure.x*10],
                    [data.arrival.y*10, data.arrival.x*10]
                ]);
                function calculateDistance(x1, y1, x2, y2) {
                    return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2)).toFixed(2);
                }

                var distance = calculateDistance(data.departure.x, data.departure.y, data.arrival.x, data.arrival.y);
                var price = Math.round(distance * 10); // Calculate price based on distance

                $('#planetsContainer').empty();

                // Create a better travel details container
                $('#planetsContainer').append(`
                    <div class="col-12 mb-4">
                        <div class="card bg-dark text-white">
                            <div class="card-header">
                                <h4>Travel Details</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-5 text-center">
                                        <h5>Departure</h5>
                                        <h3>${data.departure.name}</h3>
                                        <p>Coordinates: (${data.departure.x}, ${data.departure.y})</p>
                                        <p>Region: ${data.departure.region || 'Unknown'}</p>
                                    </div>
                                    <div class="col-md-2 text-center d-flex align-items-center justify-content-center">
                                        <div>
                                            <i class="fa fa-arrow-right" style="font-size: 2em;"></i>
                                            <p class="mt-2">Distance: ${distance} light-years</p>
                                            <p>Est. travel time: ${Math.ceil(distance/10)} days</p>
                                        </div>
                                    </div>
                                    <div class="col-md-5 text-center">
                                        <h5>Arrival</h5>
                                        <h3>${data.arrival.name}</h3>
                                        <p>Coordinates: (${data.arrival.x}, ${data.arrival.y})</p>
                                        <p>Region: ${data.arrival.region || 'Unknown'}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h5>Price: ${price} credits per ticket</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ticketQuantity">Number of tickets:</label>
                                            <input type="number" class="form-control" id="ticketQuantity" min="1" max="10" value="1">
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button class="btn btn-primary btn-lg" id="addToCartBtn">
                                            Add to cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                // Add to cart button event handler
                $('#addToCartBtn').on('click', function() {
                    const quantity = parseInt($('#ticketQuantity').val()) || 1;
                    
                    // Add ticket to cart via AJAX
                    $.post('add_to_cart.php', {
                        departure: data.departure.name,
                        arrival: data.arrival.name,
                        distance: distance,
                        price: price,
                        quantity: quantity
                    }, function(response) {
                        if (response.success) {
                            // Show success message
                            $('<div class="alert alert-success mt-3">')
                                .text(`${quantity} ticket(s) added to your cart!`)
                                .appendTo('#planetsContainer')
                                .delay(3000)
                                .fadeOut();
                                
                            // Update cart count in navbar if it exists
                            if ($('#cartCount').length) {
                                $('#cartCount').text(response.cartCount);
                            } else {
                                // Add cart count badge if it doesn't exist
                                $('.nav-link[href="cart.php"]').append(
                                    `<span id="cartCount" class="badge badge-pill badge-primary ml-1">${response.cartCount}</span>`
                                );
                            }
                        } else {
                            alert('Error adding to cart: ' + response.error);
                        }
                    }, 'json');
                });
            } else {
                alert('No route found.');
            }
        });
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group').length) {
            $('.autocomplete-dropdown').hide();
        }
    });

    $('#toggleFont').on('click', function() {
        $('body').toggleClass('aurebesh');
    });

    // Swap planets button
    $('#swapPlanets').on('click', function() {
        var departure = $('#departure').val();
        var arrival = $('#arrival').val();
        
        $('#departure').val(arrival);
        $('#arrival').val(departure);
    });

    $('head').append('<style>.aurebesh { font-family: "Aurebesh", sans-serif; }</style>');
</script>
</body>
</html>