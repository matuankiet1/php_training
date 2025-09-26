<?php
// delete.php (bản đã harden)
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
    // Không redirect về login trừ khi muốn bắt login lại — trả về list để rõ ngữ cảnh
    $_SESSION['error'] = 'Invalid CSRF token.';
    header('Location: list_users.php');
    exit;
}

// Kiểm tra quyền: ví dụ chỉ admin được xóa user
// Tùy app bạn thay điều kiện này theo logic quyền thực tế
if (empty($_SESSION['user']) || ($_SESSION['user']['type'] ?? '') !== 'admin') {
    $_SESSION['error'] = "You don't have permission to perform this action.";
    header('Location: list_users.php');
    exit;
}

// Lấy ID từ POST, validate rõ ràng bằng filter và ép kiểu int
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    $_SESSION['error'] = 'Invalid user ID.';
    header('Location: list_users.php');
    exit;
}

// Đảm bảo ép kiểu an toàn khi truyền xuống model
$id = (int) $id;

$userModel = new UserModel();

try {
    // Gọi model (model nên dùng prepared statement và LIMIT 1)
    $deleted = $userModel->deleteUserById($id);

    if ($deleted) {
        $_SESSION['success'] = 'User deleted successfully.';
    } else {
        // Không tiết lộ lý do thất bại chi tiết cho user
        $_SESSION['error'] = 'Failed to delete user.';
    }
} catch (Throwable $e) {
    // Log chi tiết nội bộ nhưng hiển thị message chung cho client
    error_log(sprintf(
        "delete.php: error deleting user id=%d by user=%s - %s in %s:%d",
        $id,
        $_SESSION['user']['name'] ?? 'guest',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));

    $_SESSION['error'] = 'An internal error occurred. Please try again later.';
}

header('Location: list_users.php');
exit;
