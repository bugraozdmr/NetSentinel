<?php
require_once __DIR__ . '/../models/ServerModel.php';
require_once __DIR__ . '/PortService.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/../utils/config.php';
require_once __DIR__ . '/../data/messages.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';

use App\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;

Config::load();

class ServerService
{
    private $serverModel;
    private $portService;
    private $notificationService;

    public function __construct($pdo)
    {
        $this->serverModel = new ServerModel($pdo);
        $this->portService = new PortService($pdo);
        $this->notificationService = new NotificationService($pdo);
    }

    public function getServersWithStatus()
    {
        $servers = $this->serverModel->getAllServers();
        
        foreach ($servers as &$server) {
            $ports = $this->portService->getPortsByServer((int)$server['id']);
            $server['ports'] = $ports;
        }

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
                    return [
                        "message" => "Server added, but one or more ports failed.",
                        "server_id" => $serverId,
                        "port_error" => $portInsert
                    ];
                }
            }
        }

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
                } else {
                    $portResults['added_ports'] = array_values($addedPorts);
                }
            }

            if (!empty($removedPorts)) {
                $deleteResult = $this->portService->deletePortByServerAndNumber($id, array_values($removedPorts));

                if (isset($deleteResult['error'])) {
                    $portResults['delete_error'] = $deleteResult['error'];
                } else {
                    $portResults['removed_ports'] = array_values($removedPorts);
                }
            }
        }

        return array_merge($updateResult, $portResults);
    }

    public function deleteServer($serverId)
    {
        return $this->serverModel->deleteServer($serverId);
    }

    //* STATUS CHECKER
    public function checkAllStatuses()
    {
        try {
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
            
        } catch (Exception $e) {
            error_log("Error in checkAllStatuses: " . $e->getMessage());
            throw $e; // Re-throw the exception so it can be handled by the exception handler
        }
    }

    // Port tarama fonksiyonu
    public function scanPorts(array $activeServers)
    {
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
                error_log("Port scan error on port $portId: $errorOutput");
            }

            try {
                $this->portService->updatePortStatus($portId, $isOpen);
            } catch (Exception $e) {
                error_log("Failed to update port status for port $portId: " . $e->getMessage());
            }
        }
    }

    public function markPortsClosed(array $inactiveServers)
    {
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
                } catch (Exception $e) {
                    error_log("Failed to mark port $portId as closed: " . $e->getMessage());
                }
            }
        }
    }
}
