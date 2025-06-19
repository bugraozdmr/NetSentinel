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

    public function getAllNotifications($page = 1, $limit = 20, $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $whereConditions = [];
            $params = [];
            
            // Filtreler
            if (!empty($filters['status'])) {
                $whereConditions[] = "n.status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['notification_type'])) {
                $whereConditions[] = "n.notification_type = :notification_type";
                $params['notification_type'] = $filters['notification_type'];
            }
            
            if (!empty($filters['server_id'])) {
                $whereConditions[] = "n.server_id = :server_id";
                $params['server_id'] = $filters['server_id'];
            }
            
            $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
            
            // Toplam sayıyı al
            $countQuery = "
                SELECT COUNT(*) as total 
                FROM notifications n 
                JOIN servers s ON n.server_id = s.id 
                {$whereClause}
            ";
            
            $countStmt = $this->pdo->prepare($countQuery);
            $countStmt->execute($params);
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Sayfalanmış verileri al
            $query = "
                SELECT n.*, s.name as server_name 
                FROM notifications n 
                JOIN servers s ON n.server_id = s.id 
                {$whereClause}
                ORDER BY n.created_at DESC
                LIMIT :limit OFFSET :offset
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'notifications' => $notifications,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $totalCount,
                    'total_pages' => ceil($totalCount / $limit),
                    'has_next' => $page < ceil($totalCount / $limit),
                    'has_prev' => $page > 1
                ]
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to fetch paginated notifications", ['details' => $e->getMessage()]);
        }
    }

    public function getNotificationsByServerId($serverId, $page = 1, $limit = 20)
    {
        try {
            if (!is_numeric($serverId)) {
                throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
            }

            $offset = ($page - 1) * $limit;
            
            // Toplam sayıyı al
            $countQuery = "
                SELECT COUNT(*) as total 
                FROM notifications n
                JOIN servers s ON n.server_id = s.id
                WHERE n.server_id = :server_id
            ";
            
            $countStmt = $this->pdo->prepare($countQuery);
            $countStmt->execute(['server_id' => $serverId]);
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Sayfalanmış verileri al
            $stmt = $this->pdo->prepare("
                SELECT n.*, s.name as server_name 
                FROM notifications n
                JOIN servers s ON n.server_id = s.id
                WHERE n.server_id = :server_id 
                ORDER BY n.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':server_id', $serverId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'notifications' => $notifications,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $totalCount,
                    'total_pages' => ceil($totalCount / $limit),
                    'has_next' => $page < ceil($totalCount / $limit),
                    'has_prev' => $page > 1
                ]
            ];
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

            $notificationType = isset($data['notification_type']) && in_array($data['notification_type'], ['status_change', 'first_down', 'repeated_down', 'long_term_down'])
                ? $data['notification_type']
                : 'status_change';

            $downCount = isset($data['down_count']) ? (int)$data['down_count'] : 0;

            $stmt = $this->pdo->prepare("
            INSERT INTO notifications (server_id, message, status, notification_type, down_count) 
            VALUES (:server_id, :message, :status, :notification_type, :down_count)
        ");

            $stmt->execute([
                'server_id' => $data['server_id'],
                'message' => $data['message'],
                'status' => $status,
                'notification_type' => $notificationType,
                'down_count' => $downCount,
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

    public function deleteNotificationsByServerId($serverId)
    {
        try {
            if (!is_numeric($serverId)) {
                throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
            }

            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE server_id = :server_id");
            $stmt->execute(['server_id' => $serverId]);

            return [
                "message" => "All notifications for server deleted successfully.",
                "deleted_count" => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to delete notifications by server ID", ['details' => $e->getMessage()]);
        }
    }

    public function deleteOldNotifications($daysOld = 30)
    {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
            ");
            $stmt->execute(['days' => $daysOld]);

            return [
                "message" => "Old notifications deleted successfully.",
                "deleted_count" => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to delete old notifications", ['details' => $e->getMessage()]);
        }
    }

    public function deleteNotificationsByType($notificationType)
    {
        try {
            if (!in_array($notificationType, ['status_change', 'first_down', 'repeated_down', 'long_term_down'])) {
                throw new ValidationException("Invalid notification type", ['type' => $notificationType]);
            }

            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE notification_type = :type");
            $stmt->execute(['type' => $notificationType]);

            return [
                "message" => "Notifications of type '{$notificationType}' deleted successfully.",
                "deleted_count" => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to delete notifications by type", ['details' => $e->getMessage()]);
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

    public function markAsRead($notificationId)
    {
        try {
            if (!$notificationId || !is_numeric($notificationId)) {
                throw new ValidationException("Notification ID is required and must be numeric", ['id' => $notificationId]);
            }

            $stmt = $this->pdo->prepare("UPDATE notifications SET status = 'read' WHERE id = :id AND status = 'unread'");
            $stmt->execute(['id' => $notificationId]);

            if ($stmt->rowCount() === 0) {
                throw new NotFoundException("Notification not found or already marked as read", ['id' => $notificationId]);
            }

            return [
                "success" => true,
                "message" => "Notification marked as read successfully"
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to mark notification as read", ['details' => $e->getMessage()]);
        }
    }

    public function deleteAllNotifications()
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM notifications");
            $stmt->execute();

            return [
                "success" => true,
                "message" => "All notifications deleted successfully",
                "deleted_count" => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to delete all notifications", ['details' => $e->getMessage()]);
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

    public function getServerNotificationState($serverId)
    {
        try {
            if (!is_numeric($serverId)) {
                throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
            }

            $stmt = $this->pdo->prepare("
                SELECT * FROM server_notification_states 
                WHERE server_id = :server_id
            ");
            $stmt->execute(['server_id' => $serverId]);
            $state = $stmt->fetch(PDO::FETCH_ASSOC);

            return $state;
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to fetch server notification state", ['details' => $e->getMessage()]);
        }
    }

    public function createOrUpdateServerNotificationState($serverId, $data)
    {
        try {
            if (!is_numeric($serverId)) {
                throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
            }

            // Önce mevcut state'i kontrol et
            $existingState = $this->getServerNotificationState($serverId);

            if ($existingState) {
                // Mevcut state'i güncelle
                $stmt = $this->pdo->prepare("
                    UPDATE server_notification_states 
                    SET last_down_notification_at = :last_down_notification_at,
                        consecutive_down_count = :consecutive_down_count,
                        last_notification_type = :last_notification_type,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE server_id = :server_id
                ");
            } else {
                // Yeni state oluştur
                $stmt = $this->pdo->prepare("
                    INSERT INTO server_notification_states 
                    (server_id, last_down_notification_at, consecutive_down_count, last_notification_type)
                    VALUES (:server_id, :last_down_notification_at, :consecutive_down_count, :last_notification_type)
                ");
            }

            $stmt->execute([
                'server_id' => $serverId,
                'last_down_notification_at' => $data['last_down_notification_at'] ?? null,
                'consecutive_down_count' => $data['consecutive_down_count'] ?? 0,
                'last_notification_type' => $data['last_notification_type'] ?? null,
            ]);

            return ["message" => "Server notification state updated successfully"];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to update server notification state", ['details' => $e->getMessage()]);
        }
    }

    public function resetServerNotificationState($serverId)
    {
        try {
            if (!is_numeric($serverId)) {
                throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
            }

            $stmt = $this->pdo->prepare("
                UPDATE server_notification_states 
                SET consecutive_down_count = 0,
                    last_notification_type = NULL,
                    updated_at = CURRENT_TIMESTAMP
                WHERE server_id = :server_id
            ");
            $stmt->execute(['server_id' => $serverId]);

            return ["message" => "Server notification state reset successfully"];
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to reset server notification state", ['details' => $e->getMessage()]);
        }
    }
}
