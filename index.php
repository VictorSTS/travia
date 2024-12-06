<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travia Tour</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <!-- Compte button removed -->
        </ul>
    </div>
    <button class="btn btn-link text-white" id="toggleFont">Traduire en Aurebesh</button>
</nav>

<div class="container">
    <h1 class="mt-5">Bienvenue sur Travia Tour</h1>
    <p>Recherchez et réservez vos billets de vaisseaux de transport commerciaux intergalactiques.</p>

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

    <div id="map"></div>
</div>

<div class="footer">
    <p>&copy; 2024 Travia Tour. Tous droits réservés.</p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    var map = L.map('map', {
        crs: L.CRS.Simple
    });
    var bounds = [[0,0], [1300,1300]];
    map.fitBounds(bounds);

    $.get('getPlanets.php', function(data) {
        data.forEach(function(planet) {
            if (planet.diameter && planet.diameter == 0){
                var radius = 1;
            } else {
                var radius = planet.diameter;
            }
            L.circle([planet.y*10, planet.x*10], {
                color: 'white',
                fillColor: 'white',
                fillOpacity: 0.5,
                radius: planet.diameter / 200_000
            }).addTo(map).bindPopup(planet.name);
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

    $('head').append('<style>.aurebesh { font-family: "Aurebesh", sans-serif; }</style>');
</script>
<
</body>
</html>
