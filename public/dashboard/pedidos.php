<?php
require_once __DIR__ . '/../../config/config.php';

$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: ' . BASE_URL . '/admin-contratante/pedidos.php' . ($query ? '?' . $query : ''), true, $_SERVER['REQUEST_METHOD'] === 'POST' ? 307 : 302);
exit;
