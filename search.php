<?php
// search.php

header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $departure = $_GET['departure'];
    $arrival = $_GET['arrival'];

    $sql = "SELECT * FROM planets WHERE name = :departure OR name = :arrival";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['departure' => $departure, 'arrival' => $arrival]);
    $planets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $departurePlanet = null;
    $arrivalPlanet = null;
    foreach ($planets as $planet) {
        if ($planet['name'] == $departure) {
            $departurePlanet = $planet;
        }
        if ($planet['name'] == $arrival) {
            $arrivalPlanet = $planet;
        }
    }

    if ($departurePlanet && $arrivalPlanet) {
        // Calculate coordinates
        $departureX = ($departurePlanet['x'] + $departurePlanet['sub_grid_x']) * 6;
        $departureY = ($departurePlanet['y'] + $departurePlanet['sub_grid_y']) * 6;
        $arrivalX = ($arrivalPlanet['x'] + $arrivalPlanet['sub_grid_x']) * 6;
        $arrivalY = ($arrivalPlanet['y'] + $arrivalPlanet['sub_grid_y']) * 6;
        
        // Calculate distance
        $distance = sqrt(pow($arrivalX - $departureX, 2) + pow($arrivalY - $departureY, 2));
        
        // Format planet data
        $result = [
            'success' => true,
            'departure' => [
                'name' => $departurePlanet['name'],
                'x' => $departureX,
                'y' => $departureY,
                'region' => $departurePlanet['region'],
                'sector' => $departurePlanet['sector'],
                'suns' => $departurePlanet['suns'],
                'moons' => $departurePlanet['moons'],
                'diameter' => $departurePlanet['diameter'],
                'gravity' => $departurePlanet['gravity'],
                'length_day' => $departurePlanet['length_day'],
                'length_year' => $departurePlanet['length_year']
            ],
            'arrival' => [
                'name' => $arrivalPlanet['name'],
                'x' => $arrivalX,
                'y' => $arrivalY,
                'region' => $arrivalPlanet['region'],
                'sector' => $arrivalPlanet['sector'],
                'suns' => $arrivalPlanet['suns'],
                'moons' => $arrivalPlanet['moons'],
                'diameter' => $arrivalPlanet['diameter'],
                'gravity' => $arrivalPlanet['gravity'],
                'length_day' => $arrivalPlanet['length_day'],
                'length_year' => $arrivalPlanet['length_year']
            ],
            'distance' => $distance,
            'travel_time' => ceil($distance / 10) // Estimate travel time in days
        ];
    } else {
        $result = ['success' => false];
    }

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
