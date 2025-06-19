<?php
require_once __DIR__ . '/../models/ServerModel.php';
require_once __DIR__ . '/PortService.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/PingService.php';
require_once __DIR__ . '/StatusProcessor.php';
require_once __DIR__ . '/PortScanner.php';
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
    private $pingService;
    private $statusProcessor;
    private $portScanner;
    private $logger;

    public function __construct($pdo)
    {
        $this->serverModel = new ServerModel($pdo);
        $this->portService = new PortService($pdo);
        $this->notificationService = new NotificationService($pdo);
        $this->pingService = new PingService();
        $this->statusProcessor = new StatusProcessor($this->serverModel, $this->notificationService);
        $this->portScanner = new PortScanner($this->portService);
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

    public function getServersWithPagination($page = 1, $limit = 100, $filters = [])
    {
        $result = $this->serverModel->getServersWithPagination($page, $limit, $filters);
        
        // Add ports to each server
        foreach ($result['servers'] as &$server) {
            $ports = $this->portService->getPortsByServer((int)$server['id']);
            $server['ports'] = $ports;
        }

        $this->logger->info("Retrieved servers with pagination", [
            'page' => $page,
            'limit' => $limit,
            'count' => count($result['servers']),
            'total' => $result['pagination']['total']
        ]);
        
        return $result;
    }

    public function getServerStats()
    {
        $stats = $this->serverModel->getServerStats();
        
        $this->logger->info("Retrieved server statistics", $stats);
        
        return $stats;
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

    /**
     * Optimized server status checker - main logic
     */
    public function checkAllStatuses()
    {
        try {
            $this->logger->info("Starting optimized server status check");
            
            // 1. Get all servers with their ports
            $servers = $this->getServersWithStatus();
            
            if (empty($servers)) {
                $this->logger->info("No servers to check");
                return;
            }

            // 2. Ping all servers in parallel
            $pingResults = $this->pingService->pingServers($servers);
            
            // 3. Process results and update database
            $serverCategories = $this->statusProcessor->processStatusResults($servers, $pingResults);
            
            // 4. Scan ports for active servers
            $this->portScanner->scanActiveServers($serverCategories['active']);
            
            // 5. Mark ports as closed for inactive servers
            $this->portScanner->markInactiveServerPorts($serverCategories['inactive']);
            
            $this->logger->info("Server status check completed", [
                'total_servers' => count($servers),
                'active_servers' => count($serverCategories['active']),
                'inactive_servers' => count($serverCategories['inactive'])
            ]);
            
        } catch (Exception $e) {
            $this->logger->error("Error in checkAllStatuses: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
}
