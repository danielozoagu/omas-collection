<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

require_once __DIR__ . "/db.php";
$data = json_decode(file_get_contents("php://input"));

if (!$data) { echo json_encode(["error" => "No data received"]); exit; }

$hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
// Role is hardcoded to 'customer'
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')");
$stmt->bind_param("sss", $data->username, $data->email, $hashed_password);

if ($stmt->execute()) { echo json_encode(["message" => "Customer registered!"]); }
else { echo json_encode(["error" => $stmt->error]); }
?>