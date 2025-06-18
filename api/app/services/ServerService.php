<?php
require_once __DIR__ . '/../models/ServerModel.php';
require_once __DIR__ . '/PortService.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/../utils/config.php';
require_once __DIR__ . '/../data/messages.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../core/Logger.php';

use App\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;
use App\Core\Logger;

Config::load();

class ServerService
{
    private $serverModel;
    private $portService;
    private $notificationService;
    private $logger;

    public function __construct($pdo)
    {
        $this->serverModel = new ServerModel($pdo);
        $this->portService = new PortService($pdo);
        $this->notificationService = new NotificationService($pdo);
        $this->logger = Logger::getInstance();
    }

    public function getServersWithStatus()
    {
        $servers = $this->serverModel->getAllServers();
        
        foreach ($servers as &$server) {
            $ports = $this->portService->getPortsByServer((int)$server['id']);
            $server['ports'] = $ports;
        }

        $this->logger->info("Retrieved servers with status", ['count' => count($servers)]);
        return $servers;
    }

    public function getServerByIdWithStatus($id)
    {
        $server = $this->serverModel->getServerById($id);

        if (!$server) {
            throw new NotFoundException("Server not found", ['id' => $id]);
        }

        $ports = $this->portService->getPortsByServer((int)$id);
        $server['ports'] = $ports;

        $this->logger->info("Retrieved server by ID", ['server_id' => $id, 'server_name' => $server['name']]);
        return $server;
    }

    public function addServer(array $data)
    {
        $result = $this->serverModel->insertServer($data);
        $serverId = $result['server_id'] ?? null;

        if (!empty($data['ports']) && is_array($data['ports']) && $serverId) {
            foreach ($data['ports'] as $key => $portVal) {
                $portData = is_array($portVal)
                    ? $portVal
                    : ['port_number' => $portVal];

                $portData['server_id'] = $serverId;

                $portInsert = $this->portService->addPorts(['ports' => [$portData], 'server_id' => $serverId]);

                if (isset($portInsert['error'])) {
                    $this->logger->warning("Server added but port insertion failed", [
                        'server_id' => $serverId,
                        'port_error' => $portInsert
                    ]);
                    return [
                        "message" => "Server added, but one or more ports failed.",
                        "server_id" => $serverId,
                        "port_error" => $portInsert
                    ];
                }
            }
        }

        $this->logger->info("Server added successfully", [
            'server_id' => $serverId,
            'server_name' => $data['name'] ?? 'Unknown',
            'ip' => $data['ip'] ?? 'Unknown'
        ]);

        return $result;
    }

    public function editServer(int $id, array $data)
    {
        $cs = $this->getServerByIdWithStatus($id);
        $updateResult = $this->serverModel->updateServer($id, $data);

        $portResults = [];

        if (isset($data['ports']) && is_array($data['ports'])) {
            $existingPorts = $this->portService->getPortsByServer($id);
            $existingPortNumbers = array_map(fn($p) => (int)$p['port_number'], $existingPorts);
            $newPorts = array_map('intval', $data['ports']);

            $removedPorts = array_diff($existingPortNumbers, $newPorts);
            $addedPorts = array_diff($newPorts, $existingPortNumbers);

            if (!empty($addedPorts)) {
                $addResult = $this->portService->addPorts([
                    'server_id' => $id,
                    'ports' => array_values($addedPorts)
                ]);

                if (isset($addResult['error'])) {
                    $portResults['add_error'] = $addResult['error'];
                    $this->logger->error("Failed to add ports to server", [
                        'server_id' => $id,
                        'ports' => $addedPorts,
                        'error' => $addResult['error']
                    ]);
                } else {
                    $portResults['added_ports'] = array_values($addedPorts);
                    $this->logger->info("Added ports to server", [
                        'server_id' => $id,
                        'ports' => $addedPorts
                    ]);
                }
            }

            if (!empty($removedPorts)) {
                $deleteResult = $this->portService->deletePortByServerAndNumber($id, array_values($removedPorts));

                if (isset($deleteResult['error'])) {
                    $portResults['delete_error'] = $deleteResult['error'];
                    $this->logger->error("Failed to delete ports from server", [
                        'server_id' => $id,
                        'ports' => $removedPorts,
                        'error' => $deleteResult['error']
                    ]);
                } else {
                    $portResults['removed_ports'] = array_values($removedPorts);
                    $this->logger->info("Removed ports from server", [
                        'server_id' => $id,
                        'ports' => $removedPorts
                    ]);
                }
            }
        }

        $this->logger->info("Server updated successfully", [
            'server_id' => $id,
            'server_name' => $data['name'] ?? 'Unknown'
        ]);

        return array_merge($updateResult, $portResults);
    }

