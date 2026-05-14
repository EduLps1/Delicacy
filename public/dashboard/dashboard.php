<?php
require_once __DIR__ . '/../../config/config.php';

$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: ' . BASE_URL . '/admin-contratante/' . ($query ? '?' . $query : ''), true, 302);
exit;
