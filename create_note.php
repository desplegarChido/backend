<?php
//backend/create_note.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

require_once "config/database.php";

$user_id = intval($_POST["user_id"] ?? 0);

$title = trim(
    $_POST["title"] ?? ""
);

$content = trim(
    $_POST["content"] ?? ""
);

$note_color =
    $_POST["note_color"] ?? "#ffffff";

$font_family =
    $_POST["font_family"] ?? "Inter";

if (
    !$user_id ||
    empty($title) ||
    empty($content)
) {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos obligatorios."
    ]);
    exit();
}

/* verificar usuario */
$sql = "
SELECT is_premium
FROM users
WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Usuario no encontrado."
    ]);
    exit();
}

$isPremium = $user["is_premium"] ?? 0;

/* limitar personalización para usuarios gratuitos */
if ($isPremium == 0) {
    $note_color = "#ffffff";
    $font_family = "Inter";
}

$sql = "
INSERT INTO notes
(
    user_id,
    title,
    content,
    note_color,
    font_family
)
VALUES
(
    ?, ?, ?, ?, ?
)
";

$stmt = $conn->prepare($sql);

$success = $stmt->execute([
    $user_id,
    $title,
    $content,
    $note_color,
    $font_family
]);

echo json_encode([
    "success" => $success,
    "message" => $success
        ? "Nota creada correctamente."
        : "No se pudo crear la nota."
]);
?>