<?php
// view_activity.php
session_start();

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// Giả sử user_id lấy từ session (mặc định = 1 nếu chưa login)
$userId = $_SESSION['user_id'] ?? 1;

// Lấy danh sách hoạt động (20 hoạt động gần nhất)
$activities = $redis->lRange("user:{$userId}:activity", 0, 19);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hoạt động của người dùng</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        tr:nth-child(even) { background-color: #fafafa; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <h2>Hoạt động gần đây của user ID: <?= htmlspecialchars($userId) ?></h2>
    <table>
        <tr>
            <th>#</th>
            <th>Nội dung</th>
        </tr>
        <?php if (!empty($activities)): ?>
            <?php foreach ($activities as $index => $activity): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($activity, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="2">Chưa có hoạt động nào</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
