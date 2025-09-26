<?php
include 'track_activity.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/helpers/csrf.php';
require_once 'models/UserModel.php';

$userModel = new UserModel();

// Lấy tham số tìm kiếm (nếu có)
$params = [];
if (!empty($_GET['keyword'])) {
    $params['keyword'] = trim($_GET['keyword']);
}
$users = $userModel->getUsers($params);

// Lấy flash messages
$successMsg = $_SESSION['success'] ?? '';
$errorMsg   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>List Users</title>
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

    <?php if (!empty($users)): ?>
        <div class="alert alert-info">List of users</div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Fullname</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="form_user.php?id=<?php echo urlencode($user['id']); ?>" title="Update">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                        <a href="view_user.php?id=<?php echo urlencode($user['id']); ?>" title="View">
                            <i class="fa fa-eye"></i>
                        </a>
                        <!-- Delete bằng POST + CSRF -->
                        <form action="delete.php" method="POST" style="display:inline;"
                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                            <button type="submit" style="border:none;background:none;color:red;cursor:pointer;" title="Delete">
                                <i class="fa fa-eraser"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-dark">No users found.</div>
    <?php endif; ?>

</div>
</body>
</html>
