<?php
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');

// Include the Planet class
require_once('../class/planet.php');

// Function to import planet data
function importPlanets($pdo)
{
    // Read the JSON file
    $jsonData = file_get_contents('../data/planets_details.json');
    $planets = json_decode($jsonData, true);

    // Check if data was decoded correctly
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('JSON decoding error: ' . json_last_error_msg());
    }

    // Delete old data from the planets table
    $pdo->exec('DELETE FROM planets');

    // Prepare the insert query
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

    // Array to store Planet objects
    $planetObjects = [];

    // Loop through the data and insert into the table
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

        // Create a Planet object for each planet and add it to the array
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

    // Return the array of Planet objects
    return $planetObjects;
}

// Import planets and get objects
$planetObjects = importPlanets($pdo);

// Display details of each planet
$i = 0;
echo "<h1>Imported Planets:</h1>";
foreach ($planetObjects as $planet) {
/*    $i++;
    if($i > 20){
        break; // Only display 20 planets
    }*/
    $planet->display();
}

