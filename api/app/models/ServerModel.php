<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/pingServer.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';

use App\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;

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
            throw new DatabaseException("Failed to fetch servers", ['details' => $e->getMessage()]);
        }
    }

    public function getServerById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM servers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $server = $stmt->fetch();

            if (!$server) {
                throw new NotFoundException("Server not found", ['id' => $id]);
            }

            return $server;
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to fetch server", ['details' => $e->getMessage()]);
        }
    }


    public function insertServer($data)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO servers (ip, name, location, panel, is_active, last_checks) VALUES (:ip, :name, :location, :panel, :is_active, :last_checks)");

            $is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 0;
            $last_checks = $data['last_checks'] ?? '{}';
            $panel = $data['panel'] ?? 'Yok';

            $stmt->execute([
                'ip' => $data['ip'],
                'name' => $data['name'],
                'location' => $data['location'],
                'panel' => $panel,
                'is_active' => $is_active,
                'last_checks' => $last_checks,
            ]);

            $serverId = $this->pdo->lastInsertId();

            return [
                "message" => "Server added successfully",
                "server_id" => $serverId
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to insert server", ['details' => $e->getMessage()]);
        }
    }

    public function updateServer(int $id, array $data)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE servers 
                SET ip = :ip, 
                    name = :name, 
                    location = :location,
                    panel = :panel,
                    is_active = :is_active
                WHERE id = :id
            ");

            $stmt->execute([
                'ip' => $data['ip'],
                'name' => $data['name'],
                'location' => $data['location'],
                'panel' => $data['panel'] ?? 'Yok',
                'is_active' => isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 0,
                'id' => $id,
            ]);

            if ($stmt->rowCount() === 0) {
                throw new NotFoundException("Server not found", ['id' => $id]);
            }

            return ["message" => "Server updated successfully"];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to update server", ['details' => $e->getMessage()]);
        }
    }


    public function deleteServer($serverId)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM servers WHERE id = :id");
            $stmt->execute(['id' => $serverId]);
            
            if ($stmt->rowCount() === 0) {
                throw new NotFoundException("Server not found", ['id' => $serverId]);
            }
            
            return ["message" => "Server deleted successfully"];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to delete server", ['details' => $e->getMessage()]);
        }
    }


    public function getAllServersForStatus(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT id, ip, last_checks, location FROM servers");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to fetch servers for status", ['details' => $e->getMessage()]);
        }
    }

    public function updateStatus(int $id, int $isActive, string $lastChecks,  string $location): void
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE servers
                SET is_active = ?, last_checks = ?, location = ?, last_check_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$isActive, $lastChecks, $location, $id]);
            
            if ($stmt->rowCount() === 0) {
                throw new NotFoundException("Server not found", ['id' => $id]);
            }
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to update server status", ['details' => $e->getMessage()]);
        }
    }

    /**
     * Get servers with pagination and filtering
     */
    public function getServersWithPagination($page = 1, $limit = 100, $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;
            $whereConditions = [];
            $params = [];

            // Apply filters
            if (!empty($filters['status'])) {
                if ($filters['status'] === 'active') {
                    $whereConditions[] = "is_active = 1";
                } elseif ($filters['status'] === 'inactive') {
                    $whereConditions[] = "is_active = 0";
                }
            }

            if (!empty($filters['location']) && $filters['location'] !== 'all') {
                $whereConditions[] = "location = :location";
                $params['location'] = $filters['location'];
            }

            if (!empty($filters['panel']) && $filters['panel'] !== 'all') {
                $whereConditions[] = "panel = :panel";
                $params['panel'] = $filters['panel'];
            }

            if (!empty($filters['search'])) {
                $whereConditions[] = "(name LIKE :search OR ip LIKE :search OR location LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            $whereClause = '';
            if (!empty($whereConditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            }

            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM servers " . $whereClause;
            $countStmt = $this->pdo->prepare($countQuery);
            $countStmt->execute($params);
            $totalCount = $countStmt->fetch()['total'];

            // Get paginated results
            $query = "SELECT * FROM servers " . $whereClause . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'servers' => $servers,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$limit,
                    'total' => (int)$totalCount,
                    'total_pages' => ceil($totalCount / $limit),
                    'has_next' => $page < ceil($totalCount / $limit),
                    'has_prev' => $page > 1
                ]
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to fetch servers with pagination", ['details' => $e->getMessage()]);
        }
    }

    /**
     * Get total count for summary statistics
     */
    public function getServerStats()
    {
        try {
            $stats = [];
            
            // Total servers
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM servers");
            $stats['total'] = $stmt->fetch()['total'];
            
            // Active servers
            $stmt = $this->pdo->query("SELECT COUNT(*) as active FROM servers WHERE is_active = 1");
            $stats['active'] = $stmt->fetch()['active'];
            
            // Inactive servers
            $stmt = $this->pdo->query("SELECT COUNT(*) as inactive FROM servers WHERE is_active = 0");
            $stats['inactive'] = $stmt->fetch()['inactive'];
            
            return $stats;
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to fetch server statistics", ['details' => $e->getMessage()]);
        }
    }
}
