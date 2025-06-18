<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/RequestHandler.php';
require_once __DIR__ . '/core/ExceptionHandler.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/exceptions/BaseException.php';
require_once __DIR__ . '/exceptions/ValidationException.php';
require_once __DIR__ . '/exceptions/NotFoundException.php';
require_once __DIR__ . '/exceptions/DatabaseException.php';

use App\Core\Logger;

try {
    $logger = Logger::getInstance();
    
    // Log the API request
    $logger->logApiRequest(
        $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
        $_POST ?: [],
        200
    );

    $router = new Router(require __DIR__ . '/routes/Routes.php');
    $requestHandler = new RequestHandler($pdo, $router);
    $requestHandler->handle();
    
} catch (\Throwable $e) {
    $response = \App\Core\ExceptionHandler::handle($e);
    
    // Log the error response
    if (isset($logger)) {
        $logger->logApiRequest(
            $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
            $_POST ?: [],
            http_response_code()
        );
    }
    
    echo $response;
}
