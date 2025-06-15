<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/RequestHandler.php';

$router = new Router(require __DIR__ . '/routes/Routes.php');
$requestHandler = new RequestHandler($pdo, $router);
$requestHandler->handle();
