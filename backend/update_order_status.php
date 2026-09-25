<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin();

$data = json_decode(file_get_contents("php://input"), true);
$order_id = filter_var($data['order_id'] ?? null, FILTER_VALIDATE_INT);
$status = trim($data['status'] ?? '');
$payment_status = trim($data['payment_status'] ?? '');
$tracking_number = trim($data['tracking_number'] ?? '');
$allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Declined', 'Cancelled'];
$allowed_payment_statuses = ['Unpaid', 'Pending', 'Paid', 'Successful', 'Failed', 'Refunded'];

if (!$order_id || !in_array($status, $allowed_statuses, true) || ($payment_status !== '' && !in_array($payment_status, $allowed_payment_statuses, true))) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "A valid order and status are required"]);
    exit();
}

$stmt = $conn->prepare("UPDATE orders SET status = ?, payment_status = ?, tracking_number = NULLIF(?, '') WHERE id = ?");
$payment_status = $payment_status ?: 'Unpaid';
$stmt->bind_param("sssi", $status, $payment_status, $tracking_number, $order_id);
$stmt->execute();
echo json_encode(["success" => true, "status" => $status, "payment_status" => $payment_status, "tracking_number" => $tracking_number]);
?>