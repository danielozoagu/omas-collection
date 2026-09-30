<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->username) || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["error" => "Username, email, and password are required"]);
    exit;
}

// Check if email already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $data->email);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    echo json_encode(["error" => "An account with this email already exists."]);
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

$hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')");
$stmt->bind_param("sss", $data->username, $data->email, $hashed_password);

if ($stmt->execute()) {
    $new_user_id = $conn->insert_id;
    require_once __DIR__ . "/auth.php";
    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['role'] = 'customer';
    
    echo json_encode([
        "message" => "Customer registered successfully!",
        "user" => [
            "id" => $new_user_id,
            "username" => $data->username,
            "email" => $data->email,
            "role" => "customer"
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Registration failed: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>