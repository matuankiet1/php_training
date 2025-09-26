<?php
include 'track_activity.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/helpers/csrf.php';

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify CSRF token
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
    $_SESSION['error'] = "Invalid CSRF token.";
    header("Location: login.php");
    exit;
}

// Xóa session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Đặt flash message nếu cần
session_start();
$_SESSION['success'] = "You have been logged out.";

// Quay lại login
header("Location: login.php");
exit;
