<?php
// Nếu dùng Predis (qua Composer)
// require "vendor/autoload.php";
// $redis = new Predis\Client();

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// Nhận dữ liệu từ JS gửi lên
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $event = [
        'ts'   => date("Y-m-d H:i:s"),
        'ip'   => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'ua'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'path' => $data['path'] ?? '',
        'event'=> $data['event'] ?? ''
    ];

    // Lưu vào Redis dạng LIST (queue)
    $redis->rpush("user:activities", json_encode($event));
}

echo json_encode(["ok" => true]);
