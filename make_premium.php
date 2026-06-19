<?php
//backend/make_premium.php

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

$id = $data["id"] ?? null;
$payment_method = trim(
    $data["payment_method"] ?? ""
);

$phone = trim(
    $data["phone"] ?? ""
);

if (!$id) {
    echo json_encode([
        "success" => false,
        "message" => "ID de usuario requerido."
    ]);
    exit();
}

if (empty($payment_method)) {
    echo json_encode([
        "success" => false,
        "message" => "Método de pago requerido."
    ]);
    exit();
}

if (empty($phone)) {
    echo json_encode([
        "success" => false,
        "message" => "Número telefónico requerido."
    ]);
    exit();
}

/* verificar usuario */
$sql = "
SELECT id,
       is_premium,
       premium_expires_at
FROM users
WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Usuario no encontrado."
    ]);
    exit();
}

/* activar o renovar premium */
$sql = "
UPDATE users
SET
    is_premium = 1,
    premium_expires_at =
        CASE
            WHEN premium_expires_at IS NOT NULL
                 AND premium_expires_at > NOW()
            THEN DATE_ADD(
                    premium_expires_at,
                    INTERVAL 30 DAY
                 )
            ELSE DATE_ADD(
                    NOW(),
                    INTERVAL 30 DAY
                 )
        END
WHERE id = ?
";

$stmt = $conn->prepare($sql);

$success = $stmt->execute([
    $id
]);

if (!$success) {
    echo json_encode([
        "success" => false,
        "message" => "No se pudo activar Premium."
    ]);
    exit();
}

/* obtener nueva fecha */
$sql = "
SELECT premium_expires_at
FROM users
WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

$updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "message" => "Premium activado correctamente.",
    "precio" => "99 pesos",
    "premium_expires_at" =>
        $updatedUser["premium_expires_at"]
]);
?>