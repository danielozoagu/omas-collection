<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";

$sql = "SELECT id, name, category, price, stock, image, description, created_at FROM products ORDER BY id DESC";
$result = $conn->query($sql);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);
$conn->close();
?>