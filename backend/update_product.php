<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://127.0.0.1:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid product ID"]);
    $conn->close();
    exit();
}

$current_stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$current_stmt->bind_param("i", $id);
$current_stmt->execute();
$current_result = $current_stmt->get_result();
$current_product = $current_result->fetch_assoc();
$current_stmt->close();

if (!$current_product) {
    echo json_encode(["success" => false, "message" => "Product not found"]);
    $conn->close();
    exit();
}

$image_path = isset($_POST['existing_image']) && $_POST['existing_image'] !== ''
    ? $_POST['existing_image']
    : $current_product['image'];
$target_dir = __DIR__ . "/uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $filename = uniqid("product_", true) . ($extension ? "." . $extension : "");
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = "http://localhost/OMAS-COLLECTION-BACKEND/uploads/" . $filename;
    }
}

$stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, image=?, description=? WHERE id=?");
$name = $_POST['name'] ?? '';
$category = $_POST['category'] ?? '';
$price = (float) ($_POST['price'] ?? 0);
$stock = (int) ($_POST['stock'] ?? 0);
$description = $_POST['description'] ?? '';
$stmt->bind_param("ssdissi", $name, $category, $price, $stock, $image_path, $description, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Product updated!", "image" => $image_path]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>