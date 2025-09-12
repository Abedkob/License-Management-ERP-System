<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require __DIR__ . '/db.php';

// Check if project name is provided
if (!isset($_GET['name'])) {
    echo json_encode(["error" => "Missing project name"]);
    exit;
}

$projectName = trim($_GET['name']);

$sql = "SELECT license, valid_from, valid_to 
        FROM projects_list 
        WHERE name = :name";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':name', $projectName, PDO::PARAM_STR);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo json_encode(["error" => "Project not found"]);
}
