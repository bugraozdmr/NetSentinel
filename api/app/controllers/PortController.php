<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/PortService.php';
require_once __DIR__ . '/../validators/PortValidator.php';

header('Content-Type: application/json');

class PortController
{
    private $portService;

    public function __construct($pdo)
    {
        $this->portService = new PortService($pdo);
    }

    public function getPorts($serverId)
    {
        if (empty($serverId) || !is_numeric($serverId)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid server ID"]);
            return;
        }

        $ports = $this->portService->getPortsByServer((int)$serverId);

        if (isset($ports['error'])) {
            http_response_code(500);
            echo json_encode($ports);
            return;
        }

        if (isset($ports['not_found'])) {
            http_response_code(404);
            echo json_encode(["error" => "No ports found for the given server ID."]);
            return;
        }

        http_response_code(200);
        echo json_encode(["ports" => $ports]);
    }


    public function addPort($data)
    {
        if (empty($data['server_id']) || !is_numeric($data['server_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Server ID is required and must be numeric."]);
            return;
        }

        if (isset($data['ports']) && is_array($data['ports'])) {
            foreach ($data['ports'] as $index => $portValue) {
                $portData = is_array($portValue)
                    ? $portValue
                    : ['port_number' => $portValue];

                $portData['server_id'] = $data['server_id'];

                $errors = PortValidator::validateInsert($portData);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode([
                        "error" => "Validation failed for port index $index",
                        "details" => $errors
                    ]);
                    return;
                }

                $data['ports'][$index] = $portData;
            }
        } else {
            $errors = PortValidator::validateInsert($data);
            if (!empty($errors)) {
                http_response_code(422);
                echo json_encode([
                    "error" => "Validation failed",
                    "details" => $errors
                ]);
                return;
            }
        }

        $result = $this->portService->addPorts($data);

        if (isset($result['error'])) {
            http_response_code(500);
            echo json_encode($result);
            return;
        }

        echo json_encode($result);
    }


    public function deletePort()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input."]);
            return;
        }

        $portIds = [];

        if (isset($input['id'])) {
            $portIds = [$input['id']];
        } elseif (isset($input['ports'])) {
            $portIds = $input['ports'];
        } else {
            http_response_code(400);
            echo json_encode(["error" => "No port id(s) provided."]);
            return;
        }

        if (!is_array($portIds)) {
            $portIds = [$portIds];
        }
        foreach ($portIds as $portId) {
            if (!is_numeric($portId)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid port id(s)."]);
                return;
            }
        }

        $result = $this->portService->removePorts($portIds);

        if (isset($result['error'])) {
            http_response_code(500);
            echo json_encode($result);
            return;
        }

        echo json_encode($result);
    }
}
