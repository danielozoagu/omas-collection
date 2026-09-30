<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$sql = "SELECT orders.id, orders.total_amount, orders.status, orders.payment_status,
               orders.tracking_number, orders.created_at,
               users.username, users.email
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.created_at DESC";

$result = $conn->query($sql);
$orders = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $item_stmt = $conn->prepare(
            "SELECT oi.quantity, oi.price,
                    p.id AS product_id,
                    IFNULL(p.name, 'Archived Product') AS name,
                    p.image
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?"
        );
        $item_stmt->bind_param("i", $row['id']);
        $item_stmt->execute();
        $row['items'] = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $item_stmt->close();
        $orders[] = $row;
    }
}

echo json_encode(["success" => true, "orders" => $orders, "data" => $orders]);
$conn->close();
?>