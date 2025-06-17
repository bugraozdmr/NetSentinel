<?php
require_once __DIR__ . '/../config/database.php';


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
            error_log("getAllNotifications error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Database error: " . $e->getMessage()];
        }
    }

    public function getNotificationsByServerId($serverId)
    {
        try {
            if (!is_numeric($serverId)) {
                throw new Exception("Invalid server ID.");
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
            error_log("getNotificationsByServerId error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Database error: " . $e->getMessage()];
        } catch (Exception $e) {
            http_response_code(400);
            return ["error" => $e->getMessage()];
        }
    }

    public function insertNotification($data)
    {
        try {
            if (!isset($data['server_id']) || !isset($data['message'])) {
                throw new Exception("Server ID and message are required.");
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
            error_log("insertNotification error: " . $e->getMessage());
            // return ["error" => "Database error: Something went wrong -- " . $e->getMessage()];
            http_response_code(500);
            return ["error" => "Database error: Something went wrong"];
        } catch (Exception $e) {
            error_log("insertNotification error: " . $e->getMessage());
            http_response_code(400);
            // return ["error" => $e->getMessage()];
            return ["error" => "Something went wrong"];
        }
    }

    public function deleteNotification($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new Exception("Notification ID is required and must be numeric.");
            }

            $stmt = $this->pdo->prepare("DELETE FROM notifications WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                return ["error" => "Notification not found or already deleted."];
            }

            return ["message" => "Notification deleted successfully."];
        } catch (PDOException $e) {
            error_log("deleteNotification error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "Database error: Something went wrong -- " . $e->getMessage()];
        } catch (Exception $e) {
            error_log("deleteNotification error: " . $e->getMessage());
            http_response_code(400);
            return ["error" => $e->getMessage()];
        }
    }

    public function getNotificationCount(int $serverId = null): int
    {
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
            error_log("markAllAsRead error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "db error: " . $e->getMessage()];
        }
    }

    // mark as read for serverId
    public function markAllAsReadByServerId($serverId)
    {
        try {
            if (!is_numeric($serverId)) {
                throw new Exception("Invalid ID");
            }

            $stmt = $this->pdo->prepare("UPDATE notifications SET status = 'read' WHERE status = 'unread' AND server_id = :server_id");
            $stmt->execute(['server_id' => $serverId]);

            return [
                "message" => "Server notifications marked as read",
                "updatedCount" => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            error_log("markAllAsReadByServerId error: " . $e->getMessage());
            http_response_code(500);
            return ["error" => "db error: " . $e->getMessage()];
        } catch (Exception $e) {
            http_response_code(400);
            return ["error" => $e->getMessage()];
        }
    }
}
