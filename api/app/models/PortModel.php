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
            if (!isset($data['server_id'])) {
                throw new Exception("Server ID is required.");
            }

            $ports = [];

            // Eğer doğrudan port numaraları verilmişse (ör: [22, 21])
            if (isset($data['ports']) && is_array($data['ports']) && is_int($data['ports'][0])) {
                $ports = array_map(fn($p) => [
                    'port_number' => $p,
                    'is_open' => 0
                ], $data['ports']);
            }
            // Eğer ['port_number' => 22] gibi yapılar gelmişse
            elseif (isset($data['ports']) && is_array($data['ports'])) {
                $ports = $data['ports'];
            }
            // Tek bir port numarası gelmişse
            elseif (isset($data['port_number'])) {
                $ports = [[
                    'port_number' => $data['port_number'],
                    'is_open' => $data['is_open'] ?? 0
                ]];
            } else {
                throw new Exception("Port data is missing or invalid.");
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
            return ["error" => "Database error: Something went wrong -- " . $e->getMessage()];
        } catch (Exception $e) {
            error_log("insertPort error: " . $e->getMessage());
            http_response_code(400);
            return ["error" => $e->getMessage()];
        }
    }



    public function deletePorts($data)
    {
        try {
            $ports = [];

            if (isset($data['ports']) && is_array($data['ports'])) {
                $ports = $data['ports'];
            } else if (isset($data['portId'])) {
                $ports = [$data['portId']];
            } else {
                throw new Exception("Port IDs are missing.");
            }

            $stmt = $this->pdo->prepare("DELETE FROM server_ports WHERE id = :id");

            foreach ($ports as $portId) {
                $stmt->execute(['id' => $portId]);
            }

            return ["message" => "Port(s) deleted successfully"];
        } catch (PDOException $e) {
            error_log("deletePorts error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Database error: Something went wrong"];
        } catch (Exception $e) {
            error_log("deletePorts error: " . $e->getMessage());
            http_response_code(400);
            return ["error" => $e->getMessage()];
        }
    }

    public function deletePortsByServerId($data)
    {
        try {
            if (
                !isset($data['serverId']) || !is_numeric($data['serverId']) ||
                !isset($data['ports']) || !is_array($data['ports']) || empty($data['ports'])
            ) {
                throw new Exception("Invalid server ID or ports array.");
            }

            $serverId = (int)$data['serverId'];
            $ports = $data['ports'];

            $placeholders = implode(',', array_fill(0, count($ports), '?'));

            $sql = "DELETE FROM server_ports WHERE server_id = ? AND port_number IN ($placeholders)";
            $stmt = $this->pdo->prepare($sql);

            $stmt->execute(array_merge([$serverId], $ports));

            return ["message" => "Specified ports deleted successfully"];
        } catch (PDOException $e) {
            error_log("deletePortsByServerId error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Database error: Something went wrong"];
        } catch (Exception $e) {
            error_log("deletePortsByServerId error: " . $e->getMessage());
            http_response_code(400);
            return ["error" => $e->getMessage()];
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
