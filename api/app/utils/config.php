<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

class Config
{
    private static $phpPath;

    public static function load()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
        self::$phpPath = $_ENV['PHP_PATH'] ?? '/usr/bin/php';
    }

    public static function getPhpPath()
    {
        return self::$phpPath;
    }
}
