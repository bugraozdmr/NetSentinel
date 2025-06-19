<?php
/**
 * Cron Job Check Runner
 * 
 * 
 * This script runs once and exits - designed for cron jobs
 */

set_time_limit(0);
date_default_timezone_set("Europe/Istanbul");

require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

echo "[" . date("Y-m-d H:i:s") . "] NetSentinel Cron Check Runner started...\n";
echo "[" . date("Y-m-d H:i:s") . "] API URL: http://localhost/netsentinel/api/check\n\n";

$startTime = microtime(true);

$url = "http://localhost/netsentinel/api/check";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 second timeout
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$executionTime = round((microtime(true) - $startTime) * 1000, 2);

if (curl_errno($ch)) {
    echo "[" . date("Y-m-d H:i:s") . "] ERROR: " . curl_error($ch) . "\n";
    exit(1);
} else {
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        $serverCount = isset($result['servers_checked']) ? $result['servers_checked'] : 'unknown';
        echo "[" . date("Y-m-d H:i:s") . "] SUCCESS: Checked {$serverCount} servers in {$executionTime}ms\n";
        echo "[" . date("Y-m-d H:i:s") . "] Cron check completed successfully.\n";
        exit(0);
    } else {
        echo "[" . date("Y-m-d H:i:s") . "] ERROR: HTTP {$httpCode} - {$response}\n";
        exit(1);
    }
}

curl_close($ch); 