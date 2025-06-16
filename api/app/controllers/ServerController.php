<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ServerService.php';
require_once __DIR__ . '/../validators/ServerValidator.php';


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

    public function getServer($id)
    {
        $server = $this->serverService->getServerByIdWithStatus($id);

        if (!$server || isset($server['error'])) {
            http_response_code(404);
            echo json_encode([
                "message" => $server['error'] ?? "Sunucu bulunamadı"
            ]);
            return;
        }

        echo json_encode([
            "server" => $server
        ]);
    }


    public function addServer($data)
    {
        $errors = ServerValidator::validateInsert($data);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(["errors" => $errors]);
            return;
        }

        echo json_encode($this->serverService->addServer($data));
    }

    public function editServer($id, $data)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Server ID is required"]);
            return;
        }

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid data"]);
            return;
        }

        $errors = ServerValidator::validateUpdate($data);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(["errors" => $errors]);
            return;
        }

        $response = $this->serverService->editServer((int)$id, $data);

        if (isset($response['error'])) {
            http_response_code(400);
        }
        echo json_encode($response);
    }


    public function deleteServer($serverId)
    {
        if (!$serverId) {
            http_response_code(400);
            echo json_encode(["error" => "Server ID is required"]);
            return;
        }
        echo json_encode($this->serverService->deleteServer($serverId));
    }


    public function checkAll(): void
    {
        $this->serverService->checkAllStatuses();

        echo json_encode(['message' => 'Tüm sunucular kontrol edildi.']);
    }
}
