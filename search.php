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
        $result = [
            'success' => true,
            'departure' => [
                'name' => $departurePlanet['name'],
                'x' => ($departurePlanet['x'] + $departurePlanet['sub_grid_x']) * 6,
                'y' => ($departurePlanet['y'] + $departurePlanet['sub_grid_y']) * 6
            ],
            'arrival' => [
                'name' => $arrivalPlanet['name'],
                'x' => ($arrivalPlanet['x'] + $arrivalPlanet['sub_grid_x']) * 6,
                'y' => ($arrivalPlanet['y'] + $arrivalPlanet['sub_grid_y']) * 6
            ]
        ];
    } else {
        $result = ['success' => false];
    }

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
