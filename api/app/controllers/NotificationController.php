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
        echo json_encode(["notifications" => $this->notificationService->getAllNotifications()]);
    }

    public function getNotificationsByServerId($id)
    {
        if (!ctype_digit($id)) {
            throw new ValidationException("Invalid server ID", ['server_id' => $id]);
        }

        $notifications = $this->notificationService->getNotificationsByServerId((int)$id);

        echo json_encode(["notifications" => $notifications]);
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
}
