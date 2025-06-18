<?php

class Config
{
    private static $phpPath;

    public static function load()
    {
        // Try to load .env file if it exists
        $envPath = __DIR__ . '/../..';
        if (file_exists($envPath . '/.env')) {
            try {
                if (file_exists($envPath . '/vendor/autoload.php')) {
                    require_once $envPath . '/vendor/autoload.php';
                    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
                    $dotenv->load();
                    self::$phpPath = $_ENV['PHP_PATH'] ?? '/usr/bin/php';
                } else {
                    // Fallback: parse .env manually
                    $envContent = file_get_contents($envPath . '/.env');
                    $lines = explode("\n", $envContent);
                    foreach ($lines as $line) {
                        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
                            list($key, $value) = explode('=', $line, 2);
                            $_ENV[trim($key)] = trim($value);
                        }
                    }
                    self::$phpPath = $_ENV['PHP_PATH'] ?? '/usr/bin/php';
                }
            } catch (Exception $e) {
                // If anything fails, use default PHP path
                self::$phpPath = '/usr/bin/php';
            }
        } else {
            // No .env file, use default PHP path
            self::$phpPath = '/usr/bin/php';
        }
    }

    public static function getPhpPath()
    {
        if (!self::$phpPath) {
            self::load();
        }
        return self::$phpPath;
    }
}
