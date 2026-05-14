<?php
/**
 * DELICACY - Admin Contratante Novo Cardapio Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/MenuController.php';

$controller = new MenuController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->createMenu();
} else {
    $controller->showCreateForm();
}
