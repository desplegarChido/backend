<?php
//backend/login.php

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

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (empty($email) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Correo y contraseña son obligatorios."
    ]);
    exit();
}

$sql = "SELECT * FROM users WHERE email = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Credenciales incorrectas"
    ]);
    exit();
}

if (!password_verify($password, $user["password"])) {
    echo json_encode([
        "success" => false,
        "message" => "Credenciales incorrectas"
    ]);
    exit();
}

/* verificar expiración premium */
if (
    $user["is_premium"] == 1 &&
    !empty($user["premium_expires_at"])
) {
    $expiresAt = strtotime(
        $user["premium_expires_at"]
    );

    if (
        $expiresAt !== false &&
        $expiresAt < time()
    ) {
        $expireStmt = $conn->prepare(
            "UPDATE users
             SET is_premium = 0
             WHERE id = ?"
        );

        $expireStmt->execute([
            $user["id"]
        ]);

        $user["is_premium"] = 0;
    }
}

echo json_encode([
    "success" => true,
    "user" => [
        "id" => $user["id"],
        "name" => $user["name"],
        "email" => $user["email"],
        "points" => $user["points"],
        "is_premium" => $user["is_premium"],
        "font_family" => $user["font_family"],
        "premium_expires_at" => $user["premium_expires_at"]
    ]
]);
?>