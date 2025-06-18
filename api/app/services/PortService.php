<?php
require_once __DIR__ . '/../models/PortModel.php';
require_once __DIR__ . '/../utils/config.php';

class PortService
{
    private $portModel;

    public function __construct($pdo)
    {
        $this->portModel = new PortModel($pdo);
    }

    public function addPorts(array $data)
    {
        return $this->portModel->insertPort($data);
    }

    public function removePorts(array $portIds)
    {
        return $this->portModel->deletePorts(['ports' => $portIds]);
    }

    public function deletePortByServerAndNumber(int $serverId, array $ports)
    {
        return $this->portModel->deletePortsByServerId(['serverId' => $serverId, 'ports' => $ports]);
    }


    public function getPortsByServer(int $serverId)
    {
        $ports = $this->portModel->getPortsByServer($serverId);

        if (isset($ports['error'])) {
            return $ports;
        }

        if (empty($ports)) {
            return ["not_found" => true];
        }

        return $ports;
    }

    public function getPortById(int $portId)
    {
        return $this->portModel->getPortById($portId);
    }

    public function updatePortStatus(int $portId, int $isOpen)
    {
        try {
            $port = $this->portModel->getPortById($portId);
            if (!$port) {
                http_response_code(404);
                return ["error" => "Port not found with ID: $portId"];
            }

            $this->portModel->updatePortStatus($portId, $isOpen);
            return ["message" => "Port status updated successfully"];
        } catch (PDOException $e) {
            error_log("updatePortStatus error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Database error: " . $e->getMessage()];
        } catch (Exception $e) {
            error_log("updatePortStatus error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Unexpected error: " . $e->getMessage()];
        }
    }
}
