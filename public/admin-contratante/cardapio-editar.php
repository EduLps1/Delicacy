<?php
/**
 * DELICACY - Admin Contratante Edit Cardapio Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/MenuController.php';

$controller = new MenuController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_menu':
            $controller->updateMenu();
            break;
        case 'add_item':
            $controller->addItem();
            break;
        case 'update_item':
            $controller->updateItem();
            break;
        case 'delete_item':
            $controller->deleteItem();
            break;
        case 'toggle_item':
            $controller->toggleItem();
            break;
        default:
            $controller->showEditForm();
    }
} else {
    $controller->showEditForm();
}
