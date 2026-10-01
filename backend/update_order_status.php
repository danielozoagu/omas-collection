<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$data = json_decode(file_get_contents("php://input"), true);
$order_id        = filter_var($data['order_id']       ?? null, FILTER_VALIDATE_INT);
$status          = trim($data['status']               ?? '');
$payment_status  = trim($data['payment_status']       ?? 'Unpaid');
$tracking_number = trim($data['tracking_number']      ?? '');

$allowed_statuses = ['Pending','Processing','Shipped','Completed','Declined','Cancelled'];
$allowed_payment  = ['Unpaid','Pending','Paid','Successful','Failed','Refunded'];

if (!$order_id || !in_array($status, $allowed_statuses, true)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "A valid order ID and status are required. Allowed statuses: " . implode(', ', $allowed_statuses)
    ]);
    exit();
}

if (!$payment_status || !in_array($payment_status, $allowed_payment, true)) {
    $payment_status = 'Unpaid';
}

$stmt = $conn->prepare(
    "UPDATE orders SET status=?, payment_status=?, tracking_number=NULLIF(?,'') WHERE id=?"
);
$stmt->bind_param("sssi", $status, $payment_status, $tracking_number, $order_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    // Check if order actually exists (update may have set same values - not an error)
    $check = $conn->prepare("SELECT id FROM orders WHERE id=?");
    $check->bind_param("i", $order_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$exists) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Order #$order_id not found."]);
        $conn->close();
        exit();
    }
}

echo json_encode([
    "success"         => true,
    "message"         => "Order #$order_id updated to '$status' successfully.",
    "order_id"        => $order_id,
    "status"          => $status,
    "payment_status"  => $payment_status,
    "tracking_number" => $tracking_number ?: null
]);
$conn->close();
?>
