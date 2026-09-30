<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$image_path = "";
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
        $prefix = strpos(<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
requireAdmin($conn);

$image_path = "";
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
        $image_path = "$scheme://$host/OMAS-COLLECTION-BACKEND/uploads/" . $filename;
    }
} else if (isset($_POST['image']) && filter_var($_POST['image'], FILTER_VALIDATE_URL)) {
    $image_path = trim($_POST['image']);
}

$stmt = $conn->prepare("INSERT INTO products (name, category, price, stock, image, description) VALUES (?, ?, ?, ?, ?, ?)");
$name = $_POST['name'] ?? '';
$category = $_POST['category'] ?? '';
$price = (float) ($_POST['price'] ?? 0);
$stock = (int) ($_POST['stock'] ?? 0);
$description = $_POST['description'] ?? '';
$stmt->bind_param("ssdiss", $name, $category, $price, $stock, $image_path, $description);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Product added!"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>SERVER["REQUEST_URI"] ?? "", "OMAS-COLLECTION-BACKEND") !== false ? "/OMAS-COLLECTION-BACKEND/uploads/" : "/uploads/"; $image_path = "$scheme://$host$prefix" . $filename;
    }
} else if (isset($_POST['image']) && filter_var($_POST['image'], FILTER_VALIDATE_URL)) {
    $image_path = trim($_POST['image']);
}

$stmt = $conn->prepare("INSERT INTO products (name, category, price, stock, image, description) VALUES (?, ?, ?, ?, ?, ?)");
$name = $_POST['name'] ?? '';
$category = $_POST['category'] ?? '';
$price = (float) ($_POST['price'] ?? 0);
$stock = (int) ($_POST['stock'] ?? 0);
$description = $_POST['description'] ?? '';
$stmt->bind_param("ssdiss", $name, $category, $price, $stock, $image_path, $description);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Product added!"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>