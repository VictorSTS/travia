<?php

header('Content-Type: application/json');

try {
    // connexion à la bdd
    $pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // récupération des données POST
    $type = $_POST['type'] ?? null;
    $data = $_POST['data'] ?? null;

    // vérification des données
    if (!$type || !$data) {
        throw new Exception('Missing type or data');
    }

    // Préparation de la requête SQL
    $sql = "INSERT INTO logs (type, data) VALUES (:type, :data)";
    $stmt = $pdo->prepare($sql);

    // exécution de la requête
    $stmt->execute(['type' => $type, 'data' => $data]);

    // réponse JSON
    echo json_encode(['success' => true, 'message' => 'Log added successfully.']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>