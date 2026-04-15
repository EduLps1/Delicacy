<?php

declare(strict_types=1);

final class JsonView
{
    public static function send(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Language: pt-BR');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
