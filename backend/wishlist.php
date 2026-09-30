<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
$authenticated_user_id = requireLogin();

$action = $_GET['action'] ?? 'get';
$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = [];
}

if ($action === 'get') {
    $stmt = $conn->prepare("SELECT w.id AS wishlist_id, w.product_id, p.name, p.price, p.image, p.category, p.stock, p.description
            FROM wishlist w
            JOIN products p ON p.id = w.product_id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC");

    $stmt->bind_param("i", $authenticated_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }

    echo json_encode($items);
    $stmt->close();
    $conn->close();
    exit();
}

if ($action === 'add') {
    $product_id = intval($input['product_id'] ?? 0);

    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "A valid product is required"]);
        exit();
    }

    $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $authenticated_user_id, $product_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Added to wishlist"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $conn->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

if ($action === 'remove') {
    $product_id = intval($input['product_id'] ?? 0);

    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "A valid product is required"]);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $authenticated_user_id, $product_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Removed from wishlist"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $conn->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

http_response_code(400);
echo json_encode(["success" => false, "message" => "Unknown action"]);
?>