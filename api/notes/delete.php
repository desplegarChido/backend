<?php
//backend/api/notes/delete.php

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {

    require_once __DIR__ . "/../../config/database.php";

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $id = $data["id"] ?? 0;
    $user_id = $data["user_id"] ?? 0;

    if (!$id || !$user_id) {

        echo json_encode([
            "success" => false,
            "message" => "Datos incompletos."
        ]);

        exit();
    }

    $sql = "
    DELETE FROM notes
    WHERE id = ?
    AND user_id = ?
    ";

    $stmt = $conn->prepare($sql);

    $success = $stmt->execute([
        $id,
        $user_id
    ]);

    echo json_encode([
        "success" => $success,
        "message" => $success
            ? "Nota eliminada correctamente."
            : "No se pudo eliminar la nota."
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}
?>