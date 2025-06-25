<?php
// Prevent any output before JSON response
ob_start();

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable HTML error output
ini_set('log_errors', 1);

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        throw new Exception('No input data received');
    }
    
    $settings = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!$settings || !is_array($settings)) {
        throw new Exception('Invalid settings data');
    }

    // Validate required fields
    $requiredFields = ['API_BASE_URL', 'APP_NAME', 'PAGE_REFRESH_INTERVAL', 'REAL_TIME_INTERVAL', 'ENABLE_REAL_TIME_UPDATES'];
    foreach ($requiredFields as $field) {
        if (!isset($settings[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate data types
    if (!is_string($settings['API_BASE_URL']) || empty($settings['API_BASE_URL'])) {
        throw new Exception('Invalid API_BASE_URL - must be a non-empty string');
    }

    // More flexible URL validation for localhost and development environments
    $apiUrl = $settings['API_BASE_URL'];
    if (!preg_match('/^https?:\/\/[a-zA-Z0-9\-\.]+(:\d+)?(\/.*)?$/', $apiUrl)) {
        throw new Exception('Invalid API_BASE_URL format');
    }

    if (!is_string($settings['APP_NAME']) || empty($settings['APP_NAME'])) {
        throw new Exception('Invalid APP_NAME');
    }

    if (!is_numeric($settings['PAGE_REFRESH_INTERVAL']) || $settings['PAGE_REFRESH_INTERVAL'] < 1000) {
        throw new Exception('PAGE_REFRESH_INTERVAL must be at least 1000ms');
    }

    if (!is_numeric($settings['REAL_TIME_INTERVAL']) || $settings['REAL_TIME_INTERVAL'] < 10000) {
        throw new Exception('REAL_TIME_INTERVAL must be at least 10000ms');
    }

    // Handle boolean conversion
    if (!is_bool($settings['ENABLE_REAL_TIME_UPDATES'])) {
        // Convert string to boolean if needed
        if (is_string($settings['ENABLE_REAL_TIME_UPDATES'])) {
            $settings['ENABLE_REAL_TIME_UPDATES'] = $settings['ENABLE_REAL_TIME_UPDATES'] === 'true' || $settings['ENABLE_REAL_TIME_UPDATES'] === '1';
        } elseif (is_numeric($settings['ENABLE_REAL_TIME_UPDATES'])) {
            $settings['ENABLE_REAL_TIME_UPDATES'] = (bool)$settings['ENABLE_REAL_TIME_UPDATES'];
        } else {
            throw new Exception('ENABLE_REAL_TIME_UPDATES must be boolean');
        }
    }

    // Prepare settings for JSON file
    $settingsForFile = [
        'API_BASE_URL' => $settings['API_BASE_URL'],
        'APP_NAME' => $settings['APP_NAME'],
        'PAGE_REFRESH_INTERVAL' => (int)$settings['PAGE_REFRESH_INTERVAL'],
        'REAL_TIME_INTERVAL' => (int)$settings['REAL_TIME_INTERVAL'],
        'ENABLE_REAL_TIME_UPDATES' => (bool)$settings['ENABLE_REAL_TIME_UPDATES']
    ];

    // Save to settings.json
    $settingsFile = __DIR__ . '/settings.json';
    $jsonData = json_encode($settingsForFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to encode settings: ' . json_last_error_msg());
    }

    // Debug information
    $debug = [
        'settingsFile' => $settingsFile,
        'fileExists' => file_exists($settingsFile),
        'isWritable' => is_writable($settingsFile),
        'dirWritable' => is_writable(__DIR__),
        'jsonData' => $jsonData
    ];

    if (file_put_contents($settingsFile, $jsonData) === false) {
        throw new Exception('Failed to save settings file');
    }

    // Clear any output buffer
    ob_end_clean();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Settings saved successfully',
        'settings' => $settingsForFile,
        'debug' => $debug
    ]);

} catch (Exception $e) {
    // Clear any output buffer
    ob_end_clean();
    
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    // Clear any output buffer
    ob_end_clean();
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?> 