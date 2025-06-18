<?php

require_once __DIR__ . '/../utils/config.php';

use App\Core\Logger;

class PingService
{
    private $logger;
    private $pingScript;
    private $phpPath;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->pingScript = realpath(__DIR__ . '/../utils/ping.php');
        $this->phpPath = Config::getPhpPath();
    }

    /**
     * Ping multiple servers in parallel
     */
    public function pingServers(array $servers): array
    {
        if (empty($servers)) {
            return [];
        }

        $this->logger->info("Starting parallel ping for servers", ['count' => count($servers)]);
        
        $processes = [];
        $pipesList = [];
        $results = [];

        // Start all ping processes
        foreach ($servers as $index => $server) {
            $cmd = escapeshellcmd($this->phpPath) . ' ' . escapeshellarg($this->pingScript) . ' ' . escapeshellarg($server['ip']);
            
            $pipes = [];
            $processes[$index] = proc_open($cmd, [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes);

            if (is_resource($processes[$index])) {
                $pipesList[$index] = $pipes;
            } else {
                $pipesList[$index] = null;
                $this->logger->error("Failed to start ping process", ['server_ip' => $server['ip']]);
            }
        }

        // Collect results
        foreach ($processes as $index => $proc) {
            $pipes = $pipesList[$index] ?? null;
            if (!$pipes) {
                $results[$index] = ['status' => 0, 'avg_ms' => null, 'error' => 'Process failed to start'];
                continue;
            }

            $output = stream_get_contents($pipes[1]);
            $errorOutput = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);

            $pingResult = $this->parsePingResult($output, $errorOutput);
            $results[$index] = $pingResult;

            if ($errorOutput) {
                $this->logger->error("Ping error for server", [
                    'server_index' => $index,
                    'server_ip' => $servers[$index]['ip'],
                    'error' => $errorOutput
                ]);
            }
        }

        $this->logger->info("Parallel ping completed", ['servers_pinged' => count($servers)]);
        return $results;
    }

    /**
     * Parse ping script output
     */
    private function parsePingResult(string $output, string $errorOutput): array
    {
        $pingResult = json_decode(trim($output), true);
        
        $status = 0;
        $avgMs = null;

        if (is_array($pingResult)) {
            $status = isset($pingResult['status']) ? (int)$pingResult['status'] : 0;
            $avgMs = isset($pingResult['avg_ms']) ? $pingResult['avg_ms'] : null;
        }

        return [
            'status' => $status,
            'avg_ms' => $avgMs,
            'error' => $errorOutput ?: null
        ];
    }
} 