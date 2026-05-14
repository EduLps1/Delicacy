<?php
/**
 * DELICACY - Confirmação de Pedido (público)
 */

require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/controllers/OrderController.php';

$controller = new OrderController();
$controller->showConfirmation();
