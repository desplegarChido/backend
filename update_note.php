<?php
//backend/update_note.php

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

$id = $data["id"] ?? 0;
$user_id = $data["user_id"] ?? 0;

$title = $data["title"] ?? "";
$content = $data["content"] ?? "";

$note_color =
    $data["note_color"] ?? "#ffffff";

$font_family =
    $data["font_family"] ?? "Inter";

if (!$id || !$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos."
    ]);
    exit();
}

/* verificar si es premium */
$sql = "SELECT is_premium
        FROM users
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$isPremium = $user["is_premium"] ?? 0;

/* limitar personalización para usuarios no premium */
if ($isPremium == 0) {
    $note_color = "#ffffff";
    $font_family = "Inter";
}

/* actualizar únicamente la nota del usuario */
$sql = "
UPDATE notes
SET
    title = ?,
    content = ?,
    note_color = ?,
    font_family = ?
WHERE id = ?
AND user_id = ?
";

$stmt = $conn->prepare($sql);

$success = $stmt->execute([
    $title,
    $content,
    $note_color,
    $font_family,
    $id,
    $user_id
]);

echo json_encode([
    "success" => $success
]);