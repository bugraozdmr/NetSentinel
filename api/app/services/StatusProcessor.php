<?php

require_once __DIR__ . '/../models/ServerModel.php';
require_once __DIR__ . '/NotificationService.php';

use App\Core\Logger;

class StatusProcessor
{
    private $logger;
    private $serverModel;
    private $notificationService;

    public function __construct($serverModel, $notificationService)
    {
        $this->logger = Logger::getInstance();
        $this->serverModel = $serverModel;
        $this->notificationService = $notificationService;
    }

    /**
     * Process ping results and update server statuses
     */
    public function processStatusResults(array $servers, array $pingResults): array
    {
        $activeServers = [];
        $inactiveServers = [];
        $updates = [];

        foreach ($servers as $index => $server) {
            $pingResult = $pingResults[$index] ?? ['status' => 0, 'avg_ms' => null];
            
            $status = $pingResult['status'];
            $avgMs = $pingResult['avg_ms'];

            // Log server check
            $this->logger->logServerCheck($server['name'], $server['ip'], $status, $avgMs);

            // Process status change and notifications
            $this->processStatusChange($server, $status);

            // Prepare database update
            $lastChecks = $this->updateLastChecks($server['last_checks'], $status, $avgMs);
            
            $updates[] = [
                'id' => $server['id'],
                'status' => $status,
                'last_checks' => json_encode($lastChecks),
                'location' => $server['location']
            ];

            // Categorize servers
            if ($status === 1) {
                $activeServers[] = $server;
            } else {
                $inactiveServers[] = $server;
            }
        }

        // Batch update database
        $this->batchUpdateStatuses($updates);

        return [
            'active' => $activeServers,
            'inactive' => $inactiveServers
        ];
    }

    /**
     * Process status change and create notifications
     */
    private function processStatusChange(array $server, int $newStatus): void
    {
        $lastChecks = json_decode($server['last_checks'], true);
        if (!is_array($lastChecks)) {
            $lastChecks = [];
        }

        $previousStatus = null;
        if (count($lastChecks) > 0) {
            $previousStatus = $lastChecks[count($lastChecks) - 1]['status'];
        }

        // Create notification if status changed
        if ($previousStatus !== null && $previousStatus !== $newStatus) {
            $this->notificationService->processSmartNotification($server, $previousStatus, $newStatus);
        }
    }

    /**
     * Create notification for status change (DEPRECATED - use processSmartNotification instead)
     */
    private function createStatusChangeNotification(array $server, int $previousStatus, int $newStatus): void
    {
        // Bu metod artık kullanılmıyor, akıllı bildirim sistemi kullanılıyor
        $this->notificationService->processSmartNotification($server, $previousStatus, $newStatus);
    }

    /**
     * Update last checks array
     */
    private function updateLastChecks(string $lastChecksJson, int $status, ?float $avgMs): array
    {
        $lastChecks = json_decode($lastChecksJson, true);
        if (!is_array($lastChecks)) {
            $lastChecks = [];
        }

        $currentTime = date('Y-m-d H:i:s');
        $lastChecks[] = [
            'time' => $currentTime,
            'status' => $status,
            'avg_ms' => $avgMs !== null ? number_format($avgMs, 2, '.', '') : null
        ];

        // Keep only last 10 checks
        if (count($lastChecks) > 10) {
            array_shift($lastChecks);
        }

        return $lastChecks;
    }

    /**
     * Batch update server statuses in database
     */
    private function batchUpdateStatuses(array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        $this->logger->info("Batch updating server statuses", ['count' => count($updates)]);
        
        foreach ($updates as $update) {
            try {
                $this->serverModel->updateStatus(
                    $update['id'],
                    $update['status'],
                    $update['last_checks'],
                    $update['location']
                );
            } catch (Exception $e) {
                $this->logger->error("Failed to update server status", [
                    'server_id' => $update['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
} 