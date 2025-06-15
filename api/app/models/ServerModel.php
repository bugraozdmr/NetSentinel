<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/pingServer.php';


class ServerModel
{

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllServers()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM servers");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("getAllServers error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Veritabanı hatası"];
        }
    }

    public function getServerById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM servers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $server = $stmt->fetch();

            if (!$server) {
                // Kayıt yoksa null dönebiliriz
                return null;
            }

            return $server;
        } catch (PDOException $e) {
            error_log("getServerById error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Veritabanı hatası"];
        }
    }


    public function insertServer($data)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO servers (ip, name, location,assigned_id, is_active, last_checks) VALUES (:ip, :name, :location, :assigned_id, :is_active, :last_checks)");

            $is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 0;
            $last_checks = $data['last_checks'] ?? '{}';

            $stmt->execute([
                'ip' => $data['ip'],
                'name' => $data['name'],
                'location' => $data['location'],
                'assigned_id' => $data['assigned_id'],
                'is_active' => $is_active,
                'last_checks' => $last_checks,
            ]);

            return ["message" => "Server added successfully"];
        } catch (PDOException $e) {
            error_log("insertServer error: " . $e->getMessage());
            http_response_code(500);
            // return ["error" => "Database error: " . $e->getMessage()];
            return ["error" => "Database error: Something went wrong "];
        }
    }

    public function updateServer(int $id, array $data)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE servers 
                SET ip = :ip, name = :name, assigned_id = :assigned_id , location = :location
                WHERE id = :id
            ");

            $stmt->execute([
                'ip' => $data['ip'],
                'name' => $data['name'],
                'assigned_id' => $data['assigned_id'],
                'location' => $data['location'],
                'id' => $id,
            ]);

            if ($stmt->rowCount() === 0) {
                // return ["error" => "No server found with the provided ID or data is the same."];
                return ["error" => "Something went wrong !"];
            }

            return ["message" => "Server updated successfully"];
        } catch (PDOException $e) {
            error_log("updateServer error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Database error"];
        }
    }


    public function deleteServer($serverId)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM servers WHERE id = :id");
            $stmt->execute(['id' => $serverId]);
            return ["message" => "Server deleted successfully"];
        } catch (PDOException $e) {
            error_log("deleteServer error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Veritabanı hatası"];
        }
    }


    public function getAllServersForStatus(): array
    {
        $stmt = $this->pdo->query("SELECT id, ip, last_checks, location FROM servers");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $id, int $isActive, string $lastChecks,  string $location): void
    {
        $stmt = $this->pdo->prepare("
        UPDATE servers
        SET is_active = ?, last_checks = ?, location = ?, last_check_at = NOW()
        WHERE id = ?
    ");
        $stmt->execute([$isActive, $lastChecks, $location, $id]);
    }
}
