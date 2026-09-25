<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin();

// Total Revenue and Orders
$sales_result = $conn->query("SELECT COUNT(*) as total_orders, IFNULL(SUM(total_amount), 0) as total_revenue FROM orders");
$sales = $sales_result->fetch_assoc();

// Total Products
$product_result = $conn->query("SELECT COUNT(*) as total_products FROM products");
$products = $product_result->fetch_assoc();

// Combine into one response
echo json_encode([
    "total_revenue" => $sales['total_revenue'],
    "total_orders" => $sales['total_orders'],
    "total_products" => $products['total_products']
]);
?>