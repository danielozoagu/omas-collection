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
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Get Cart Items
if ($action == 'get') {
    $stmt = $conn->prepare("SELECT cart.id as cart_id, cart.quantity, products.name, products.price, products.image FROM cart JOIN products ON cart.product_id = products.id WHERE cart.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart = array();
    while($row = $result->fetch_assoc()) { $cart[] = $row; }
    echo json_encode($cart);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];

// Add to Cart
if ($action == 'add') {
    $product_id = intval($data['product_id'] ?? 0);
    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "A valid product is required"]);
        exit;
    }
    $check = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $update = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $update->bind_param("ii", $user_id, $product_id);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param("ii", $user_id, $product_id);
        $insert->execute();
    }
    echo json_encode(["message" => "Added to cart!"]);
    exit;
}

// Remove from Cart
if ($action == 'remove') {
    $cart_id = intval($data['cart_id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    echo json_encode(["message" => "Removed from cart!"]);
    exit;
}
?>