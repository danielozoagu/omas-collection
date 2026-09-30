<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin();

// Total Revenue and Orders
$sales_result = $conn->query("SELECT COUNT(*) as total_orders, IFNULL(SUM(total_amount), 0) as total_revenue FROM orders");
$sales = $sales_result ? $sales_result->fetch_assoc() : ["total_orders" => 0, "total_revenue" => 0];

// Total Products
$product_result = $conn->query("SELECT COUNT(*) as total_products FROM products");
$products = $product_result ? $product_result->fetch_assoc() : ["total_products" => 0];

// Total Customers
$customer_result = $conn->query("SELECT COUNT(*) as total_customers FROM users WHERE role = 'customer'");
$customers = $customer_result ? $customer_result->fetch_assoc() : ["total_customers" => 0];

echo json_encode([
    "total_revenue" => (float) ($sales['total_revenue'] ?? 0),
    "total_orders" => (int) ($sales['total_orders'] ?? 0),
    "total_products" => (int) ($products['total_products'] ?? 0),
    "total_customers" => (int) ($customers['total_customers'] ?? 0)
]);
$conn->close();
?>