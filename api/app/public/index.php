<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$requestedRoute = $_GET['route'] ?? null;

if (!$requestedRoute) {
    http_response_code(404);
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

require_once __DIR__ . '/../api.php';