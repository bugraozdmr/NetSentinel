<?php

if ($argc < 3) {
    echo "Usage: php port_scan.php <ip> <port>\n";
    exit(1);
}

$ip = $argv[1];
$port = (int)$argv[2];
$timeout = 2;

$connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);

if (is_resource($connection)) {
    echo "open";
    fclose($connection);
    exit(0);
} else {
    echo "closed";
    exit(1);
}
