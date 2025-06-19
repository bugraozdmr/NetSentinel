<?php
/**
 * Continuous Check Runner (Daemon Mode)
 * Usage : php app/worker/check-runner.php
 * Run in background : nohup php app/worker/check-runner.php > check.log 2>&1 &
 * 
 * To Stop:
 * ps aux | grep check-runner.php
 * kill PID
 * 
 * For cron jobs, use: cron-check-runner.php instead
 */

set_time_limit(0);
date_default_timezone_set("Europe/Istanbul");

require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// Get interval from environment or use default
$interval = $_ENV['WORKER_INTERVAL'] ?? 30;

echo "[" . date("Y-m-d H:i:s") . "] NetSentinel Continuous Status Checker started...\n";
echo "[" . date("Y-m-d H:i:s") . "] Interval: {$interval} seconds\n";
echo "[" . date("Y-m-d H:i:s") . "] API URL: http://localhost/netsentinel/api/check\n";
echo "[" . date("Y-m-d H:i:s") . "] Mode: Continuous (Daemon)\n\n";

$runCount = 0;
$errorCount = 0;

while (true) {
    $runCount++;
    $startTime = microtime(true);
    
    echo "[" . date("Y-m-d H:i:s") . "] Run #{$runCount} - Starting check...\n";

    $url = "http://localhost/netsentinel/api/check";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $executionTime = round((microtime(true) - $startTime) * 1000, 2);

    if (curl_errno($ch)) {
        $errorCount++;
        echo "[" . date("Y-m-d H:i:s") . "] ERROR #{$errorCount}: " . curl_error($ch) . "\n";
    } else {
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $serverCount = isset($result['servers_checked']) ? $result['servers_checked'] : 'unknown';
            echo "[" . date("Y-m-d H:i:s") . "] SUCCESS: Checked {$serverCount} servers in {$executionTime}ms\n";
        } else {
            $errorCount++;
            echo "[" . date("Y-m-d H:i:s") . "] ERROR #{$errorCount}: HTTP {$httpCode} - {$response}\n";
        }
    }

    curl_close($ch);

    echo "[" . date("Y-m-d H:i:s") . "] Next check in {$interval} seconds...\n\n";
    
    // Sleep for the specified interval
    sleep($interval);
}
