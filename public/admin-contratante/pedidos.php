<?php
/**
 * DELICACY - Admin Contratante Pedidos Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/OrderController.php';

$controller = new OrderController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $controller->updateStatus();
    }
} else {
    $controller->listOrders();
}
