<?php
// delete.php (bản đã harden + chống XSS gián tiếp)
include 'track_activity.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'models/UserModel.php';
require_once __DIR__ . '/helpers/csrf.php';

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// CSRF check (verify_csrf phải dùng hash_equals internally)
if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    header('Location: list_users.php');
    exit;
}

// Kiểm tra quyền
if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? '') !== 'admin') {
    $_SESSION['error'] = "You don't have permission to perform this action.";
    header('Location: list_users.php');
    exit;
}

// Validate ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    $_SESSION['error'] = 'Invalid user ID.';
    header('Location: list_users.php');
    exit;
}
$id = (int) $id;

$userModel = new UserModel();

try {
    $deleted = $userModel->deleteUserById($id);

    if ($deleted) {
        $_SESSION['success'] = 'User deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete user.';
    }
} catch (Throwable $e) {
    // Escape dữ liệu khi ghi log để tránh XSS nếu log được hiển thị qua giao diện web
    $safeUser = isset($_SESSION['user']['name'])
        ? str_replace(["\n", "\r"], [' ', ' '], $_SESSION['user']['name'])
        : 'guest';

    error_log(sprintf(
        "delete.php: error deleting user id=%d by user=%s - %s in %s:%d",
        $id,
        $safeUser,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));

    $_SESSION['error'] = 'An internal error occurred. Please try again later.';
}

header('Location: list_users.php');
exit;
