<?php

declare(strict_types=1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$routes = require __DIR__ . '/../routes/web.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

$handler = $routes[$method][$path] ?? null;

if ($handler === null) {
    require_once __DIR__ . '/../app/views/JsonView.php';
    JsonView::send([
        'error' => 'Not Found',
        'method' => $method,
        'path' => $path,
    ], 404);
    exit;
}

[$controllerClass, $action] = $handler;
$controller = new $controllerClass();
$controller->$action();
