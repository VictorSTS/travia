<?php
// autocomplete.php

header('Content-Type: application/json');


$pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = $_GET['query'];
$sql = "SELECT name FROM planets WHERE name LIKE :query LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute(['query' => $query . '%']);
$results = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(['suggestions' => $results]);

