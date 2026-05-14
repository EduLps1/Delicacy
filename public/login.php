<?php
/**
 * DELICACY - Login Router
 * 
 * Manipula requisições POST de login e exibe formulário GET
 */

require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/controllers/AuthController.php';

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        $controller->login();
    }
} else {
    // GET - Exibe formulário
    $controller->showLoginForm();
}