    public function deleteServer($serverId)
    {
        $result = $this->serverModel->deleteServer($serverId);
        
        $this->logger->info("Server deleted", ['server_id' => $serverId]);
        
        return $result;
    }

    //* STATUS CHECKER
    public function checkAllStatuses()
    {
        try {
            $this->logger->info("Starting server status check");
            
            $servers = $this->getServersWithStatus();

            $processes = [];
            $pipesList = [];

            $pingScript = realpath(__DIR__ . '/../utils/ping.php');

            $activeServers = [];
            $inactiveServers = [];

            foreach ($servers as $index => $server) {
                $pipes = [];
                $cmd = escapeshellcmd(Config::getPhpPath()) . ' ' . escapeshellarg($pingScript) . ' ' . escapeshellarg($server['ip']);

                $processes[$index] = proc_open(
                    $cmd,
                    [
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w'],
                    ],
                    $pipes
                );

                if (is_resource($processes[$index])) {
                    $pipesList[$index] = $pipes;
                } else {
                    $pipesList[$index] = null;
                }
            }

            foreach ($processes as $index => $proc) {
                $pipes = $pipesList[$index];
                if (!$pipes) continue;

                $output = stream_get_contents($pipes[1]);
                $errorOutput = stream_get_contents($pipes[2]);

                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($proc);

                if (trim($errorOutput)) {
                    file_put_contents(__DIR__ . '/../../ping_errors.log', "Server #{$index}: $errorOutput\n", FILE_APPEND);
                    $this->logger->error("Ping error for server", [
                        'server_index' => $index,
                        'error' => $errorOutput
                    ]);
                }

                $server = $servers[$index];
                $id = $server['id'];
                $location = $server['location'];

                $pingResult = json_decode(trim($output), true);

                $status = 0;
                $avgMs = null;

                if (is_array($pingResult)) {
                    $status = isset($pingResult['status']) ? (int)$pingResult['status'] : 0;
                    $avgMs = isset($pingResult['avg_ms']) ? $pingResult['avg_ms'] : null;
                }

                // Log server check
                $this->logger->logServerCheck($server['name'], $server['ip'], $status, $avgMs);

                $lastChecks = json_decode($server['last_checks'], true);
                if (!is_array($lastChecks)) {
                    $lastChecks = [];
                }

                // Önceki durumu al
                $previousStatus = null;
                if (count($lastChecks) > 0) {
                    $previousStatus = $lastChecks[count($lastChecks) - 1]['status'];
                }

                // Durum değiştiyse bildirim oluştur
                if ($previousStatus !== null && $previousStatus !== $status) {
                    $serverName = "{$server['name']} (IP: {$server['ip']}})";
                    if ($status === 1) {
                        $active_messages = getActiveMessages();
                        $msg = $active_messages[array_rand($active_messages)];
                    } else {
                        $passive_messages = getPassiveMessages();
                        $msg = $passive_messages[array_rand($passive_messages)];
                    }
                    $message = "Sunucu {$serverName} {$msg}.";

                    $this->notificationService->addNotification([
                        "server_id" => $server['id'],
                        "message" => $message,
                    ]);

                    $this->logger->notice("Server status changed", [
                        'server_name' => $server['name'],
                        'server_ip' => $server['ip'],
                        'previous_status' => $previousStatus,
                        'new_status' => $status,
                        'message' => $message
                    ]);
                }

                $currentTime = date('Y-m-d H:i:s');

                $lastChecks[] = [
                    'time' => $currentTime,
                    'status' => $status,
                    'avg_ms' => $avgMs !== null ? number_format($avgMs, 2, '.', '') : null
                ];

                if (count($lastChecks) > 10) {
                    array_shift($lastChecks);
                }

                $this->serverModel->updateStatus($id, $status, json_encode($lastChecks), $location);

                if ($status === 1) {
                    $activeServers[] = $server;
                } else {
                    $inactiveServers[] = $server;
                }
            }

            $this->scanPorts($activeServers);
            $this->markPortsClosed($inactiveServers);
            
            $this->logger->info("Server status check completed", [
                'total_servers' => count($servers),
                'active_servers' => count($activeServers),
                'inactive_servers' => count($inactiveServers)
            ]);
            
        } catch (Exception $e) {
            $this->logger->error("Error in checkAllStatuses: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e; // Re-throw the exception so it can be handled by the exception handler
        }
    }

    // Port tarama fonksiyonu
    public function scanPorts(array $activeServers)
    {
        if (empty($activeServers)) {
            $this->logger->debug("No active servers to scan ports for");
            return;
        }

        $this->logger->info("Starting port scan for active servers", ['count' => count($activeServers)]);
        
        $processes = [];
        $pipesList = [];

        $portScanScript = realpath(__DIR__ . '/../utils/portScan.php');

        foreach ($activeServers as $server) {
            if (!isset($server['ports']) || empty($server['ports'])) {
                continue; // Skip servers without ports
            }
            
            foreach ($server['ports'] as $port) {
                if (!isset($port['id']) || !isset($port['port_number'])) {
                    continue; // Skip invalid port data
                }
                
                $cmd = escapeshellcmd(Config::getPhpPath()) . ' ' . escapeshellarg($portScanScript) . ' ' . escapeshellarg($server['ip']) . ' ' . escapeshellarg($port['port_number']);

                $pipes = [];
                $proc = proc_open(
                    $cmd,
                    [
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w']
                    ],
                    $pipes
                );

                if (is_resource($proc)) {
                    $processes[$port['id']] = $proc;
                    $pipesList[$port['id']] = $pipes;
                }
            }
        }

        foreach ($processes as $portId => $proc) {
            $pipes = $pipesList[$portId];

            $output = stream_get_contents($pipes[1]);
            $errorOutput = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            proc_close($proc);

            $isOpen = (trim($output) === 'open') ? 1 : 0;

            if ($errorOutput) {
                $this->logger->error("Port scan error", [
                    'port_id' => $portId,
                    'error' => $errorOutput
                ]);
            }

            try {
                $this->portService->updatePortStatus($portId, $isOpen);
                $this->logger->debug("Port status updated", [
                    'port_id' => $portId,
                    'status' => $isOpen ? 'open' : 'closed'
                ]);
            } catch (Exception $e) {
                $this->logger->error("Failed to update port status", [
                    'port_id' => $portId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->logger->info("Port scan completed", ['ports_scanned' => count($processes)]);
    }

    public function markPortsClosed(array $inactiveServers)
    {
        if (empty($inactiveServers)) {
            $this->logger->debug("No inactive servers to mark ports closed for");
            return;
        }

        $this->logger->info("Marking ports closed for inactive servers", ['count' => count($inactiveServers)]);
        
        foreach ($inactiveServers as $server) {
            if (!isset($server['ports']) || empty($server['ports'])) {
                continue; // Skip servers without ports
            }
            
            foreach ($server['ports'] as $port) {
                if (!isset($port['id'])) {
                    continue; // Skip invalid port data
                }
                
                $portId = $port['id'];
                try {
                    $this->portService->updatePortStatus($portId, 0);
                    $this->logger->debug("Port marked as closed", ['port_id' => $portId]);
                } catch (Exception $e) {
                    $this->logger->error("Failed to mark port as closed", [
                        'port_id' => $portId,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        $this->logger->info("Finished marking ports closed for inactive servers");
    }
}
