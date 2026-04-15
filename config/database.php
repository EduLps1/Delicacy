<?php

declare(strict_types=1);

/**
 * Configuração de banco (PDO).
 *
 * Por enquanto o projeto usa dados em memória; este arquivo já deixa pronto o ponto
 * padrão para evoluir para MySQL/PostgreSQL.
 */
return [
    'dsn' => getenv('DB_DSN') ?: 'mysql:host=localhost;port=3306;dbname=savour_stream;charset=utf8mb4',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
];
