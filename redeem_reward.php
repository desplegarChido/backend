<?php
//backend/redeem_reward.php

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

$user_id = $data["user_id"] ?? null;
$reward_id = $data["reward_id"] ?? null;

if (!$user_id || !$reward_id) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos."
    ]);
    exit();
}

/* obtener usuario */
$sql = "
SELECT points
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

/* obtener recompensa */
$sql = "
SELECT id,
       reward_name,
       points_required
FROM rewards
WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$reward_id]);

$reward = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reward) {
    echo json_encode([
        "success" => false,
        "message" => "Recompensa no encontrada."
    ]);
    exit();
}

$currentPoints = intval(
    $user["points"]
);

$cost = intval(
    $reward["points_required"]
);

if ($currentPoints < $cost) {
    echo json_encode([
        "success" => false,
        "message" => "No tienes suficientes puntos."
    ]);
    exit();
}

/* descontar puntos */
$newPoints = $currentPoints - $cost;

$sql = "
UPDATE users
SET points = ?
WHERE id = ?
";

$stmt = $conn->prepare($sql);

$success = $stmt->execute([
    $newPoints,
    $user_id
]);

if (!$success) {
    echo json_encode([
        "success" => false,
        "message" => "No se pudieron descontar los puntos."
    ]);
    exit();
}

/* registrar canje */
$sql = "
INSERT INTO redeemed_rewards
(
    user_id,
    reward_id
)
VALUES
(
    ?, ?
)
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    $user_id,
    $reward_id
]);

echo json_encode([
    "success" => true,
    "message" => "🎉 Recompensa canjeada correctamente.",
    "reward" => $reward["reward_name"],
    "cost" => $cost,
    "points" => $newPoints
]);
?>