<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

// Support both JSON body and form-data POST
$input = json_decode(file_get_contents("php://input"), true) ?: [];

$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($input['id']) ? intval($input['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0));
if ($id <= 0) {
    http_response_code(400);
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
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Product not found"]);
    $conn->close();
    exit();
}

$image_path = $current_product['image'] ?? '';
$target_dir = __DIR__ . "/uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $filename = time() . "_" . uniqid() . ($extension ? "." . $extension : "");
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $prefix = (strpos($_SERVER['REQUEST_URI'] ?? '', 'OMAS-COLLECTION-BACKEND') !== false) ? '/OMAS-COLLECTION-BACKEND/uploads/' : '/uploads/';
        $image_path = "$scheme://$host$prefix" . $filename;
    }
} else if (isset($_POST['image']) && filter_var($_POST['image'], FILTER_VALIDATE_URL)) {
    $image_path = trim($_POST['image']);
} else if (isset($input['image']) && filter_var($input['image'], FILTER_VALIDATE_URL)) {
    $image_path = trim($input['image']);
}

$name = isset($_POST['name']) ? trim($_POST['name']) : (isset($input['name']) ? trim($input['name']) : '');
$category = isset($_POST['category']) ? trim($_POST['category']) : (isset($input['category']) ? trim($input['category']) : '');
$price = isset($_POST['price']) ? (float)$_POST['price'] : (isset($input['price']) ? (float)$input['price'] : 0);
$stock = isset($_POST['stock']) ? (int)$_POST['stock'] : (isset($input['stock']) ? (int)$input['stock'] : 0);
$description = isset($_POST['description']) ? trim($_POST['description']) : (isset($input['description']) ? trim($input['description']) : '');

$stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, image=?, description=? WHERE id=?");
$stmt->bind_param("ssdissi", $name, $category, $price, $stock, $image_path, $description, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Product updated!", "image" => $image_path]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>