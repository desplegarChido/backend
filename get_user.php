<?php
//backend/get_user.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once "config/database.php";

$userId = $_GET["id"] ?? null;

if (!$userId) {
    echo json_encode([
        "success" => false,
        "message" => "ID de usuario requerido."
    ]);
    exit();
}

$sql = "
SELECT
    id,
    name,
    email,
    points,
    is_premium,
    font_family,
    premium_expires_at
FROM users
WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Usuario no encontrado."
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
            $userId
        ]);

        $user["is_premium"] = 0;
    }
}

echo json_encode([
    "success" => true,
    "user" => $user
]);
?>