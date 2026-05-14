<?php
require_once __DIR__ . '/../../config/config.php';

header('Location: ' . BASE_URL . '/admin-contratante/editar-restaurante.php', true, $_SERVER['REQUEST_METHOD'] === 'POST' ? 307 : 302);
exit;
