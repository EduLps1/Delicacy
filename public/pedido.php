<?php
/**
 * DELICACY - Criar Pedido (público, sem login)
 */

require_once __DIR__ . '/../config/config.php';
require_once SRC_PATH . '/controllers/OrderController.php';

$controller = new OrderController();
$controller->createOrder();
