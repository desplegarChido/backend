<?php
//backend/paypal_create_orden.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}


// Credenciales de PayPal Sandbox (Business)
$clientId = "AX8oxNu0iaqbUfmQHMbK_qvQWYhRNHRW2pyGUel48JrQxHXDfFZbfVQMp5-0YtPsUbbfJBGynxtXJChu";
$secret   = "EGY3zVeIKeiCxYZRg_k7jn0u4cTqMjRZR65RpfkECoqyoGSUO7Vh7yHXucfCUurwvf_iai_XjUls39G8";

// Crear orden de pago
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v2/checkout/orders");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Basic " . base64_encode("$clientId:$secret")
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "amount" => [
            "currency_code" => "MXN",
            "value" => "99.00"
        ],
        "description" => "Compra Premium Synotes"
    ]]
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
