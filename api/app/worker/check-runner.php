<?php
/**
 * Usage : php app/worker/check-runner.php
 * Run in background : nohup phpphp app/worker/check-runner.php > check.log 2>&1 &
 * 
 * To Stop:
 * ps aux | grep check-runner.php
 * kill PID
 */

set_time_limit(0);
date_default_timezone_set("Europe/Istanbul");

require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();
$interval = $_ENV['WORKER_INTERVAL'] ?? 30;

echo "Control started...\n";

while (true) {
    $url = "http://localhost/netsentinel/api/check";

    echo "[" . date("Y-m-d H:i:s") . "] request sent: $url\n";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "Error : " . curl_error($ch) . "\n";
    } else {
        echo "Response : $response\n";
    }

    curl_close($ch);

    sleep($interval);
}
