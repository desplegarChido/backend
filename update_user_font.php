<?php
//backend/update_user_font.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

require_once "config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data["user_id"] ?? null;
$font_family = trim($data["font_family"] ?? "Arial");

if (!$user_id || !$font_family) {
    echo json_encode([
        "success" => false,
        "message" => "Datos inválidos para actualizar la tipografía."
    ]);
    exit();
}

$sql = "UPDATE users SET font_family = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$success = $stmt->execute([$font_family, $user_id]);

if (!$success) {
    echo json_encode([
        "success" => false,
        "message" => "No se pudo actualizar la tipografía."
    ]);
    exit();
}

echo json_encode([
    "success" => true,
    "message" => "Tipografía actualizada correctamente."
]);
