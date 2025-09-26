<?php
include 'track_activity.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/helpers/csrf.php'; // đồng bộ (chưa dùng ở view)
require_once 'models/UserModel.php';

// -------------------------
// Security HTTP headers (tăng cường chống XSS / clickjacking / sniffing)
// Lưu ý: nếu bạn đã set những header này ở nơi khác (ví dụ middleware/nginx) thì có thể bỏ.
// -------------------------
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: geolocation=(), microphone=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'");

// -------------------------
// Helper escape HTML
// -------------------------
function e(string $str = ''): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$userModel = new UserModel();

// Lấy ID an toàn
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user = null;
if ($id) {
    $result = $userModel->findUserById($id);
    // đảm bảo dạng $user là mảng một dòng (tùy model trả về)
    $user = $result ? (is_array($result) && isset($result[0]) ? $result[0] : $result) : null;
}

// Lấy flash messages (đã unset ở đây, sẽ in ra sau với escape)
$successMsg = $_SESSION['success'] ?? '';
$errorMsg   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo e('User Profile'); ?></title>
    <?php include 'views/meta.php'; ?>
</head>
<body>
<?php include 'views/header.php'; ?>

<div class="container">

    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <?php echo e($successMsg); ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger">
            <?php echo e($errorMsg); ?>
        </div>
    <?php endif; ?>

    <?php if ($user): ?>
        <div class="alert alert-info" role="alert">
            <?php echo e('User Profile'); ?>
        </div>

        <div class="form-group">
            <label>Name: </label>
            <span><?php echo e($user['name'] ?? ''); ?></span>
        </div>

        <div class="form-group">
            <label>Fullname: </label>
            <span><?php echo e($user['fullname'] ?? ''); ?></span>
        </div>

        <div class="form-group">
            <label>Email: </label>
            <span><?php echo e($user['email'] ?? ''); ?></span>
        </div>

        <div class="form-group">
            <label>Type: </label>
            <span><?php echo e($user['type'] ?? 'user'); ?></span>
        </div>
    <?php else: ?>
        <div class="alert alert-danger" role="alert">
            <?php echo e('User not found!'); ?>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
