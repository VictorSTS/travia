<?php
// getPlanets.php

header('Content-Type: application/json');


$pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT name as name, (x + sub_grid_x) * 6 as x, (y + sub_grid_y) * 6 as y, diameter as diameter, region as region, image as image FROM planets;";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$planets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($planets);

