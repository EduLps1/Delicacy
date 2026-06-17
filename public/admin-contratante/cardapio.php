<?php
/**
 * DELICACY - Admin Contratante Cardapio Router
 */

require_once __DIR__ . '/../../config/config.php';
require_once SRC_PATH . '/controllers/MenuController.php';

$controller = new MenuController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'delete':
            $controller->deleteMenu();
            break;
        case 'secure_delete':
            $controller->secureDeleteMenu();
            break;
        case 'quick_update':
            $controller->quickUpdateMenu();
            break;
        case 'toggle_publish':
            $controller->togglePublish();
            break;
        default:
            $controller->listMenus();
    }
} else {
    $controller->listMenus();
}
