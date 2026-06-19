<?php
//backend/complete_note.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

require_once "config/database.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$noteId = $data["note_id"] ?? 0;
$userId = $data["user_id"] ?? 0;

if (!$noteId || !$userId) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos."
    ]);
    exit();
}

/* verificar que la nota pertenezca al usuario */
$sql = "
SELECT id
FROM notes
WHERE id = ?
AND user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $noteId,
    $userId
]);

$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    echo json_encode([
        "success" => false,
        "message" => "La nota no existe o no pertenece al usuario."
    ]);
    exit();
}

/* marcar nota completada */
$sql = "
UPDATE notes
SET completed = 1
WHERE id = ?
AND user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $noteId,
    $userId
]);

/* revisar premium */
$sql = "
SELECT is_premium
FROM users
WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $userId
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$points = (
    ($user["is_premium"] ?? 0) == 1
)
    ? 20
    : 10;

/* sumar puntos */
$sql = "
UPDATE users
SET points = points + ?
WHERE id = ?
";

$stmt = $conn->prepare($sql);

$success = $stmt->execute([
    $points,
    $userId
]);

/* obtener puntos actualizados */
$updatedPoints = null;

if ($success) {

    $sql = "
    SELECT points
    FROM users
    WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        $userId
    ]);

    $userPoints = $stmt->fetch(PDO::FETCH_ASSOC);

    $updatedPoints =
        $userPoints["points"] ?? 0;
}

echo json_encode([
    "success" => $success,
    "points_added" => $points,
    "points" => $updatedPoints
]);
?>