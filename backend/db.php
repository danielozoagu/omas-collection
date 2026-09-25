<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "omas_collection";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Keep older installations compatible with the admin order workflow.
$status_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
if ($status_column && $status_column->num_rows === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Processing'");
}
$order_columns = [
    'payment_status' => "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(30) NOT NULL DEFAULT 'Unpaid'",
    'tracking_number' => "ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(120) NULL"
];
foreach ($order_columns as $column => $alter_query) {
    $column_result = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
    if ($column_result && $column_result->num_rows === 0) $conn->query($alter_query);
}
?>