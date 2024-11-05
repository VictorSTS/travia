<?php

require_once '../class/trip.php';

// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');

// Load JSON data
$jsonData = file_get_contents('../data/planets_details.json');
$planets = json_decode($jsonData, true);

try {
    $pdo->beginTransaction();

    // Clear existing trips data
    $pdo->exec("DELETE FROM trips");

    foreach ($planets as $planet) {
        if (isset($planet['trips'])) {
            foreach ($planet['trips'] as $day => $trips) {
                foreach ($trips as $trip) {
                    foreach ($trip['destination_planet_id'] as $index => $destId) {
                        $departureTime = $trip['departure_time'][$index];
                        $shipId = $trip['ship_id'][$index];

                        // Create Trip object and save it
                        $tripObj = new Trip($day, $destId, $departureTime, $shipId);
                        $tripObj->save($pdo);
                    }
                }
            }
        }
    }

    $pdo->commit();
    echo "Trips imported successfully!";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed to import trips: " . $e->getMessage();
}