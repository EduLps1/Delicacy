<?php
/**
 * DELICACY - Register Router
 */

require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/controllers/AuthController.php';

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $controller->register();
    }
} else {
    $controller->showRegisterForm();
}