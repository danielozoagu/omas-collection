<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$sales_r    = $conn->query("SELECT COUNT(*) as total_orders, IFNULL(SUM(total_amount),0) as total_revenue FROM orders");
$sales      = $sales_r ? $sales_r->fetch_assoc() : ["total_orders"=>0,"total_revenue"=>0];

$prod_r     = $conn->query("SELECT COUNT(*) as total_products FROM products");
$products   = $prod_r ? $prod_r->fetch_assoc() : ["total_products"=>0];

$cust_r     = $conn->query("SELECT COUNT(*) as total_customers FROM users WHERE role='customer' OR role IS NULL OR role=''");
$customers  = $cust_r ? $cust_r->fetch_assoc() : ["total_customers"=>0];

$pending_r  = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE status='Pending' OR status='Processing'");
$pending    = $pending_r ? $pending_r->fetch_assoc() : ["cnt"=>0];

$recent_r   = $conn->query(
    "SELECT o.id, o.total_amount, o.status, o.payment_status, o.created_at, u.username, u.email
     FROM orders o JOIN users u ON o.user_id=u.id
     ORDER BY o.created_at DESC LIMIT 5"
);
$recent_orders = [];
if ($recent_r) {
    while ($row = $recent_r->fetch_assoc()) $recent_orders[] = $row;
}

$stats = [
    "total_revenue"   => (float)($sales['total_revenue']   ?? 0),
    "total_orders"    => (int)  ($sales['total_orders']    ?? 0),
    "total_products"  => (int)  ($products['total_products']?? 0),
    "total_customers" => (int)  ($customers['total_customers']??0),
    "pending_orders"  => (int)  ($pending['cnt']           ?? 0),
    "recent_orders"   => $recent_orders
];

echo json_encode(array_merge(["success" => true, "data" => $stats], $stats));
$conn->close();
?>