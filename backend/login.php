<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://127.0.0.1:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . "/db.php";
$data = json_decode(file_get_contents("php://input"));

if (!$data) { echo json_encode(["error" => "No data received"]); exit; }

$stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $data->email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($data->password, $row['password'])) {
        session_start();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        echo json_encode(["message" => "Login successful", "user" => [
            "id" => $row['id'],
            "username" => $row['username'],
            "email" => $row['email'],
            "role" => $row['role']
        ]]);
    } else { echo json_encode(["error" => "Wrong password"]); }
} else { echo json_encode(["error" => "User not found"]); }
?>