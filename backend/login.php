<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$data = json_decode(file_get_contents('php://input'));

if (!$data || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

$stmt = $conn->prepare('SELECT id, username, email, password, role FROM users WHERE email = ?');
$stmt->bind_param('s', $data->email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($data->password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];

        $token = bin2hex(random_bytes(32));
        if ($row['role'] === 'admin') {
            @$del = $conn->prepare('DELETE FROM admin_tokens WHERE user_id = ?');
            if ($del) {
                $del->bind_param('i', $row['id']);
                $del->execute();
                $del->close();
            }
            @$ins = $conn->prepare('INSERT INTO admin_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))');
            if ($ins) {
                $ins->bind_param('is', $row['id'], $token);
                $ins->execute();
                $ins->close();
            }
        }

        echo json_encode([
            'message' => 'Login successful',
            'token'   => $token,
            'user' => [
                'id'       => $row['id'],
                'username' => $row['username'],
                'email'    => $row['email'],
                'role'     => $row['role'],
                'token'    => $token
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password']);
    }
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
}
$stmt->close();
$conn->close();
?>