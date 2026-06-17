<?php
/**
 * DELICACY - Front Controller
 */

require_once __DIR__ . '/../config/config.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim($path, '/');

if ($path === '/') {
    require_once VIEWS_PATH . '/landing/home.php';
    exit;
}

if ($path === '/admin_contratante/cardapios/novo') {
    require_once SRC_PATH . '/controllers/MenuController.php';
    (new MenuController())->showVisualCreate();
    exit;
}

if ($path === '/admin_contratante/cardapios/api') {
    require_once SRC_PATH . '/controllers/MenuController.php';
    $controller = new MenuController();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'save_editor':
            $controller->saveVisualEditor();
            break;
        case 'publish_editor':
            $controller->publishVisualMenu();
            break;
        case 'upload_editor_asset':
            $controller->uploadEditorAsset();
            break;
        default:
            jsonResponse(['error' => 'Acao invalida'], 400);
    }
}

if (preg_match('#^/admin_contratante/cardapios/([A-Z0-9]{5})/editar$#', $path, $matches)) {
    require_once SRC_PATH . '/controllers/MenuController.php';
    (new MenuController())->showVisualEditor($matches[1]);
    exit;
}

if (preg_match('#^/([a-z0-9_]+_[0-9]+)$#', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require_once SRC_PATH . '/controllers/OrderController.php';
    (new OrderController())->showPublicMenu();
    exit;
}

http_response_code(404);
echo 'Rota nao encontrada';
