<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/RequestHandler.php';
require_once __DIR__ . '/core/ExceptionHandler.php';
require_once __DIR__ . '/exceptions/BaseException.php';
require_once __DIR__ . '/exceptions/ValidationException.php';
require_once __DIR__ . '/exceptions/NotFoundException.php';
require_once __DIR__ . '/exceptions/DatabaseException.php';

try {
    $router = new Router(require __DIR__ . '/routes/Routes.php');
    $requestHandler = new RequestHandler($pdo, $router);
    $requestHandler->handle();
} catch (\Throwable $e) {
    echo \App\Core\ExceptionHandler::handle($e);
}
