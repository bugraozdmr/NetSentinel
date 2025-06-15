<?php
require_once __DIR__ . '/../config/database.php';


class PortModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function insertPort($data)
    {
        try {
            $ports = [];

            if (isset($data['ports']) && is_array($data['ports'])) {
                $ports = $data['ports'];
            } else if (isset($data['port_number'])) {
                $ports = [
                    [
                        'port_number' => $data['port_number'],
                        'is_open' => $data['is_open'] ?? 0
                    ]
                ];
            } else {
                throw new Exception("Port data is missing.");
            }

            $stmt = $this->pdo->prepare("INSERT INTO server_ports (server_id, port_number, is_open) VALUES (:server_id, :port_number, :is_open)");

            foreach ($ports as $port) {
                $is_open = isset($port['is_open']) ? ($port['is_open'] ? 1 : 0) : 0;

                $stmt->execute([
                    'server_id' => $data['server_id'],
                    'port_number' => $port['port_number'],
                    'is_open' => $is_open,
                ]);
            }

            return ["message" => "Port(s) added successfully"];
        } catch (PDOException $e) {
            error_log("insertPort error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Database error: Something went wrong" . '---' . $e->getMessage()];
        } catch (Exception $e) {
            error_log("insertPort error: " . $e->getMessage());
            http_response_code(400);
            return ["error" => $e->getMessage()];
        }
    }


    public function deletePort($portId)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM server_ports WHERE id = :id");
            $stmt->execute(['id' => $portId]);
            return ["message" => "Port deleted successfully"];
        } catch (PDOException $e) {
            error_log("deletePort error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Database error: Something went wrong"];
        }
    }

    public function getPortsByServer(int $serverId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM server_ports WHERE server_id = :server_id ORDER BY port_number ASC");
            $stmt->execute(['server_id' => $serverId]);
            $ports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $ports;
        } catch (PDOException $e) {
            error_log("getPortsByServer error: " . $e->getMessage());
            return ["error" => "Database error: Unable to fetch ports"];
        }
    }
}
