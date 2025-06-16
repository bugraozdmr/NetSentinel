<?php

function pingServer(string $ip, int $timeoutSeconds = 2): string
{
    $output = shell_exec("ping -c 2 -W {$timeoutSeconds} {$ip} 2>&1");

    $status = 0;
    $avgMs = null;

    if (preg_match('/(\d+)\s+packets transmitted.*?(\d+)\s+packets received/', $output, $matches)) {
        $sent = (int)$matches[1];
        $received = (int)$matches[2];

        if ($received > 0) {
            $status = 1;

            if (preg_match('/rtt min\/avg\/max\/mdev = [\d\.]+\/([\d\.]+)\//', $output, $rttMatch)) {
                $avgMs = number_format((float)$rttMatch[1], 2, '.', '');
            }
        }
    }

    return json_encode([
        'status' => $status,
        'avg_ms' => $avgMs !== null ? (float)$avgMs : null,
    ]);
}
