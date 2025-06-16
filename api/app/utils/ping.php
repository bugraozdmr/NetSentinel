<?php

if ($argc < 2) {
    echo json_encode(['status' => 0, 'avg_ms' => null]);
    exit;
}

$ip = escapeshellarg($argv[1]);
$cmd = "ping -c 4 -W 1 {$ip} 2>&1";
$output = shell_exec($cmd);

$status = 0;
$avgMs = null;

if (strpos($output, 'packets received') !== false) {
    if (preg_match('/(\d+)\s+packets transmitted.*?(\d+)\s+packets received/', $output, $matches)) {
        $sent = (int)$matches[1];
        $received = (int)$matches[2];

        if ($received > 0) {
            $status = 1;

            if (preg_match('/round-trip.*?=\s*([\d\.]+)\/([\d\.]+)\/([\d\.]+)\/([\d\.]+)\s*ms/', $output, $pingMatches)) {
                $avgMs = (float)$pingMatches[2];
            }
        }
    }
}

echo json_encode(['status' => $status, 'avg_ms' => $avgMs]);
