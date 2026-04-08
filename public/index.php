<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../src/MenuRepository.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/');

if ($path === '') {
    $path = '/';
}

if ($path === '/api/health') {
    echo json_encode([
        'status' => 'ok',
        'service' => 'savour-stream-php',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($path === '/api/menu') {
    echo json_encode([
        'categories' => getCategories(),
        'categoryLabels' => getCategoryLabels(),
        'dishes' => getDishes(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(404);
echo json_encode([
    'error' => 'Not Found',
    'path' => $path,
], JSON_UNESCAPED_UNICODE);
