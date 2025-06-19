<?php
require_once __DIR__ . '/../config/database.php';

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection failed.");
    }

    // Notifications tablosuna yeni alanlar ekle
    $sql = "
    ALTER TABLE notifications 
    ADD COLUMN notification_type ENUM('status_change', 'first_down', 'repeated_down', 'long_term_down') DEFAULT 'status_change' AFTER message,
    ADD COLUMN down_count INT DEFAULT 0 AFTER notification_type;
    ";

    $pdo->exec($sql);

    // Server notification states tablosu oluştur
    $sql2 = "
    CREATE TABLE IF NOT EXISTS server_notification_states (
        id INT AUTO_INCREMENT PRIMARY KEY,
        server_id INT NOT NULL,
        last_down_notification_at TIMESTAMP NULL DEFAULT NULL,
        consecutive_down_count INT DEFAULT 0,
        last_notification_type ENUM('status_change', 'first_down', 'repeated_down', 'long_term_down') DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
        UNIQUE KEY server_notification_state_unique (server_id)
    );
    ";

    $pdo->exec($sql2);

    echo json_encode(["success" => "Notification system updated successfully"]);
} catch (Exception $e) {
    error_log("[ERROR] " . $e->getMessage());
    die(json_encode(["error" => $e->getMessage()]));
} 