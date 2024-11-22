<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travia Tour</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            padding-top: 56px;
        }
        #map {
            height: 500px;
            width: 100%;
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .autocomplete-dropdown {
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
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
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <a class="navbar-brand" href="#">Travia Tour</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="#">Compte</a>
            </li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<div class="container">
    <h1 class="mt-5">Bienvenue sur Travia Tour</h1>
    <p>Recherchez et réservez vos billets de vaisseaux de transport commerciaux intergallactiques.</p>

    <!-- Search Form -->
    <form id="searchForm" class="mt-5">
        <div class="form-row">
            <div class="form-group col-md-6 position-relative">
                <label for="departure">Planète de départ</label>
                <input type="text" class="form-control" id="departure" name="departure" placeholder="Coruscant" required>
                <ul class="autocomplete-dropdown" id="departureDropdown"></ul>
            </div>
            <div class="form-group col-md-6 position-relative">
                <label for="arrival">Planète d'arrivée</label>
                <input type="text" class="form-control" id="arrival" name="arrival" placeholder="Tatooine" required>
                <ul class="autocomplete-dropdown" id="arrivalDropdown"></ul>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    <!-- Map -->
    <div id="map"></div>
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; 2024 Travia Tour. Tous droits réservés.</p>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Initialize the map
    var map = L.map('map', {
        crs: L.CRS.Simple
    });
    var bounds = [[0,0], [1000,1000]];
    var image = L.imageOverlay('uqm_map_full.png', bounds).addTo(map);
    map.fitBounds(bounds);

    // Fetch all planets and add them to the map
    $.get('getPlanets.php', function(data) {
        console.log(data);
        if (data.planets && data.planets.length > 0) {
            var planets = data.planets;
            planets.forEach(function(planet) {
                L.circle([planet.y, planet.x], {
                    color: 'blue',
                    fillColor: '#f03',
                    fillOpacity: 0.5,
                    radius: planet.diameter / 1000
                }).addTo(map).bindPopup(planet.name);
            });
        }
    });

    // Autocomplete for planets
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

    // Handle dropdown item click
    $('body').on('click', '.autocomplete-dropdown li', function() {
        var input = $(this).parent().prev('input');
        input.val($(this).text());
        $(this).parent().hide();
    });

    // Handle form submission
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
                // Clear the map
                map.eachLayer(function (layer) {
                    if (layer instanceof L.Marker || layer instanceof L.Polyline) {
                        map.removeLayer(layer);
                    }
                });

                // Add markers for departure and arrival
                L.marker([data.departure.y, data.departure.x]).addTo(map)
                    .bindPopup('Departure: ' + data.departure.name)
                    .openPopup();
                L.marker([data.arrival.y, data.arrival.x]).addTo(map)
                    .bindPopup('Arrival: ' + data.arrival.name)
                    .openPopup();

                // Add a line between departure and arrival
                L.polyline([
                    [data.departure.y, data.departure.x],
                    [data.arrival.y, data.arrival.x]
                ], { color: 'red' }).addTo(map);

                // Center the map on the departure and arrival
                map.fitBounds([
                    [data.departure.y, data.departure.x],
                    [data.arrival.y, data.arrival.x]
                ]);
            } else {
                alert('No route found.');
            }
        });
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group').length) {
            $('.autocomplete-dropdown').hide();
        }
    });
</script>
</body>
</html>
