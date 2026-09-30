<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
$user_id = requireLogin();

$conn->begin_transaction();
try {
    // 1. Get the user's cart items
    $stmt = $conn->prepare("SELECT cart.product_id, cart.quantity, products.name, products.price, products.stock FROM cart JOIN products ON cart.product_id = products.id WHERE cart.user_id = ? FOR UPDATE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (count($cart_items) === 0) {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Cart is empty"]);
        exit;
    }

    // 2. Calculate the total price & check stock
    $total_amount = 0;
    foreach ($cart_items as $item) {
        if ((int) $item['quantity'] <= 0 || (int) $item['stock'] < (int) $item['quantity']) {
            throw new RuntimeException("Insufficient stock for item: " . ($item['name'] ?? 'Product'));
        }
        $total_amount += ($item['price'] * $item['quantity']);
    }

    // 3. Create the Order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, payment_status) VALUES (?, ?, 'Processing', 'Paid')");
    $stmt->bind_param("id", $user_id, $total_amount);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();

    // 4. Save items, and DEDUCT STOCK FROM PRODUCTS
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

    foreach ($cart_items as $item) {
        $product_id = (int) $item['product_id'];
        $quantity = (int) $item['quantity'];
        $price = (float) $item['price'];
        
        $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $item_stmt->execute();

        $update_stock->bind_param("iii", $quantity, $product_id, $quantity);
        $update_stock->execute();
        if ($update_stock->affected_rows !== 1) {
            throw new RuntimeException("Insufficient stock for item: " . ($item['name'] ?? 'Product'));
        }
    }
    $item_stmt->close();
    $update_stock->close();

    // 5. Empty the user's cart
    $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_cart->bind_param("i", $user_id);
    $clear_cart->execute();
    $clear_cart->close();

    if (!$conn->commit()) {
        throw new RuntimeException("Could not complete the order transaction");
    }
    echo json_encode([
        "success" => true,
        "message" => "Order placed successfully!",
        "order_id" => $order_id,
        "total" => $total_amount
    ]);
} catch (Throwable $error) {
    $conn->rollback();
    http_response_code(409);
    echo json_encode(["success" => false, "error" => $error->getMessage()]);
}
$conn->close();
?>