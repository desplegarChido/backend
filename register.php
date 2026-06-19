<?php
//backend/register.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "config/database.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$passwordRaw = $data["password"] ?? "";

if (
    empty($name) ||
    empty($email) ||
    empty($passwordRaw)
) {
    echo json_encode([
        "success" => false,
        "message" => "Todos los campos son obligatorios."
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Correo electrónico inválido."
    ]);
    exit();
}

/* verificar si el correo ya existe */
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$email]);

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode([
        "success" => false,
        "message" => "Este correo ya está registrado."
    ]);
    exit();
}

$password = password_hash(
    $passwordRaw,
    PASSWORD_DEFAULT
);

$sql = "
INSERT INTO users
(
    name,
    email,
    password
)
VALUES
(
    ?, ?, ?
)
";

$stmt = $conn->prepare($sql);

$success = $stmt->execute([
    $name,
    $email,
    $password
]);

echo json_encode([
    "success" => $success,
    "message" => $success
        ? "Usuario registrado correctamente."
        : "No se pudo registrar el usuario."
]);
?>