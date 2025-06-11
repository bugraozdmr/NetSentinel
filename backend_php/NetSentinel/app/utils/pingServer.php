<?php

function pingServer(string $ip, int $timeoutSeconds = 2): string
{
    $output = shell_exec("ping -c 4 -W {$timeoutSeconds} {$ip} 2>&1");

    if (preg_match('/(\d+)\s+packets transmitted.*?(\d+)\s+packets received/', $output, $matches)) {
        $sent = (int)$matches[1];
        $received = (int)$matches[2];

        if ($received > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    return 0;
}
