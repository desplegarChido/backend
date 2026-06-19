<?php
//backend/paypal_capture_orden.php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}
require_once "config/database.php";

// Credenciales de PayPal Sandbox (Business)
$clientId = "AX8oxNu0iaqbUfmQHMbK_qvQWYhRNHRW2pyGUel48JrQxHXDfFZbfVQMp5-0YtPsUbbfJBGynxtXJChu";
$secret   = "EGY3zVeIKeiCxYZRg_k7jn0u4cTqMjRZR65RpfkECoqyoGSUO7Vh7yHXucfCUurwvf_iai_XjUls39G8";

$data = json_decode(file_get_contents("php://input"), true);
$orderId = $data["orderID"] ?? null;
$userId  = $data["user_id"] ?? null;

if (!$orderId || !$userId) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit();
}

// Capturar orden en PayPal
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v2/checkout/orders/$orderId/capture");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Basic " . base64_encode("$clientId:$secret")
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// Validar estado COMPLETED
$statusRoot = $result["status"] ?? null;
$statusCapture = $result["purchase_units"][0]["payments"]["captures"][0]["status"] ?? null;

if ($statusRoot === "COMPLETED" || $statusCapture === "COMPLETED") {
    // Activar Premium en BD
    $sql = "UPDATE users 
            SET is_premium = 1, premium_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);

    echo json_encode([
        "success" => true,
        "message" => "Pago confirmado. Premium activado por 30 días.",
        "precio" => "99 pesos",
        "paypal_response" => $result
    ]);
} else {

error_log("PayPal capture response: " . print_r($result, true));

    echo json_encode([
        "success" => false,
        "message" => "Error al capturar el pago.",
        "paypal_response" => $result
    ]);
}
