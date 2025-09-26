<?php
include 'track_activity.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'models/UserModel.php';
require_once __DIR__ . '/helpers/csrf.php';

$userModel = new UserModel();
$user = null;

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
            // Nếu để trống password thì giữ mật khẩu cũ
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
        $_SESSION['error'] = "Error: " . $e->getMessage();
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

    <h2><?php echo $user ? "Edit User" : "Add New User"; ?></h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrf_field() ?>
        <?php if ($user): ?>
            <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="name" required
                   value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>"
                   class="form-control">
        </div>

        <div class="form-group">
            <label>Fullname</label>
            <input type="text" name="fullname"
                   value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>"
                   class="form-control">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email"
                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                   class="form-control">    
        </div>

        <div class="form-group">
            <label>Password <?php echo $user ? "(leave blank to keep old)" : ""; ?></label>
            <input type="password" name="password"
                   class="form-control" <?php echo $user ? "" : "required"; ?>>
        </div>

        <button type="submit" class="btn btn-primary">
            <?php echo $user ? "Update" : "Create"; ?>
        </button>
    </form>
</div>
</body>
</html>
