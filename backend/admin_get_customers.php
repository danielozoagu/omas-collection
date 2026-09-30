<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$result = $conn->query(
    "SELECT id, username, email, role, created_at FROM users WHERE role='customer' OR role IS NULL OR role='' ORDER BY id DESC"
);
$customers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) $customers[] = $row;
}
echo json_encode(["success"=>true, "customers"=>$customers, "data"=>$customers]);
$conn->close();
?>