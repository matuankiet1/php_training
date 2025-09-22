<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// Lấy toàn bộ log
$logs = $redis->lrange("user:activities", 0, -1);

foreach ($logs as $log) {
    $entry = json_decode($log, true);
    echo $entry['ts'] . " | " . $entry['ip'] . " | " . $entry['event'] . "<br>";
}
