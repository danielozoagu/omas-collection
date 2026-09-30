<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "POST only"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$email    = trim($data['email']    ?? '');
$password =      $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit();
}

$stmt = $conn->prepare(
    "SELECT id, username, email, password, role FROM users WHERE email = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    exit();
}

if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Administrator access only"]);
    exit();
}

// Generate secure token
$token = bin2hex(random_bytes(32));

// Remove old tokens, insert new one (30-day expiry)
$del = $conn->prepare("DELETE FROM admin_tokens WHERE user_id = ?");
$del->bind_param("i", $user['id']);
$del->execute();
$del->close();

$ins = $conn->prepare(
    "INSERT INTO admin_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))"
);
$ins->bind_param("is", $user['id'], $token);
$ins->execute();
$ins->close();

unset($user['password']);

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "data"    => [
        "user"  => $user,
        "token" => $token
    ]
]);
$conn->close();
?>
