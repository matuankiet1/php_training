<?php
include 'track_activity.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/helpers/csrf.php'; // đồng bộ (chưa dùng ở view)
require_once 'models/UserModel.php';

$userModel = new UserModel();

// Lấy ID an toàn
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user = null;
if ($id) {
    $result = $userModel->findUserById($id);
    $user = $result ? $result[0] : null;
}

// Lấy flash messages
$successMsg = $_SESSION['success'] ?? '';
$errorMsg   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <?php include 'views/meta.php'; ?>
</head>
<body>
<?php include 'views/header.php'; ?>

<div class="container">

    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($user): ?>
        <div class="alert alert-info" role="alert">
            User Profile
        </div>

        <div class="form-group">
            <label>Name: </label>
            <span><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="form-group">
            <label>Fullname: </label>
            <span><?php echo htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="form-group">
            <label>Email: </label>
            <span><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="form-group">
            <label>Type: </label>
            <span><?php echo htmlspecialchars($user['type'] ?? 'user', ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php else: ?>
        <div class="alert alert-danger" role="alert">
            User not found!
        </div>
    <?php endif; ?>

</div>
</body>
</html>
