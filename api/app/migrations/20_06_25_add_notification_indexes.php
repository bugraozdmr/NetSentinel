<?php
require_once __DIR__ . '/../config/database.php';

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection failed.");
    }

    // Notifications tablosu için performans index'leri
    $sql = "
    -- created_at index (en çok kullanılan sıralama)
    CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at DESC);
    
    -- server_id index (sunucu bazlı sorgular için)
    CREATE INDEX IF NOT EXISTS idx_notifications_server_id ON notifications(server_id);
    
    -- status index (okunmamış bildirimler için)
    CREATE INDEX IF NOT EXISTS idx_notifications_status ON notifications(status);
    
    -- notification_type index (tür bazlı filtreleme için)
    CREATE INDEX IF NOT EXISTS idx_notifications_type ON notifications(notification_type);
    
    -- Composite index (server_id + created_at)
    CREATE INDEX IF NOT EXISTS idx_notifications_server_created ON notifications(server_id, created_at DESC);
    
    -- Composite index (status + created_at)
    CREATE INDEX IF NOT EXISTS idx_notifications_status_created ON notifications(status, created_at DESC);
    
    -- Server notification states için index
    CREATE INDEX IF NOT EXISTS idx_server_notification_states_server_id ON server_notification_states(server_id);
    CREATE INDEX IF NOT EXISTS idx_server_notification_states_updated_at ON server_notification_states(updated_at);
    ";

    $pdo->exec($sql);

    echo json_encode(["success" => "Notification indexes added successfully"]);
} catch (Exception $e) {
    error_log("[ERROR] " . $e->getMessage());
    die(json_encode(["error" => $e->getMessage()]));
} 