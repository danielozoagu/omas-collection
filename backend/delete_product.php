<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$request_body = json_decode(file_get_contents("php://input"), true) ?: [];
$id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id'] ?? ($request_body['id'] ?? 0));

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid product ID"]);
    $conn->close();
    exit();
}

$conn->begin_transaction();

try {
    $check_stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $product_exists = $check_stmt->get_result()->num_rows > 0;
    $check_stmt->close();

    if (!$product_exists) {
        throw new RuntimeException("Product not found");
    }

    foreach (["cart", "wishlist", "order_items"] as $table) {
        $dependent_stmt = $conn->prepare("DELETE FROM $table WHERE product_id = ?");
        $dependent_stmt->bind_param("i", $id);
        if (!$dependent_stmt->execute()) {
            throw new RuntimeException($dependent_stmt->error);
        }
        $dependent_stmt->close();
    }

    $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delete_stmt->bind_param("i", $id);
    if (!$delete_stmt->execute() || $delete_stmt->affected_rows !== 1) {
        throw new RuntimeException($delete_stmt->error ?: "Product could not be deleted");
    }
    $delete_stmt->close();

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Product deleted successfully!"]);
} catch (Throwable $error) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Delete failed: " . $error->getMessage()]);
}

$conn->close();
?>