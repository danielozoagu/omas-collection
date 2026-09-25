<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAdmin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Admin access required"
        ]);
        exit();
    }
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Login required"
        ]);
        exit();
    }
    return (int) $_SESSION['user_id'];
}
?>
