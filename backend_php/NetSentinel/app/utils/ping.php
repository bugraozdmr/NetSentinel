<?php

if ($argc < 2) {
    echo "0\n";
    exit;
}

$ip = escapeshellarg($argv[1]);
$cmd = "ping -c 1 -W 1 {$ip} 2>&1";
$output = shell_exec($cmd);

if (strpos($output, '1 packets received') !== false || strpos($output, '1 received') !== false) {
    echo "1\n";
} else {
    echo "0\n";
}
