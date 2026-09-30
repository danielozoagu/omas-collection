<?php
header("Content-Type: application/json; charset=UTF-8");
echo json_encode([
    "status" => "online",
    "message" => "OMAS Collection API Server is active and running",
    "version" => "1.0.0",
    "database" => "TiDB Cloud Connected"
]);
?>