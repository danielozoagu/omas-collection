<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
$user_id = requireLogin();
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get Cart Items
if ($action === 'get') {
    $stmt = $conn->prepare("SELECT cart.id as cart_id, cart.product_id, cart.quantity, products.name, products.price, products.image, products.stock FROM cart JOIN products ON cart.product_id = products.id WHERE cart.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart = array();
    while($row = $result->fetch_assoc()) { $cart[] = $row; }
    echo json_encode($cart);
    $stmt->close();
    $conn->close();
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];

// Add to Cart
if ($action === 'add') {
    $product_id = intval($data['product_id'] ?? 0);
    $qty_to_add = max(1, intval($data['quantity'] ?? 1));
    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "A valid product is required"]);
        exit;
    }
    $check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $update = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
        $update->bind_param("iii", $qty_to_add, $user_id, $product_id);
        $update->execute();
        $update->close();
    } else {
        $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $user_id, $product_id, $qty_to_add);
        $insert->execute();
        $insert->close();
    }
    $check->close();
    echo json_encode(["success" => true, "message" => "Added to cart!"]);
    $conn->close();
    exit;
}

// Remove from Cart
if ($action === 'remove') {
    $cart_id = intval($data['cart_id'] ?? 0);
    $product_id = intval($data['product_id'] ?? 0);
    if ($cart_id > 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
    } else if ($product_id > 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $product_id, $user_id);
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Cart item or product ID is required"]);
        exit;
    }
    $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => true, "message" => "Removed from cart!"]);
    $conn->close();
    exit;
}

// Update Cart Quantity
if ($action === 'update') {
    $cart_id = intval($data['cart_id'] ?? 0);
    $quantity = intval($data['quantity'] ?? 0);
    if ($cart_id <= 0 || $quantity <= 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Item removed from cart"]);
    } else {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["success" => true, "message" => "Cart updated"]);
    }
    $conn->close();
    exit;
}

http_response_code(400);
echo json_encode(["success" => false, "message" => "Invalid cart action"]);
?>