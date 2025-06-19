<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../validators/NotificationValidator.php';
require_once __DIR__ . '/../exceptions/ValidationException.php';
require_once __DIR__ . '/../exceptions/NotFoundException.php';
require_once __DIR__ . '/../exceptions/DatabaseException.php';

use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\DatabaseException;

header('Content-Type: application/json');

class NotificationController
{
    private $notificationService;

    public function __construct($pdo)
    {
        $this->notificationService = new NotificationService($pdo);
    }

    public function getNotifications()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $filters = [];
        
        // Filtreleri al
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['notification_type'])) {
            $filters['notification_type'] = $_GET['notification_type'];
        }
        if (isset($_GET['server_id'])) {
            $filters['server_id'] = (int)$_GET['server_id'];
        }
        
        $result = $this->notificationService->getAllNotifications($page, $limit, $filters);
        echo json_encode($result);
    }

    public function getNotificationsByServerId($id)
    {
        if (!ctype_digit($id)) {
            throw new ValidationException("Invalid server ID", ['server_id' => $id]);
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

        $result = $this->notificationService->getNotificationsByServerId((int)$id, $page, $limit);
        echo json_encode($result);
    }

    public function addNotification($data)
    {
        $errors = NotificationValidator::validateInsert($data);
        if (!empty($errors)) {
            throw new ValidationException("Validation failed", $errors);
        }

        echo json_encode($this->notificationService->addNotification($data));
    }

    public function deleteNotification($id)
    {
        if (!$id) {
            throw new ValidationException("Notification id is required", ['field' => 'id']);
        }

        if (!ctype_digit($id)) {
            throw new ValidationException("Invalid notification ID", ['id' => $id]);
        }

        echo json_encode($this->notificationService->removeNotification($id));
    }

    public function deleteNotificationsByServerId($serverId)
    {
        if (!$serverId) {
            throw new ValidationException("Server ID is required", ['field' => 'server_id']);
        }

        if (!ctype_digit($serverId)) {
            throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
        }

        echo json_encode($this->notificationService->removeNotificationsByServerId((int)$serverId));
    }

    public function deleteOldNotifications()
    {
        // Check if it's a DELETE request with JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        $daysOld = isset($input['days']) ? (int)$input['days'] : 30;
        
        // Fallback to GET parameter if no JSON body
        if (!isset($input['days'])) {
            $daysOld = isset($_GET['days']) ? (int)$_GET['days'] : 30;
        }
        
        echo json_encode($this->notificationService->removeOldNotifications($daysOld));
    }

    public function deleteAllNotifications()
    {
        echo json_encode($this->notificationService->removeAllNotifications());
    }

    public function deleteNotificationsByType()
    {
        $type = $_GET['type'] ?? '';
        if (empty($type)) {
            throw new ValidationException("Notification type is required", ['field' => 'type']);
        }

        echo json_encode($this->notificationService->removeNotificationsByType($type));
    }

    public function notificationCountAction($serverId)
    {
        if (!empty($serverId) && !ctype_digit($serverId) && ($serverId != 'all')) {
            throw new ValidationException("Invalid server ID", ['server_id' => $serverId]);
        }

        if ($serverId == 'all') {
            $serverId = null;
        }

        $count = $this->notificationService->getNotificationCount($serverId);

        echo json_encode(['unread_count' => $count]);
    }

    public function markAsReadAll()
    {
        $result = $this->notificationService->markAsReadAll();
        echo json_encode($result);
    }

    public function markAsRead($id)
    {
        if (!$id) {
            throw new ValidationException("Notification id is required", ['field' => 'id']);
        }

        if (!ctype_digit($id)) {
            throw new ValidationException("Invalid notification ID", ['id' => $id]);
        }

        echo json_encode($this->notificationService->markAsRead($id));
    }
}
