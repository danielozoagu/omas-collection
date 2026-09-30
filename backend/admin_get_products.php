<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$result = $conn->query(
    "SELECT id, name, category, price, stock, image, description, created_at FROM products ORDER BY id DESC"
);
$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) $products[] = $row;
}
echo json_encode(["success"=>true, "products"=>$products, "data"=>$products]);
$conn->close();
?>