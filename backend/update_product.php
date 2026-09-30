<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
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
    http_response_code(444);
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
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $prefix = strpos(<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
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
    http_response_code(444);
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
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $image_path = "$scheme://$host/OMAS-COLLECTION-BACKEND/uploads/" . $filename;
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
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>SERVER["REQUEST_URI"] ?? "", "OMAS-COLLECTION-BACKEND") !== false ? "/OMAS-COLLECTION-BACKEND/uploads/" : "/uploads/"; $image_path = "$scheme://$host$prefix" . $filename;
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
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>