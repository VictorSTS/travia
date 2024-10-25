<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');

// Inclure la classe Planet
require_once('../class/planet.php');

// Fonction pour importer les données des planètes
function importPlanets($pdo)
{
    // Lire le fichier JSON
    $jsonData = file_get_contents('../data/planets_details.json');
    $planets = json_decode($jsonData, true);

    // Vérifier si les données ont été décodées correctement
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Erreur de décodage JSON : ' . json_last_error_msg());
    }

    // Supprimer les anciennes données de la table planets
    $pdo->exec('DELETE FROM planets');

    // Préparer la requête d'insertion
    $query = $pdo->prepare('
        INSERT INTO planets (id, name, image, coord, x, y, sub_grid_coord, sub_grid_x, sub_grid_y, region, sector, suns, moons, position, distance, length_day, length_year, diameter, gravity)
        VALUES (:id, :name, :image, :coord, :x, :y, :sub_grid_coord, :sub_grid_x, :sub_grid_y, :region, :sector, :suns, :moons, :position, :distance, :length_day, :length_year, :diameter, :gravity)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            image = VALUES(image),
            coord = VALUES(coord),
            x = VALUES(x),
            y = VALUES(y),
            sub_grid_coord = VALUES(sub_grid_coord),
            sub_grid_x = VALUES(sub_grid_x),
            sub_grid_y = VALUES(sub_grid_y),
            region = VALUES(region),
            sector = VALUES(sector),
            suns = VALUES(suns),
            moons = VALUES(moons),
            position = VALUES(position),
            distance = VALUES(distance),
            length_day = VALUES(length_day),
            length_year = VALUES(length_year),
            diameter = VALUES(diameter),
            gravity = VALUES(gravity)
    ');

    // Tableau pour stocker les objets Planet
    $planetObjects = [];

    // Parcourir les données et les insérer dans la table
    foreach ($planets as $planetData) {
        $query->execute([
            ':id' => $planetData['Id'],
            ':name' => $planetData['Name'],
            ':image' => $planetData['Image'] ?? null,
            ':coord' => $planetData['Coord'] ?? "Unknown",
            ':x' => $planetData['X'],
            ':y' => $planetData['Y'],
            ':sub_grid_coord' => $planetData['SubGridCoord'] ?? 'N/A',
            ':sub_grid_x' => $planetData['SubGridX'],
            ':sub_grid_y' => $planetData['SubGridY'],
            ':region' => $planetData['Region'],
            ':sector' => $planetData['Sector'],
            ':suns' => $planetData['Suns'],
            ':moons' => $planetData['Moons'],
            ':position' => $planetData['Position'],
            ':distance' => $planetData['Distance'],
            ':length_day' => $planetData['LengthDay'],
            ':length_year' => $planetData['LengthYear'],
            ':diameter' => $planetData['Diameter'],
            ':gravity' => $planetData['Gravity']
        ]);

        // Créer un objet Planet pour chaque planète et l'ajouter au tableau
        $planet = new Planet(
            $planetData['Id'],
            $planetData['Name'],
            $planetData['Image'] ?? null,
            $planetData['Coord'] ?? null,
            $planetData['X'],
            $planetData['Y'],
            $planetData['SubGridCoord'] ?? 'N/A',
            $planetData['SubGridX'],
            $planetData['SubGridY'],
            $planetData['Region'],
            $planetData['Sector'],
            $planetData['Suns'],
            $planetData['Moons'],
            $planetData['Position'],
            $planetData['Distance'],
            $planetData['LengthDay'],
            $planetData['LengthYear'],
            $planetData['Diameter'],
            $planetData['Gravity']
        );
        $planetObjects[] = $planet;
    }

    // Retourner le tableau d'objets Planet
    return $planetObjects;
}

// Importer les planètes et obtenir les objets
$planetObjects = importPlanets($pdo);

// Afficher les détails de chaque planète
$i = 0;
echo "<h1>Planètes importées :</h1>";
foreach ($planetObjects as $planet) {
    $i++;
    if($i > 20){
        break; // On affiche seulement 100 planètes
    }
    $planet->display();
}

