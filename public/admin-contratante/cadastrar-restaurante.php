<?php
/**
 * DELICACY - Admin Contratante Register Restaurant Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/AdminContratanteController.php';

$controller = new AdminContratanteController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $controller->registerRestaurant();
    } else {
        header('Location: ' . BASE_URL . '/admin-contratante/cadastrar-restaurante.php');
        exit;
    }
} else {
    $controller->showRegisterRestaurantForm();
}
