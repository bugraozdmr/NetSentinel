<?php
require_once __DIR__ . '/../models/PortModel.php';
require_once __DIR__ . '/../utils/config.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';

use App\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;

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

        if (empty($ports)) {
            return [];
        }

        return $ports;
    }

    public function getPortById(int $portId)
    {
        return $this->portModel->getPortById($portId);
    }

    public function updatePortStatus(int $portId, int $isOpen)
    {
        $port = $this->portModel->getPortById($portId);
        if (!$port) {
            throw new NotFoundException("Port not found", ['port_id' => $portId]);
        }

        return $this->portModel->updatePortStatus($portId, $isOpen);
    }
}
