<?php
include 'track_activity.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'models/UserModel.php';
require_once __DIR__ . '/helpers/csrf.php';

$userModel = new UserModel();
$user = null;

// Helper escape
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Lấy ID an toàn từ GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $result = $userModel->findUserById($id);
    $user = $result ?: null;
}

// Xử lý submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die("Forbidden - Invalid CSRF token");
    }

    $data = [
        'id'       => $_POST['id'] ?? null,
        'name'     => trim($_POST['name'] ?? ''),
        'fullname' => trim($_POST['fullname'] ?? ''),   
        'email'    => trim($_POST['email'] ?? ''),
        'password' => trim($_POST['password'] ?? ''),
    ];

    try {
        if (!empty($data['id'])) {
            if (empty($data['password'])) {
                unset($data['password']);
            }
            $userModel->updateUser($data);
            $_SESSION['success'] = "User updated successfully.";
        } else {
            $userModel->insertUser($data);
            $_SESSION['success'] = "User created successfully.";
        }

        header("Location: list_users.php");
        exit;
    } catch (Exception $e) {
        // Không nên hiển thị message raw từ Exception
        error_log("form_user.php error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while saving user.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Form</title>
    <?php include 'views/meta.php'; ?>
</head>
<body>
<?php include 'views/header.php'; ?>
<div class="container">

    <h2><?= $user ? "Edit User" : "Add New User" ?></h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= e($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrf_field() ?>
        <?php if ($user): ?>
            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="name" required
                   value="<?= e($user['name'] ?? '') ?>"
                   class="form-control">
        </div>

        <div class="form-group">
            <label>Fullname</label>
            <input type="text" name="fullname"
                   value="<?= e($user['fullname'] ?? '') ?>"
                   class="form-control">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?= e($user['email'] ?? '') ?>"
                   class="form-control">    
        </div>

        <div class="form-group">
            <label>Password <?= $user ? "(leave blank to keep old)" : "" ?></label>
            <input type="password" name="password"
                   class="form-control" <?= $user ? "" : "required" ?>>
        </div>

        <button type="submit" class="btn btn-primary">
            <?= $user ? "Update" : "Create" ?>
        </button>
    </form>
</div>
</body>
</html>
