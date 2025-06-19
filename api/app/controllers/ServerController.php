<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ServerService.php';
require_once __DIR__ . '/../validators/ServerValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';

use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

header('Content-Type: application/json');

class ServerController
{
    private $serverService;

    public function __construct($pdo)
    {
        $this->serverService = new ServerService($pdo);
    }

    public function getServers()
    {
        echo json_encode(["servers" => $this->serverService->getServersWithStatus()]);
    }

    public function getServersWithPagination()
    {
        // Get query parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        
        // Validate parameters
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 500) $limit = 100; // Max 500 per page
        
        // Get filters from query parameters
        $filters = [];
        if (isset($_GET['status']) && in_array($_GET['status'], ['active', 'inactive'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['location'])) {
            $filters['location'] = $_GET['location'];
        }
        if (isset($_GET['panel'])) {
            $filters['panel'] = $_GET['panel'];
        }
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $filters['search'] = trim($_GET['search']);
        }

        $result = $this->serverService->getServersWithPagination($page, $limit, $filters);
        echo json_encode($result);
    }

    public function getServerStats()
    {
        $stats = $this->serverService->getServerStats();
        echo json_encode($stats);
    }

    public function getServer($id)
    {
        $server = $this->serverService->getServerByIdWithStatus($id);

        if (!$server || isset($server['error'])) {
            throw new NotFoundException("Server not found", ['id' => $id]);
        }

        echo json_encode([
            "server" => $server
        ]);
    }

    public function addServer($input)
    {
        $errors = ServerValidator::validateInsert($input);
        if (!empty($errors)) {
            throw new ValidationException("Validation failed", $errors);
        }

        echo json_encode($this->serverService->addServer($input));
    }

    public function editServer($id, $data)
    {
        if (!$id) {
            throw new ValidationException("Server ID is required", ['field' => 'id']);
        }

        if (!is_array($data)) {
            throw new ValidationException("Invalid data format", ['data' => $data]);
        }

        $errors = ServerValidator::validateUpdate($data);
        if (!empty($errors)) {
            throw new ValidationException("Validation failed", $errors);
        }

        $response = $this->serverService->editServer((int)$id, $data);

        if (isset($response['error'])) {
            throw new NotFoundException("Server not found", ['id' => $id]);
        }
        
        echo json_encode($response);
    }

    public function deleteServer($serverId)
    {
        if (!$serverId) {
            throw new ValidationException("Server ID is required", ['field' => 'server_id']);
        }
        
        echo json_encode($this->serverService->deleteServer($serverId));
    }

    public function checkAll(): void
    {
        $this->serverService->checkAllStatuses();
        echo json_encode(['message' => 'Tüm sunucular kontrol edildi.']);
    }
}
