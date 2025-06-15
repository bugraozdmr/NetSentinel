<?php
require_once __DIR__ . '/../models/PortModel.php';
// require_once __DIR__ . '/../utils/config.php';

class PortService
{
    private $portModel;

    public function __construct($pdo)
    {
        $this->portModel = new PortModel($pdo);
    }

    /**
     * One or more ports can be added
     * $data: [
     *   server_id => int,
     *   port_number => int,           // One port
     *   is_open => bool,              // Optional
     *   ports => [                    // Multiple Ports
     *      ['port_number'=>int, 'is_open'=>bool],
     *      ...
     *   ]
     * ]
     */
    public function addPorts(array $data)
    {
        return $this->portModel->insertPort($data);
    }

    /**
     * Delete Port
     * $portId: int
     */
    public function removePort(int $portId)
    {
        return $this->portModel->deletePort($portId);
    }

    /**
     * List ports based on server_id
     */
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
}
