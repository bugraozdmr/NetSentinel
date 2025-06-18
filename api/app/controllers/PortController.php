<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/PortService.php';
require_once __DIR__ . '/../validators/PortValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\DatabaseException;

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
            throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
        }

        $ports = $this->portService->getPortsByServer((int)$serverId);

        if (isset($ports['error'])) {
            throw new DatabaseException("Failed to fetch ports", ['details' => $ports['error']]);
        }

        if (isset($ports['not_found'])) {
            throw new NotFoundException("No ports found for the given server ID", ['server_id' => $serverId]);
        }

        echo json_encode(["ports" => $ports]);
    }

    public function addPort($data)
    {
        if (empty($data['server_id']) || !is_numeric($data['server_id'])) {
            throw new ValidationException("Server ID is required and must be numeric", ['field' => 'server_id']);
        }

        if (isset($data['ports']) && is_array($data['ports'])) {
            foreach ($data['ports'] as $index => $portValue) {
                $portData = is_array($portValue)
                    ? $portValue
                    : ['port_number' => $portValue];

                $portData['server_id'] = $data['server_id'];

                $errors = PortValidator::validateInsert($portData);
                if (!empty($errors)) {
                    throw new ValidationException("Validation failed for port index $index", $errors);
                }

                $data['ports'][$index] = $portData;
            }
        } else {
            $errors = PortValidator::validateInsert($data);
            if (!empty($errors)) {
                throw new ValidationException("Validation failed", $errors);
            }
        }

        $result = $this->portService->addPorts($data);

        if (isset($result['error'])) {
            throw new DatabaseException("Failed to add ports", ['details' => $result['error']]);
        }

        echo json_encode($result);
    }

    public function deletePort()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new ValidationException("Invalid input", ['input' => $input]);
        }

        $portIds = [];

        if (isset($input['id'])) {
            $portIds = [$input['id']];
        } elseif (isset($input['ports'])) {
            $portIds = $input['ports'];
        } else {
            throw new ValidationException("No port id(s) provided", ['input' => $input]);
        }

        if (!is_array($portIds)) {
            $portIds = [$portIds];
        }
        
        foreach ($portIds as $portId) {
            if (!is_numeric($portId)) {
                throw new ValidationException("Invalid port id(s)", ['port_ids' => $portIds]);
            }
        }

        $result = $this->portService->removePorts($portIds);

        if (isset($result['error'])) {
            throw new DatabaseException("Failed to delete ports", ['details' => $result['error']]);
        }

        echo json_encode($result);
    }

    public function editPortStatus()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new ValidationException("Invalid input", ['input' => $input]);
        }

        if (!isset($input['portId']) || !isset($input['is_open'])) {
            throw new ValidationException("Missing portId or is_open in input", ['input' => $input]);
        }

        $portId = $input['portId'];
        $isOpen = $input['is_open'];

        if (!is_numeric($portId) || !in_array($isOpen, [0, 1], true)) {
            throw new ValidationException("Invalid portId or is_open value", [
                'port_id' => $portId,
                'is_open' => $isOpen
            ]);
        }

        $result = $this->portService->updatePortStatus((int)$portId, (int)$isOpen);

        if (isset($result['error'])) {
            throw new DatabaseException("Failed to update port status", ['details' => $result['error']]);
        }

        echo json_encode($result);
    }
}
