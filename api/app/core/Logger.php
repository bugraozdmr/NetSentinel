<?php

namespace App\Core;

class Logger
{
    private static $instance = null;
    private $logDir;
    private $logLevel;
    
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    private function __construct()
    {
        $this->logDir = __DIR__ . '/../logs'; // app/logs
        $this->logLevel = $this->getLogLevel();
        
        // Create logs directory if it doesn't exist
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    private function getLogLevel(): int
    {
        // You can set this via environment variable or config
        $level = $_ENV['LOG_LEVEL'] ?? 'INFO';
        
        switch (strtoupper($level)) {
            case 'EMERGENCY': return self::EMERGENCY;
            case 'ALERT':     return self::ALERT;
            case 'CRITICAL':  return self::CRITICAL;
            case 'ERROR':     return self::ERROR;
            case 'WARNING':   return self::WARNING;
            case 'NOTICE':    return self::NOTICE;
            case 'INFO':      return self::INFO;
            case 'DEBUG':     return self::DEBUG;
            default:          return self::INFO;
        }
    }

    private function getLogFileName(string $level = 'app'): string
    {
        $date = date('Y-m-d');
        return "{$this->logDir}/{$level}-{$date}.log";
    }

    private function formatLogEntry(string $level, string $message, array $context = []): array
    {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
        ];
    }

    private function writeLog(string $level, string $message, array $context = []): void
    {
        $levelValue = constant("self::" . strtoupper($level));
        
        if ($levelValue > $this->logLevel) {
            return; // Skip if log level is higher than configured
        }

        $logEntry = $this->formatLogEntry($level, $message, $context);
        $logFile = $this->getLogFileName();
        
        $jsonLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        
        file_put_contents($logFile, $jsonLine, FILE_APPEND | LOCK_EX);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->writeLog('emergency', $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->writeLog('alert', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->writeLog('critical', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->writeLog('error', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->writeLog('warning', $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->writeLog('notice', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->writeLog('info', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->writeLog('debug', $message, $context);
    }

    // Convenience method for API requests
    public function logApiRequest(string $method, string $endpoint, array $data = [], int $statusCode = 200): void
    {
        $this->info("API Request", [
            'method' => $method,
            'endpoint' => $endpoint,
            'data' => $data,
            'status_code' => $statusCode
        ]);
    }

    // Convenience method for database operations
    public function logDatabaseOperation(string $operation, string $table, array $data = [], ?string $error = null): void
    {
        $level = $error ? 'error' : 'info';
        $this->$level("Database Operation", [
            'operation' => $operation,
            'table' => $table,
            'data' => $data,
            'error' => $error
        ]);
    }

    // Convenience method for server status checks
    public function logServerCheck(string $serverName, string $ip, int $status, ?float $responseTime = null): void
    {
        $level = $status === 1 ? 'info' : 'warning';
        $this->$level("Server Status Check", [
            'server_name' => $serverName,
            'ip' => $ip,
            'status' => $status,
            'response_time' => $responseTime
        ]);
    }

    // Get recent logs
    public function getRecentLogs(int $lines = 100, string $level = null): array
    {
        $logFile = $this->getLogFileName();
        
        if (!file_exists($logFile)) {
            return [];
        }

        $logs = [];
        $file = new \SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        
        for ($i = $startLine; $i < $totalLines; $i++) {
            $file->seek($i);
            $line = trim($file->current());
            
            if (empty($line)) continue;
            
            $logEntry = json_decode($line, true);
            
            if ($logEntry && (!$level || strtoupper($logEntry['level']) === strtoupper($level))) {
                $logs[] = $logEntry;
            }
        }

        return array_reverse($logs);
    }

    // Clear old logs (keep last N days)
    public function clearOldLogs(int $daysToKeep = 30): int
    {
        $deletedCount = 0;
        $cutoffDate = date('Y-m-d', strtotime("-{$daysToKeep} days"));
        
        $files = glob($this->logDir . '/*.log');
        
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/(\d{4}-\d{2}-\d{2})\.log$/', $filename, $matches)) {
                $fileDate = $matches[1];
                if ($fileDate < $cutoffDate) {
                    if (unlink($file)) {
                        $deletedCount++;
                    }
                }
            }
        }
        
        return $deletedCount;
    }
} 