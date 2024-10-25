<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');

require_once('../class/ship.php');

// Fonction pour lire le fichier JSON et insérer les données dans la base de données
function importShips($pdo) {
    // Lire le fichier JSON
    $jsonData = file_get_contents('../data/ships.json');
    $ships = json_decode($jsonData, true);

    // Vérifier si les données ont été décodées correctement
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Erreur de décodage JSON : ' . json_last_error_msg());
    }

    // Préparer la requête d'insertion
    $query = $pdo->prepare("
        INSERT INTO ships (id, name, camp, speed_kmh, capacity)
        VALUES (:id, :name, :camp, :speed_kmh, :capacity)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            camp = VALUES(camp),
            speed_kmh = VALUES(speed_kmh),
            capacity = VALUES(capacity)
    ");

    // Tableau pour stocker les objets Ship
    $shipObjects = [];

    // Parcourir les données et les insérer dans la table
    foreach ($ships as $shipData) {
        $query->execute([
            ':id' => $shipData['id'],
            ':name' => $shipData['name'],
            ':camp' => $shipData['camp'],
            ':speed_kmh' => $shipData['speed_kmh'],
            ':capacity' => $shipData['capacity']
        ]);

        // Créer un objet Ship pour chaque vaisseau et l'ajouter au tableau
        $ship = new Ship(
            $shipData['id'],
            $shipData['name'],
            $shipData['camp'],
            $shipData['speed_kmh'],
            $shipData['capacity']
        );
        $shipObjects[] = $ship;
    }

    // Retourner le tableau d'objets Ship
    return $shipObjects;
}

// Importer les vaisseaux et obtenir les objets
$shipObjects = importShips($pdo);

// Afficher les détails de chaque vaisseau
echo "<h1>Vaisseaux importés :</h1>";
foreach ($shipObjects as $ship) {
    $ship->display();
}