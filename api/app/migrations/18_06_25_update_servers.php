<?php
require_once __DIR__ . '/../config/database.php';

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection failed.");
    }

    $sql = "CREATE TABLE IF NOT EXISTS servers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL UNIQUE,
        name VARCHAR(200) NOT NULL NOT NULL UNIQUE,
        panel ENUM('cPanel', 'Plesk', 'Backup', 'ESXi', 'Yok', 'Diğer') DEFAULT 'Yok',
        location ENUM('mars', 'hetzner') NOT NULL,
        is_active BOOLEAN DEFAULT 0,
        last_checks JSON NOT NULL,
        last_check_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS server_ports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        server_id INT NOT NULL,
        port_number INT NOT NULL,
        is_open BOOLEAN DEFAULT 0,
        FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
        UNIQUE KEY server_port_unique (server_id, port_number) -- if same id and port exists error
    );

    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        server_id INT NOT NULL,
        message TEXT NOT NULL,
        status ENUM('unread', 'read') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
    );
    ";

    $pdo->exec($sql);

    echo json_encode(["success" => "Table created successfully"]);
} catch (Exception $e) {
    error_log("[ERROR] " . $e->getMessage());
    die(json_encode(["error" => $e->getMessage()]));
}
