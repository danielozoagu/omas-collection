<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";

$order_id = filter_var($_GET['order_id'] ?? ($_POST['order_id'] ?? 0), FILTER_VALIDATE_INT);
$email    = trim($_GET['email'] ?? ($_POST['email'] ?? ''));

if (!$order_id || $order_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Please provide a valid Order ID."]);
    $conn->close();
    exit();
}

$sql = "SELECT o.id, o.total_amount, o.status, o.payment_status, o.tracking_number, o.created_at,
               u.username, u.email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?";

if (!empty($email)) {
    $sql .= " AND (u.email = ? OR ? = '')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $order_id, $email, $email);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
}

$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Order not found. Please verify the order number and email address."]);
    $conn->close();
    exit();
}

// Fetch order items
$item_stmt = $conn->prepare(
    "SELECT oi.quantity, oi.price,
            p.id AS product_id,
            IFNULL(p.name, 'Archived Product') AS name,
            p.image
     FROM order_items oi
     LEFT JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?"
);
$item_stmt->bind_param("i", $order['id']);
$item_stmt->execute();
$order['items'] = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$item_stmt->close();

echo json_encode([
    "success" => true,
    "order"   => $order
]);
$conn->close();
?>