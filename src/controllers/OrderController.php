<?php
/**
 * DELICACY - Order Controller
 * 
 * Gerencia pedidos - admin visualiza/atualiza, cliente cria.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/Menu.php';
require_once __DIR__ . '/../models/MenuItem.php';
require_once __DIR__ . '/../models/Order.php';

class OrderController
{
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new Order();
    }

    /**
     * Lista pedidos do restaurante (admin)
     */
    public function listOrders()
    {
        requireRole(ROLE_ADMIN_RESTAURANT);

        $restaurantModel = new Restaurant();
        $userId = getAuthUserId();
        $restaurant = $restaurantModel->findByUserId($userId);

        if (!$restaurant) {
            redirectWithMessage(BASE_URL . '/admin-contratante/', 'Cadastre um restaurante primeiro', 'warning');
        }

        // Filtros
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = sanitizeText($_GET['status']);
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = sanitizeText($_GET['date_from']);
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = sanitizeText($_GET['date_to']);
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $orders = $this->orderModel->findByRestaurantId($restaurant['id'], $filters, $limit, $offset);
        $totalOrders = $this->orderModel->countByRestaurantId($restaurant['id'], $filters);
        $totalPages = ceil($totalOrders / $limit);
        $stats = $this->orderModel->getStats($restaurant['id']);
        $csrf_token = generateCSRFToken();

        require_once VIEWS_PATH . '/dashboard/orders.php';
    }

    /**
     * Atualiza status do pedido (admin)
     */
    public function updateStatus()
    {
        requireRole(ROLE_ADMIN_RESTAURANT);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/pedidos.php', 'Token invÃ¡lido', 'error');
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = sanitizeText($_POST['new_status'] ?? '');

        try {
            // Verifica se o pedido pertence ao restaurante do admin
            $order = $this->orderModel->findById($orderId);
            if (!$order) {
                redirectWithMessage(BASE_URL . '/admin-contratante/pedidos.php', 'Pedido nÃ£o encontrado', 'error');
            }

            $restaurantModel = new Restaurant();
            $restaurant = $restaurantModel->findByUserId(getAuthUserId());

            if (!$restaurant || (int)$order['restaurant_id'] !== (int)$restaurant['id']) {
                redirectWithMessage(BASE_URL . '/admin-contratante/pedidos.php', 'Sem permissÃ£o', 'error');
            }

            $this->orderModel->updateStatus($orderId, $newStatus);

            redirectWithMessage(
                BASE_URL . '/admin-contratante/pedidos.php',
                'Status do pedido #' . $orderId . ' atualizado para ' . $newStatus,
                'success'
            );

        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/admin-contratante/pedidos.php', $e->getMessage(), 'error');
        }
    }

    /**
     * Exibe cardÃ¡pio pÃºblico e carrinho (sem login)
     */
    public function showPublicMenu()
    {
        $slug = $_GET['slug'] ?? '';

        if (empty($slug)) {
            http_response_code(404);
            echo '<h1>CardÃ¡pio nÃ£o encontrado</h1>';
            exit;
        }

        $menuModel = new Menu();
        $menu = $menuModel->findBySlug($slug);

        if (!$menu) {
            http_response_code(404);
            echo '<h1>CardÃ¡pio nÃ£o encontrado ou nÃ£o estÃ¡ publicado</h1>';
            exit;
        }

        $menuItemModel = new MenuItem();
        $itemsGrouped = $menuItemModel->findByMenuIdGrouped($menu['id']);

        require_once VIEWS_PATH . '/cardapio/menu.php';
    }

    /**
     * Processa criaÃ§Ã£o de pedido (cliente - sem login)
     */
    public function createOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);
        $cartData = $_POST['cart_data'] ?? '';

        if (empty($cartData)) {
            redirectWithMessage(BASE_URL, 'Carrinho vazio', 'error');
        }

        $cartItems = json_decode($cartData, true);

        if (empty($cartItems) || !is_array($cartItems)) {
            redirectWithMessage(BASE_URL, 'Dados do carrinho invÃ¡lidos', 'error');
        }

        try {
            $menuItemModel = new MenuItem();

            // Valida e prepara itens
            $items = [];
            foreach ($cartItems as $cartItem) {
                $item = $menuItemModel->findById((int)$cartItem['id']);
                if (!$item || !$item['is_active']) {
                    continue;
                }
                $items[] = [
                    'menu_item_id' => $item['id'],
                    'quantity' => max(1, (int)($cartItem['quantity'] ?? 1)),
                    'price' => (float)$item['price'],
                    'notes' => sanitizeText($cartItem['notes'] ?? '')
                ];
            }

            if (empty($items)) {
                redirectWithMessage(BASE_URL, 'Nenhum item vÃ¡lido no carrinho', 'error');
            }

            $orderId = $this->orderModel->create(
                [
                    'menu_id' => $menuId,
                    'customer_name' => sanitizeText($_POST['customer_name'] ?? ''),
                    'customer_phone' => sanitizeText($_POST['customer_phone'] ?? ''),
                    'delivery_address' => sanitizeText($_POST['delivery_address'] ?? '')
                ],
                $items
            );

            // Redireciona para confirmaÃ§Ã£o
            header('Location: ' . BASE_URL . '/pedido-confirmacao.php?id=' . $orderId);
            exit;

        } catch (Exception $e) {
            redirectWithMessage(BASE_URL, 'Erro ao criar pedido: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Exibe confirmaÃ§Ã£o do pedido
     */
    public function showConfirmation()
    {
        $orderId = (int)($_GET['id'] ?? 0);

        if (!$orderId) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $order = $this->orderModel->findById($orderId);
        if (!$order) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $orderItems = $this->orderModel->getOrderItems($orderId);

        require_once VIEWS_PATH . '/cardapio/pedido-confirmacao.php';
    }
}
