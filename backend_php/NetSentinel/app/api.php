<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/RequestHandler.php';

$router = new Router(require __DIR__ . '/routes/ServerRoutes.php');
$requestHandler = new RequestHandler($pdo, $router);
$requestHandler->handle();
