<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["user" => null]);
    exit();
}

$stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    session_destroy();
}
echo json_encode(["user" => $user ?: null]);
$stmt->close();
$conn->close();
?>