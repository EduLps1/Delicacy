<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/controllers/MenuController.php';

return [
    'GET' => [
        '/api/health' => [MenuController::class, 'health'],
        '/api/menu' => [MenuController::class, 'menu'],
    ],
];
