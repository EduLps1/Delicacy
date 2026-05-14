<?php
/**
 * DELICACY - Admin Delicacy Controller
 * 
 * Gerencia operações administrativas da plataforma.
 * Acesso restrito a admin_delicacy.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/Menu.php';

class AdminDelicacyController {
    private $userModel;
    private $restaurantModel;
    private $menuModel;

    public function __construct() {
        requireRole(ROLE_ADMIN_DELICACY);
        $this->userModel = new User();
        $this->restaurantModel = new Restaurant();
        $this->menuModel = new Menu();
    }

    /**
     * Exibe dashboard do admin Delicacy
     */
    public function showDashboard() {
        // Coleta estatísticas
        $stats = [
            'total_restaurants' => $this->restaurantModel->count(['is_active' => 1]),
            'total_users' => $this->userModel->countByRole(ROLE_ADMIN_RESTAURANT),
            'total_inactive' => $this->restaurantModel->count(['is_active' => 0]),
            'total_menus' => $this->menuModel->count(),
            'estimated_revenue' => 0
        ];

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-delicacy/dashboard.php';
    }

    /**
     * Lista restaurantes com busca e paginação
     */
    public function listRestaurants() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = isset($_GET['search']) ? sanitizeText($_GET['search']) : '';
        $status = isset($_GET['status']) ? sanitizeText($_GET['status']) : '';

        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Monta filtros
        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        if ($status === 'active') {
            $filters['is_active'] = 1;
        } elseif ($status === 'inactive') {
            $filters['is_active'] = 0;
        }

        // Busca restaurantes
        $restaurants = $this->restaurantModel->findAll($filters, $limit, $offset);
        $total = $this->restaurantModel->count($filters);
        $pages = ceil($total / $limit);

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-delicacy/list-restaurants.php';
    }

    /**
     * Exibe detalhes de um restaurante
     */
    public function showRestaurant($restaurantId) {
        $restaurant = $this->restaurantModel->findById($restaurantId);

        if (!$restaurant) {
            redirectWithMessage(
                BASE_URL . '/admin-delicacy/restaurants.php',
                'Restaurante não encontrado',
                'error'
            );
        }

        // Coleta dados do restaurante
        $user = $this->userModel->findById($restaurant['user_id']);

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-delicacy/restaurant-detail.php';
    }

    /**
     * Atualiza status de restaurante (ativar/desativar)
     */
    public function updateRestaurantStatus($restaurantId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        // Validar CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            jsonResponse(['error' => 'Token inválido'], 403);
        }

        try {
            $restaurant = $this->restaurantModel->findById($restaurantId);
            if (!$restaurant) {
                jsonResponse(['error' => 'Restaurante não encontrado'], 404);
            }

            $new_status = $restaurant['is_active'] ? 0 : 1;
            $this->restaurantModel->update($restaurantId, ['is_active' => $new_status]);

            // Registra ação
            $this->logAdminAction(
                getAuthUserId(),
                'update',
                'restaurants',
                $restaurantId,
                ['is_active' => $new_status]
            );

            jsonResponse([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
                'new_status' => $new_status
            ]);

        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Exibe lista de usuários (admin_restaurant)
     */
    public function listUsers() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $users = $this->userModel->findByRole(ROLE_ADMIN_RESTAURANT, $limit, $offset);
        $total = $this->userModel->countByRole(ROLE_ADMIN_RESTAURANT);
        $pages = ceil($total / $limit);

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-delicacy/list-users.php';
    }

    /**
     * Registra ação administrativa em admin_logs
     */
    private function logAdminAction($adminId, $action, $entityType, $entityId = null, $changes = null) {
        $query = "INSERT INTO admin_logs (admin_id, action, entity_type, entity_id, changes) 
                  VALUES (?, ?, ?, ?, ?)";
        
        Database::getInstance()->execute(
            $query,
            [
                $adminId,
                $action,
                $entityType,
                $entityId,
                $changes ? json_encode($changes) : null
            ],
            'issis'
        );
    }
}
