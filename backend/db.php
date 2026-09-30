<?php
$host     = getenv('DB_HOST')     ?: "localhost";
$user     = getenv('DB_USER')     ?: "root";
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : "";
$database = getenv('DB_NAME')     ?: "omas_collection";
$port     = intval(getenv('DB_PORT') ?: 3306);
$use_ssl  = getenv('DB_SSL') === 'true' || getenv('TIDB_ENABLE_SSL') === 'true' || $port == 4000 || strpos($host, 'tidbcloud.com') !== false;

mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_init();

if ($use_ssl) {
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

if (!@$conn->real_connect($host, $user, $password, $database, $port, NULL, $use_ssl ? MYSQLI_CLIENT_SSL : 0)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

$conn->set_charset("utf8mb4");

try {
    $table_check = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($table_check && $table_check->num_rows > 0) {
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
            if ($column_result && $column_result->num_rows === 0) {
                $conn->query($alter_query);
            }
        }
    }
} catch (Throwable $e) {
    // Ignore schema check error
}
?>