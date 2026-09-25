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

$sql = "SELECT orders.id, orders.total_amount, orders.status, orders.payment_status, orders.tracking_number, orders.created_at, users.username, users.email 
        FROM orders 
        JOIN users ON orders.user_id = users.id 
        ORDER BY orders.created_at DESC";

$result = $conn->query($sql);

$orders = array();
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $item_stmt = $conn->prepare("SELECT order_items.quantity, order_items.price, products.id AS product_id, products.name, products.image FROM order_items JOIN products ON order_items.product_id = products.id WHERE order_items.order_id = ?");
        $item_stmt->bind_param("i", $row['id']);
        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        $row['items'] = $item_result->fetch_all(MYSQLI_ASSOC);
        $item_stmt->close();
        $orders[] = $row;
    }
}

echo json_encode($orders);
?>