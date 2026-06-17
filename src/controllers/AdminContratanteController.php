<?php
/**
 * DELICACY - Admin Contratante (Restaurant) Controller
 *
 * Gerencia operacoes dos restaurantes contratantes.
 * Acesso restrito a admin_restaurant.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/Order.php';

class AdminContratanteController {
    private $userModel;
    private $restaurantModel;
    private $orderModel;

    public function __construct() {
        requireRole(ROLE_ADMIN_RESTAURANT);
        $this->userModel = new User();
        $this->restaurantModel = new Restaurant();
        $this->orderModel = new Order();
    }

    /**
     * Exibe dashboard do restaurante.
     */
    public function showDashboard() {
        $userId = getAuthUserId();
        $restaurant = $this->restaurantModel->findByUserId($userId);
        if (!$restaurant) {
            $restaurant = $this->restaurantModel->ensureTestRestaurantForUser($userId, 'Teste');
        }

        $periodDays = (int)($_GET['period'] ?? 30);
        if (!in_array($periodDays, [7, 30, 90, 365], true)) {
            $periodDays = 30;
        }

        $summary = ['total_orders' => 0, 'revenue' => 0.0];
        $dailySeries = $this->buildEmptyDailySeries(min($periodDays, 30));

        if ($restaurant) {
            $summary = $this->orderModel->getSummaryByRestaurantId($restaurant['id'], $periodDays);
            $dailySeries = $this->orderModel->getDailySeriesByRestaurantId($restaurant['id'], min($periodDays, 30));
        }

        $commissionRate = (float)($restaurant['active_commission_rate'] ?? 0);
        $commission = ((float)$summary['revenue'] * $commissionRate) / 100;

        $contractorStats = [
            'period_days' => $periodDays,
            'revenue' => (float)$summary['revenue'],
            'commission' => $commission,
            'received' => max(0, (float)$summary['revenue'] - $commission),
            'orders' => (int)$summary['total_orders'],
            'average_ticket' => (int)$summary['total_orders'] > 0
                ? (float)$summary['revenue'] / (int)$summary['total_orders']
                : 0.0
        ];

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-contratante/dashboard.php';
    }

    private function buildEmptyDailySeries($days) {
        $days = max(1, min(30, (int)$days));
        $series = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $series[] = [
                'day' => (int)date('j', strtotime($date)),
                'date' => $date,
                'orders' => 0,
                'revenue' => 0.0
            ];
        }

        return $series;
    }

    /**
     * Exibe formulario de cadastro de restaurante.
     */
    public function showRegisterRestaurantForm() {
        $userId = getAuthUserId();
        $existing = $this->restaurantModel->findByUserId($userId);

        if ($existing) {
            header('Location: ' . BASE_URL . '/admin-contratante/');
            exit;
        }

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/dashboard/register-restaurant.php';
    }

    /**
     * Processa cadastro de restaurante.
     */
    public function registerRestaurant() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin-contratante/cadastrar-restaurante.php');
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(
                BASE_URL . '/admin-contratante/cadastrar-restaurante.php',
                'Token de seguranca invalido',
                'error'
            );
        }

        $userId = getAuthUserId();
        $data = [
            'user_id' => $userId,
            'name' => sanitizeText($_POST['name'] ?? ''),
            'description' => sanitizeText($_POST['description'] ?? ''),
            'cnpj' => sanitizeText($_POST['cnpj'] ?? ''),
            'phone' => sanitizeText($_POST['phone'] ?? ''),
            'email' => sanitizeEmail($_POST['email'] ?? ''),
            'commission_type' => sanitizeText($_POST['commission_type'] ?? COMMISSION_HYBRID),
            'plan_type' => sanitizeText($_POST['plan_type'] ?? PLAN_BASIC)
        ];

        if (empty($data['name'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cadastrar-restaurante.php', 'Nome do restaurante e obrigatorio', 'error');
        }

        if (empty($data['cnpj'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cadastrar-restaurante.php', 'CNPJ e obrigatorio', 'error');
        }

        if (!validateCNPJ($data['cnpj'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cadastrar-restaurante.php', 'CNPJ invalido', 'error');
        }

        if (!empty($_POST['phone']) && !validatePhoneBR($_POST['phone'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cadastrar-restaurante.php', 'Telefone invalido (use DDD + 8 ou 9 digitos)', 'error');
        }

        try {
            $restaurantId = $this->restaurantModel->create($data);
            $this->logAdminAction(getAuthUserId(), 'create', 'restaurants', $restaurantId, $data);

            redirectWithMessage(
                BASE_URL . '/admin-contratante/',
                'Restaurante cadastrado com sucesso!',
                'success'
            );
        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cadastrar-restaurante.php', $e->getMessage(), 'error');
        }
    }

    /**
     * Exibe formulario de edicao de restaurante.
     */
    public function showEditRestaurantForm() {
        $userId = getAuthUserId();
        $restaurant = $this->restaurantModel->findByUserId($userId);

        if (!$restaurant) {
            $user = getAuthUser() ?: ['name' => 'Restaurante', 'email' => ''];
            $restaurant = [
                'id' => 0,
                'name' => $user['name'] ?? 'Restaurante',
                'description' => '',
                'phone' => '',
                'email' => $user['email'] ?? ''
            ];
        }

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/dashboard/edit-restaurant.php';
    }

    /**
     * Processa edicao de restaurante.
     */
    public function updateRestaurant() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin-contratante/editar-restaurante.php');
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/editar-restaurante.php', 'Token invalido', 'error');
        }

        try {
            $restaurant = $this->restaurantModel->findByUserId(getAuthUserId());

            if (!$restaurant) {
                redirectWithMessage(BASE_URL . '/admin-contratante/editar-restaurante.php', 'Restaurante nao encontrado', 'error');
            }

            $data = [];

            if (isset($_POST['name'])) {
                $data['name'] = sanitizeText($_POST['name']);
            }

            if (isset($_POST['description'])) {
                $data['description'] = sanitizeText($_POST['description']);
            }

            if (isset($_POST['phone'])) {
                if (!empty($_POST['phone']) && !validatePhoneBR($_POST['phone'])) {
                    redirectWithMessage(BASE_URL . '/admin-contratante/editar-restaurante.php', 'Telefone invalido', 'error');
                }
                $data['phone'] = sanitizeText($_POST['phone']);
            }

            if (isset($_POST['email'])) {
                if (!empty($_POST['email']) && !validateEmail($_POST['email'])) {
                    redirectWithMessage(BASE_URL . '/admin-contratante/editar-restaurante.php', 'Email invalido', 'error');
                }
                $data['email'] = sanitizeEmail($_POST['email']);
            }

            $this->restaurantModel->update($restaurant['id'], $data);
            $this->logAdminAction(getAuthUserId(), 'update', 'restaurants', $restaurant['id'], $data);

            redirectWithMessage(BASE_URL . '/admin-contratante/editar-restaurante.php', 'Restaurante atualizado com sucesso', 'success');
        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/admin-contratante/editar-restaurante.php', $e->getMessage(), 'error');
        }
    }

    /**
     * Registra acao administrativa em admin_logs.
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
