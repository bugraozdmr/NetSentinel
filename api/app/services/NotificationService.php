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

    public function getAllNotifications($page = 1, $limit = 20, $filters = [])
    {
        return $this->notificationModel->getAllNotifications($page, $limit, $filters);
    }

    public function getNotificationsByServerId(int $serverId, $page = 1, $limit = 20)
    {
        return $this->notificationModel->getNotificationsByServerId($serverId, $page, $limit);
    }

    public function addNotification(array $data)
    {
        return $this->notificationModel->insertNotification($data);
    }

    public function removeNotification(int $notificationId)
    {
        return $this->notificationModel->deleteNotification($notificationId);
    }

    public function removeNotificationsByServerId(int $serverId)
    {
        return $this->notificationModel->deleteNotificationsByServerId($serverId);
    }

    public function removeOldNotifications(int $daysOld = 30)
    {
        return $this->notificationModel->deleteOldNotifications($daysOld);
    }

    public function removeNotificationsByType(string $notificationType)
    {
        return $this->notificationModel->deleteNotificationsByType($notificationType);
    }

    public function getNotificationCount(int $serverId = null)
    {
        return $this->notificationModel->getNotificationCount($serverId);
    }

    public function markAsReadAll()
    {
        return $this->notificationModel->markAllAsRead();
    }

    public function markAsRead(int $notificationId)
    {
        return $this->notificationModel->markAsRead($notificationId);
    }

    public function removeAllNotifications()
    {
        return $this->notificationModel->deleteAllNotifications();
    }

    /**
     * Akıllı bildirim sistemi - Sunucu durumu değişikliklerini kontrol eder
     */
    public function processSmartNotification(array $server, int $previousStatus, int $newStatus): void
    {
        $serverId = $server['id'];
        $serverName = "{$server['name']} (IP: {$server['ip']})";
        
        // Sunucu açıldıysa notification state'i sıfırla
        if ($newStatus === 1 && $previousStatus === 0) {
            $this->notificationModel->resetServerNotificationState($serverId);
            
            // Sunucu açıldı bildirimi gönder
            $active_messages = getActiveMessages();
            $msg = $active_messages[array_rand($active_messages)];
            $message = "Sunucu {$serverName} {$msg}.";
            
            $this->addNotification([
                "server_id" => $serverId,
                "message" => $message,
                "notification_type" => "status_change",
                "status" => "unread"
            ]);
            return;
        }
        
        // Sunucu kapandıysa akıllı bildirim sistemi devreye girer
        if ($newStatus === 0 && $previousStatus === 1) {
            $this->processDownNotification($serverId, $serverName);
        }
    }

    /**
     * Sunucu kapandığında akıllı bildirim oluşturur
     */
    private function processDownNotification(int $serverId, string $serverName): void
    {
        $state = $this->notificationModel->getServerNotificationState($serverId);
        $now = date('Y-m-d H:i:s');
        
        if (!$state) {
            // İlk kez kapandı
            $this->createFirstDownNotification($serverId, $serverName);
            $this->notificationModel->createOrUpdateServerNotificationState($serverId, [
                'last_down_notification_at' => $now,
                'consecutive_down_count' => 1,
                'last_notification_type' => 'first_down'
            ]);
        } else {
            $lastNotificationTime = $state['last_down_notification_at'];
            $consecutiveCount = $state['consecutive_down_count'];
            
            // Son bildirimden bu yana geçen süreyi hesapla (dakika cinsinden)
            $timeDiff = $lastNotificationTime ? 
                (strtotime($now) - strtotime($lastNotificationTime)) / 60 : 
                PHP_INT_MAX;
            
            // Bildirim stratejisi:
            // 1. İlk 30 dakika: Bildirim gönderme
            // 2. 30 dakika - 2 saat: Her 30 dakikada bir tekrar bildirim
            // 3. 2 saat - 24 saat: Her 2 saatte bir uzun süreli bildirim
            // 4. 24 saat sonra: Her 6 saatte bir uzun süreli bildirim
            
            if ($timeDiff < 30) {
                // Henüz çok erken, bildirim gönderme
                return;
            } elseif ($timeDiff < 120) { // 2 saat
                // Her 30 dakikada bir tekrar bildirim
                if ($timeDiff >= 30) {
                    $this->createRepeatedDownNotification($serverId, $serverName, $consecutiveCount + 1);
                    $this->notificationModel->createOrUpdateServerNotificationState($serverId, [
                        'last_down_notification_at' => $now,
                        'consecutive_down_count' => $consecutiveCount + 1,
                        'last_notification_type' => 'repeated_down'
                    ]);
                }
            } else {
                // Uzun süreli düşüş bildirimi
                $hoursDown = round($timeDiff / 60, 1);
                $this->createLongTermDownNotification($serverId, $serverName, $hoursDown);
                $this->notificationModel->createOrUpdateServerNotificationState($serverId, [
                    'last_down_notification_at' => $now,
                    'consecutive_down_count' => $consecutiveCount + 1,
                    'last_notification_type' => 'long_term_down'
                ]);
            }
        }
    }

    /**
     * İlk düşüş bildirimi
     */
    private function createFirstDownNotification(int $serverId, string $serverName): void
    {
        $passive_messages = getPassiveMessages();
        $msg = $passive_messages[array_rand($passive_messages)];
        $message = "Sunucu {$serverName} {$msg}.";
        
        $this->addNotification([
            "server_id" => $serverId,
            "message" => $message,
            "notification_type" => "first_down",
            "down_count" => 1,
            "status" => "unread"
        ]);
    }

    /**
     * Tekrar düşüş bildirimi
     */
    private function createRepeatedDownNotification(int $serverId, string $serverName, int $count): void
    {
        $message = "Sunucu {$serverName} hala kapalı. Bu {$count}. kontrol.";
        
        $this->addNotification([
            "server_id" => $serverId,
            "message" => $message,
            "notification_type" => "repeated_down",
            "down_count" => $count,
            "status" => "unread"
        ]);
    }

    /**
     * Uzun süreli düşüş bildirimi
     */
    private function createLongTermDownNotification(int $serverId, string $serverName, float $hoursDown): void
    {
        $message = "Sunucu {$serverName} {$hoursDown} saattir kapalı. Acil müdahale gerekli!";
        
        $this->addNotification([
            "server_id" => $serverId,
            "message" => $message,
            "notification_type" => "long_term_down",
            "down_count" => 0,
            "status" => "unread"
        ]);
    }
}
