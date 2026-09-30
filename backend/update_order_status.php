<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$data = json_decode(file_get_contents("php://input"), true);
$order_id       = filter_var($data['order_id']       ?? null, FILTER_VALIDATE_INT);
$status         = trim($data['status']               ?? '');
$payment_status = trim($data['payment_status']       ?? 'Unpaid');
$tracking_number= trim($data['tracking_number']      ?? '');

$allowed_statuses = ['Pending','Processing','Shipped','Completed','Declined','Cancelled'];
$allowed_payment  = ['Unpaid','Pending','Paid','Successful','Failed','Refunded'];

if (!$order_id || !in_array($status, $allowed_statuses, true)) {
    http_response_code(400);
    echo json_encode(["success"=>false,"message"=>"A valid order and status are required"]);
    exit();
}
if ($payment_status && !in_array($payment_status, $allowed_payment, true)) {
    $payment_status = 'Unpaid';
}

$stmt = $conn->prepare(
    "UPDATE orders SET status=?, payment_status=?, tracking_number=NULLIF(?,'') WHERE id=?"
);
$stmt->bind_param("sssi", $status, $payment_status, $tracking_number, $order_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    "success"         => true,
    "status"          => $status,
    "payment_status"  => $payment_status,
    "tracking_number" => $tracking_number
]);
$conn->close();
?>
