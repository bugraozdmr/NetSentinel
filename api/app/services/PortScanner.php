<?php

require_once __DIR__ . '/PortService.php';
require_once __DIR__ . '/../utils/config.php';

use App\Core\Logger;

class PortScanner
{
    private $logger;
    private $portService;
    private $portScanScript;
    private $phpPath;

    public function __construct($portService)
    {
        $this->logger = Logger::getInstance();
        $this->portService = $portService;
        $this->portScanScript = realpath(__DIR__ . '/../utils/portScan.php');
        $this->phpPath = Config::getPhpPath();
    }

    /**
     * Scan ports for active servers
     */
    public function scanActiveServers(array $activeServers): void
    {
        if (empty($activeServers)) {
            $this->logger->debug("No active servers to scan ports for");
            return;
        }

        $this->logger->info("Starting port scan for active servers", ['count' => count($activeServers)]);
        
        $processes = [];
        $pipesList = [];
        $portMap = []; // Map process key to port info

        // Start all port scan processes
        foreach ($activeServers as $server) {
            if (!isset($server['ports']) || empty($server['ports'])) {
                continue;
            }
            
            foreach ($server['ports'] as $port) {
                if (!isset($port['id']) || !isset($port['port_number'])) {
                    continue;
                }
                
                $cmd = escapeshellcmd($this->phpPath) . ' ' . escapeshellarg($this->portScanScript) . ' ' . 
                       escapeshellarg($server['ip']) . ' ' . escapeshellarg($port['port_number']);

                $pipes = [];
                $proc = proc_open($cmd, [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w']
                ], $pipes);

                if (is_resource($proc)) {
                    $processKey = $port['id'];
                    $processes[$processKey] = $proc;
                    $pipesList[$processKey] = $pipes;
                    $portMap[$processKey] = [
                        'port_id' => $port['id'],
                        'server_ip' => $server['ip'],
                        'port_number' => $port['port_number']
                    ];
                }
            }
        }

        // Process results
        $this->processPortScanResults($processes, $pipesList, $portMap);
        
        $this->logger->info("Port scan completed", ['ports_scanned' => count($processes)]);
    }

    /**
     * Mark ports as closed for inactive servers
     */
    public function markInactiveServerPorts(array $inactiveServers): void
    {
        if (empty($inactiveServers)) {
            $this->logger->debug("No inactive servers to mark ports closed for");
            return;
        }

        $this->logger->info("Marking ports closed for inactive servers", ['count' => count($inactiveServers)]);
        
        $closedPorts = 0;
        
        foreach ($inactiveServers as $server) {
            if (!isset($server['ports']) || empty($server['ports'])) {
                continue;
            }
            
            foreach ($server['ports'] as $port) {
                if (!isset($port['id'])) {
                    continue;
                }
                
                try {
                    $this->portService->updatePortStatus($port['id'], 0);
                    $closedPorts++;
                    $this->logger->debug("Port marked as closed", ['port_id' => $port['id']]);
                } catch (Exception $e) {
                    $this->logger->error("Failed to mark port as closed", [
                        'port_id' => $port['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        $this->logger->info("Finished marking ports closed", ['ports_closed' => $closedPorts]);
    }

    /**
     * Process port scan results
     */
    private function processPortScanResults(array $processes, array $pipesList, array $portMap): void
    {
        foreach ($processes as $processKey => $proc) {
            $pipes = $pipesList[$processKey] ?? null;
            if (!$pipes) {
                continue;
            }

            $portInfo = $portMap[$processKey] ?? null;
            if (!$portInfo) {
                continue;
            }

            $output = stream_get_contents($pipes[1]);
            $errorOutput = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);

            $isOpen = (trim($output) === 'open') ? 1 : 0;

            if ($errorOutput) {
                $this->logger->error("Port scan error", [
                    'port_id' => $portInfo['port_id'],
                    'server_ip' => $portInfo['server_ip'],
                    'port_number' => $portInfo['port_number'],
                    'error' => $errorOutput
                ]);
            }

            try {
                $this->portService->updatePortStatus($portInfo['port_id'], $isOpen);
                $this->logger->debug("Port status updated", [
                    'port_id' => $portInfo['port_id'],
                    'server_ip' => $portInfo['server_ip'],
                    'port_number' => $portInfo['port_number'],
                    'status' => $isOpen ? 'open' : 'closed'
                ]);
            } catch (Exception $e) {
                $this->logger->error("Failed to update port status", [
                    'port_id' => $portInfo['port_id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
} 