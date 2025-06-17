<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../utils/config.php';

class NotificationService
{
    private $notificationModel;

    public function __construct($pdo)
    {
        $this->notificationModel = new NotificationModel($pdo);
    }

    public function getAllNotifications()
    {
        return $this->notificationModel->getAllNotifications();
    }

    public function getNotificationsByServerId(int $serverId)
    {
        return $this->notificationModel->getNotificationsByServerId($serverId);
    }

    public function addNotification(array $data)
    {
        return $this->notificationModel->insertNotification($data);
    }

    public function removeNotification(int $notificationId)
    {
        return $this->notificationModel->deleteNotification($notificationId);
    }

    public function getNotificationCount(int $serverId = null)
    {
        return $this->notificationModel->getNotificationCount($serverId);
    }

    public function markAsReadAll()
    {
        return $this->notificationModel->markAllAsRead();
    }
}
