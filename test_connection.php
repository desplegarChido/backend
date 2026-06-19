// backend/test_connection.php

<?php

require_once "config/database.php";

echo json_encode([
    "success" => true,
    "message" => "Conectado a Aiven correctamente"
]);