<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid order ID"]);
    exit;
}

$sql = "SELECT order_items.quantity, order_items.price, IFNULL(products.name, 'Archived Product') AS name, products.image 
        FROM order_items 
        LEFT JOIN products ON order_items.product_id = products.id 
        WHERE order_items.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$items = array();
while($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();
echo json_encode($items);
$conn->close();
?>