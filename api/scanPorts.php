<?php

function scanPorts(string $ip, array $ports, int $timeout = 2): array {
    $results = [];

    foreach ($ports as $port) {
        $connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);

        if (is_resource($connection)) {
            $results[] = [
                'port' => $port,
                'status' => 'open'
            ];
            fclose($connection);
        } else {
            $results[] = [
                'port' => $port,
                'status' => 'closed'
            ];
        }
    }

    return $results;
}

// Dummy değerlerle çalıştır
$ip = '192.168.253.5';
$ports = [80, 443];

$result = scanPorts($ip, $ports);

// JSON olarak çıktı
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
