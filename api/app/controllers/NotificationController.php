<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../validators/NotificationValidator.php';

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
        // TODO : Server Check
        if (!ctype_digit($id)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid server ID"]);
            return;
        }

        $notifications = $this->notificationService->getNotificationsByServerId((int)$id);

        echo json_encode(["notifications" => $notifications]);
    }

    public function addNotification($data)
    {
        $errors = NotificationValidator::validateInsert($data);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(["errors" => $errors]);
            return;
        }

        echo json_encode($this->notificationService->addNotification($data));
    }

    public function deleteNotification($id)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Notification id is required"]);
            return;
        }

        if (!ctype_digit($id)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid server ID"]);
            return;
        }

        echo json_encode($this->notificationService->removeNotification($id));
    }

    public function notificationCountAction($serverId)
    {
        if (!empty($serverId) && !ctype_digit($serverId) && ($serverId != 'all')) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid server ID"]);
            return;
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
