<?php

$host = "mysql-35315aa1-hasweek1atm23-3ce8.a.aivencloud.com";
$port = "19566";
$dbname = "syanote_db";

$username = "avnadmin";
$password = "AVNS_qrT-f0K4BeY1CgtvuIU";

try {

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $conn = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {

    die(json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]));
}