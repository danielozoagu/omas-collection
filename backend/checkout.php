<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
$user_id = requireLogin();

$conn->begin_transaction();
try {
// 1. Get the user's cart items
$stmt = $conn->prepare("SELECT cart.product_id, cart.quantity, products.price, products.stock FROM cart JOIN products ON cart.product_id = products.id WHERE cart.user_id = ? FOR UPDATE");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

if (count($cart_items) == 0) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Cart is empty"]);
    exit;
}

// 2. Calculate the total price
$total_amount = 0;
foreach ($cart_items as $item) {
    if ((int) $item['quantity'] <= 0 || (int) $item['stock'] < (int) $item['quantity']) {
        throw new RuntimeException("Insufficient stock for one or more items");
    }
    $total_amount += ($item['price'] * $item['quantity']);
}

// 3. Create the Order
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'Processing')");
$stmt->bind_param("id", $user_id, $total_amount);
$stmt->execute();
$order_id = $conn->insert_id;

// 4. Save items, and DEDUCT STOCK FROM PRODUCTS
$stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
$update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

foreach ($cart_items as $item) {
    $product_id = (int) $item['product_id'];
    $quantity = (int) $item['quantity'];
    $price = (float) $item['price'];
    // Insert into order_items
    $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
    $stmt->execute();

    // Decrease the stock
    $update_stock->bind_param("iii", $quantity, $product_id, $quantity);
    $update_stock->execute();
    if ($update_stock->affected_rows !== 1) {
        throw new RuntimeException("Insufficient stock for one or more items");
    }
}

// 5. Empty the user's cart
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

if (!$conn->commit()) {
    throw new RuntimeException("Could not complete the order");
}
echo json_encode(["success" => true, "message" => "Order placed successfully!", "total" => $total_amount]);
} catch (Throwable $error) {
    $conn->rollback();
    http_response_code(409);
    echo json_encode(["success" => false, "error" => $error->getMessage()]);
}
?>