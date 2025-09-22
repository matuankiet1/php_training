<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);

    $userId = $_SESSION['user_id'] ?? 1;

    // Thời gian chi tiết đến mili giây
    $micro = sprintf("%03d", (microtime(true) * 1000) % 1000);
    $time = date("Y-m-d H:i:s") . "." . $micro;

    // Tạo chuỗi hoạt động có thêm ID duy nhất
    $activity = sprintf(
        "Truy cập %s lúc %s [ID:%s]",
        $_SERVER['REQUEST_URI'],
        $time,
        uniqid()
    );

    $redis->lPush("user:{$userId}:activity", $activity);
    $redis->lTrim("user:{$userId}:activity", 0, 19);

    // test hiển thị ra luôn cho chắc
    echo "Đã lưu: " . htmlspecialchars($activity);

} catch (Exception $e) {
    echo "Lỗi Redis: " . $e->getMessage();
}
