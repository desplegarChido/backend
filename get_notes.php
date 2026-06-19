<?php
//backend/get_notes.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once "config/database.php";

$user_id = $_GET["user_id"] ?? null;

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "user_id es requerido."
    ]);
    exit();
}

$sql = "
SELECT *
FROM notes
WHERE user_id = ?
ORDER BY created_at DESC
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    $user_id
]);

$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "notes" => $notes
]);
?>