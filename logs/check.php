<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// Lấy toàn bộ keys hiện có
$keys = $redis->keys('*');

echo "<h3>Các keys trong Redis:</h3><ul>";
foreach ($keys as $key) {
    echo "<li>$key</li>";
}
echo "</ul>";

// Lấy chi tiết hoạt động user 1
$activities = $redis->lRange("user:1:activity", 0, -1);
echo "<h3>Hoạt động của user 1:</h3><ul>";
foreach ($activities as $a) {
    echo "<li>$a</li>";
}
echo "</ul>";
