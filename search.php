<?php
require_once("connection.php"); // Include your connection file

$newconnection = new Connection();
$connection = $newconnection->openConnection();

$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

if ($searchTerm) {
    $stmt = $connection->prepare("SELECT * FROM inquire_tb WHERE Name LIKE :search OR Address LIKE :search OR Email LIKE :search");
    $searchTermWithWildcards = '%' . $searchTerm . '%';
    $stmt->bindParam(':search', $searchTermWithWildcards, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    echo json_encode($results);
} else {
    echo json_encode([]);
}
