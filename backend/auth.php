<?php
require_once __DIR__ . '/cors.php';

if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_set_cookie_params([
            'lifetime' => 86400,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    @session_start();
}

/**
 * Get the Bearer token from headers (supporting Apache, CGI, FastCGI, Nginx).
 */
function getBearerToken(): ?string {
    $auth = '';
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? $headers['X-Admin-Token'] ?? $headers['x-admin-token'] ?? '';
    }
    if (empty($auth)) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '';
    }
    if (empty($auth) && isset($_GET['token'])) {
        return trim($_GET['token']);
    }
    if (preg_match('/Bearer\s+(.+)/i', $auth, $matches)) {
        return trim($matches[1]);
    }
    return !empty($auth) ? trim($auth) : null;
}

/**
 * Validate a Bearer token against admin_tokens table.
 */
function validateBearerToken(mysqli $conn): ?array {
    $token = getBearerToken();
    if (!$token) return null;

    $stmt = @$conn->prepare(
        "SELECT u.id, u.username, u.email, u.role
         FROM admin_tokens t
         JOIN users u ON u.id = t.user_id
         WHERE t.token = ? AND t.expires_at > NOW()
         LIMIT 1"
    );
    if (!$stmt) return null;
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/**
 * Require admin access - supports BOTH Bearer token (Desktop app / Web app) and Session.
 */
function requireAdmin(?mysqli $conn = null) {
    if ($conn === null) {
        global $conn;
    }

    // 1. Check Bearer token
    if ($conn && getBearerToken()) {
        $user = validateBearerToken($conn);
        if ($user && $user['role'] === 'admin') {
            return $user;
        }
    }

    // 2. Check Web session
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'Admin',
            'email'    => $_SESSION['email'] ?? '',
            'role'     => 'admin'
        ];
    }

    // 3. Fallback check for web client passing user header / token
    $admin_role_header = $_SERVER['HTTP_X_ADMIN_ROLE'] ?? '';
    if ($admin_role_header === 'admin' && isset($_SESSION['user_id'])) {
        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'Admin',
            'email'    => $_SESSION['email'] ?? '',
            'role'     => 'admin'
        ];
    }

    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit();
}

/**
 * Require login access.
 */
function requireLogin(?mysqli $conn = null): int {
    if ($conn === null) {
        global $conn;
    }

    if ($conn && getBearerToken()) {
        $user = validateBearerToken($conn);
        if ($user) return (int) $user['id'];
    }

    if (isset($_SESSION['user_id'])) {
        return (int) $_SESSION['user_id'];
    }

    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit();
}
?>