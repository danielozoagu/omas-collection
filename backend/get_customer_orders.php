<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
$user_id = requireLogin();

$stmt = $conn->prepare("SELECT id, total_amount, status, payment_status, tracking_number, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];

while ($order = $result->fetch_assoc()) {
    $item_stmt = $conn->prepare("SELECT order_items.quantity, order_items.price, products.id AS product_id, products.name, products.image FROM order_items JOIN products ON order_items.product_id = products.id WHERE order_items.order_id = ?");
    $item_stmt->bind_param("i", $order['id']);
    $item_stmt->execute();
    $order['items'] = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $item_stmt->close();
    $orders[] = $order;
}

echo json_encode($orders);
?>