<?php

/* --- Show Errors ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

$baseUrl = '/netsentinel';
$appName = 'NetSentinel';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routePath = str_replace($baseUrl . '/', '', $uri);

$routePath = trim($routePath, '/');

$segments = explode('/', $routePath);

$id = null;
if (count($segments) >= 3) {
    $id = array_pop($segments);
}

$route = implode('/', $segments);

$route = $route === '' ? 'home' : $route;

$allowedPages = ['home', 'server/addServer', 'server/updateServer'];
$contentFile = __DIR__ . "/pages/{$route}.php";

if ($route === 'server/updateServer' && $id === null) {
    header("HTTP/1.0 404 Not Found");
    $route = '404';
    $contentFile = __DIR__ . "/pages/404.php";
}

if ($route !== 'home' && (!in_array($route, $allowedPages) || !file_exists($contentFile))) {
    header("HTTP/1.0 404 Not Found");
    $route = '404';
    $contentFile = __DIR__ . "/pages/404.php";
}

$GLOBALS['id'] = $id;

ob_start();
include $contentFile;
$content = ob_get_clean();

include __DIR__ . '/templates/main.php';
