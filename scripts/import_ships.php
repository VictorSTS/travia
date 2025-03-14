<?php
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');

require_once('../class/ship.php');

// Function to read the JSON file and insert data into the database
function importShips($pdo) {
    // Read the JSON file
    $jsonData = file_get_contents('../data/ships.json');
    $ships = json_decode($jsonData, true);

    // Check if data was decoded correctly
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('JSON decoding error: ' . json_last_error_msg());
    }

    // Prepare the insert query
    $query = $pdo->prepare("
        INSERT INTO ships (id, name, camp, speed_kmh, capacity)
        VALUES (:id, :name, :camp, :speed_kmh, :capacity)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            camp = VALUES(camp),
            speed_kmh = VALUES(speed_kmh),
            capacity = VALUES(capacity)
    ");

    // Array to store Ship objects
    $shipObjects = [];

    // Loop through the data and insert into the table
    foreach ($ships as $shipData) {
        $query->execute([
            ':id' => $shipData['id'],
            ':name' => $shipData['name'],
            ':camp' => $shipData['camp'],
            ':speed_kmh' => $shipData['speed_kmh'],
            ':capacity' => $shipData['capacity']
        ]);

        // Create a Ship object for each ship and add it to the array
        $ship = new Ship(
            $shipData['id'],
            $shipData['name'],
            $shipData['camp'],
            $shipData['speed_kmh'],
            $shipData['capacity']
        );
        $shipObjects[] = $ship;
    }

    // Return the array of Ship objects
    return $shipObjects;
}

// Import ships and get objects
$shipObjects = importShips($pdo);

// Display details of each ship
echo "<h1>Imported Ships:</h1>";
foreach ($shipObjects as $ship) {
    $ship->display();
}