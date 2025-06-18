<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';

use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

class NotificationModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllNotifications()
    {
        try {
            $stmt = $this->pdo->query("
            SELECT n.*, s.name as server_name 
            FROM notifications n 
            JOIN servers s ON n.server_id = s.id 
            ORDER BY n.created_at DESC
        ");
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $notifications;
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to fetch all notifications", ['details' => $e->getMessage()]);
        }
    }

    public function getNotificationsByServerId($serverId)
    {
        try {
            if (!is_numeric($serverId)) {
                throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
            }

            $stmt = $this->pdo->prepare("
            SELECT * FROM notifications 
            WHERE server_id = :server_id 
            ORDER BY created_at DESC
        ");
            $stmt->execute(['server_id' => $serverId]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $notifications;
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to fetch notifications by server ID", ['details' => $e->getMessage()]);
        }
    }

    public function insertNotification($data)
    {
        try {
            if (!isset($data['server_id']) || !isset($data['message'])) {
                throw new ValidationException("Server ID and message are required", ['data' => $data]);
            }

            $status = isset($data['status']) && in_array($data['status'], ['unread', 'read'])
                ? $data['status']
                : 'unread';

            $stmt = $this->pdo->prepare("
            INSERT INTO notifications (server_id, message, status) 
            VALUES (:server_id, :message, :status)
        ");

            $stmt->execute([
                'server_id' => $data['server_id'],
                'message' => $data['message'],
                'status' => $status,
            ]);

            return ["message" => "Notification added successfully"];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to insert notification", ['details' => $e->getMessage()]);
        }
    }

    public function deleteNotification($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new ValidationException("Notification ID is required and must be numeric", ['id' => $id]);
            }

            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() === 0) {
                throw new NotFoundException("Notification not found or already deleted", ['id' => $id]);
            }

            return ["message" => "Notification deleted successfully."];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to delete notification", ['details' => $e->getMessage()]);
        }
    }

    public function getNotificationCount(int $serverId = null): int
    {
        try {
            $query = "SELECT COUNT(*) as count FROM notifications WHERE status = 'unread'";
            $params = [];

            if ($serverId !== null) {
                $query .= " AND server_id = :serverId";
                $params[':serverId'] = $serverId;
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($result['count'] ?? 0);
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to get notification count", ['details' => $e->getMessage()]);
        }
    }

    public function markAllAsRead()
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE notifications SET status = 'read' WHERE status = 'unread'");
            $stmt->execute();

            return [
                "success" => true,
                "message" => "All notifications marked as read",
                "updatedCount" => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to mark all notifications as read", ['details' => $e->getMessage()]);
        }
    }

    public function markAllAsReadByServerId($serverId)
    {
        try {
            if (!is_numeric($serverId)) {
                throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
            }

            $stmt = $this->pdo->prepare("UPDATE notifications SET status = 'read' WHERE status = 'unread' AND server_id = :server_id");
            $stmt->execute(['server_id' => $serverId]);

            return [
                "message" => "Server notifications marked as read",
                "updatedCount" => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to mark server notifications as read", ['details' => $e->getMessage()]);
        }
    }
}
