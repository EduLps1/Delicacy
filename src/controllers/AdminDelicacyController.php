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
require_once __DIR__ . '/../models/Order.php';

class AdminDelicacyController {
    private $userModel;
    private $restaurantModel;
    private $menuModel;
    private $orderModel;

    public function __construct() {
        requireRole(ROLE_ADMIN_DELICACY);
        $this->userModel = new User();
        $this->restaurantModel = new Restaurant();
        $this->menuModel = new Menu();
        $this->orderModel = new Order();
    }

    /**
     * Exibe dashboard do admin Delicacy
     */
    public function showDashboard() {
        // Coleta estatísticas
        $database = Database::getInstance();
        $revenue = $database->fetchOne(
            "SELECT COALESCE(SUM(total_value), 0) AS total FROM orders WHERE status != 'cancelled'"
        );
        $newRestaurants = $database->fetchOne(
            "SELECT COUNT(*) AS total FROM restaurants WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
        );
        $activeOrders = $database->fetchOne(
            "SELECT COUNT(*) AS total FROM orders WHERE status IN ('pending', 'paid', 'preparing', 'ready')"
        );

        $stats = [
            'total_restaurants' => $this->restaurantModel->count(['is_active' => 1]),
            'total_users' => $this->userModel->countByRole(ROLE_ADMIN_RESTAURANT),
            'total_inactive' => $this->restaurantModel->count(['is_active' => 0]),
            'total_menus' => $this->menuModel->count(),
            'estimated_revenue' => (float)($revenue['total'] ?? 0),
            'new_restaurants' => (int)($newRestaurants['total'] ?? 0),
            'active_orders' => (int)($activeOrders['total'] ?? 0)
        ];

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-delicacy/dashboard.php';
    }

    /**
     * Lista restaurantes com busca e paginação
     */
    public function listRestaurants() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
        $status = isset($_GET['status']) ? strtolower(trim((string)$_GET['status'])) : '';
        if (!in_array($status, ['active', 'pending', 'inactive'], true)) {
            $status = '';
        }

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

        // O schema atual ainda nao possui estado pendente; o filtro fica pronto sem exibir falsos resultados.
        if ($status === 'pending') {
            $restaurants = [];
            $total = 0;
        } else {
            $restaurants = $this->restaurantModel->findAll($filters, $limit, $offset);
            $total = $this->restaurantModel->count($filters);
        }
        $pages = ceil($total / $limit);
        $restaurantStats = [
            'total' => $this->restaurantModel->count(),
            'active' => $this->restaurantModel->count(['is_active' => 1]),
            'inactive' => $this->restaurantModel->count(['is_active' => 0]),
            'pending' => 0
        ];

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-delicacy/list-restaurants.php';
    }

    /**
     * Exibe a dashboard de um restaurante em modo somente leitura para o admin global.
     */
    public function showRestaurantDashboard($restaurantId) {
        $restaurant = $this->restaurantModel->findById((int)$restaurantId);

        if (!$restaurant) {
            redirectWithMessage(
                BASE_URL . '/admin-delicacy/restaurants.php',
                'Restaurante nao encontrado',
                'error'
            );
        }

        $periodDays = (int)($_GET['period'] ?? 30);
        if (!in_array($periodDays, [7, 30, 90, 365], true)) {
            $periodDays = 30;
        }

        $summary = $this->orderModel->getSummaryByRestaurantId($restaurant['id'], $periodDays);
        $dailySeries = $this->orderModel->getDailySeriesByRestaurantId($restaurant['id'], min($periodDays, 30));
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

        $adminPreview = true;
        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-contratante/dashboard.php';
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

        $database = Database::getInstance();
        $allUsers = $database->fetchOne("SELECT COUNT(*) AS total FROM users");
        $activeStaff = $database->fetchOne(
            "SELECT COUNT(*) AS total FROM users
             WHERE status = ? AND role IN (?, ?, ?)",
            [STATUS_ACTIVE, ROLE_ADMIN_DELICACY, ROLE_ADMIN_RESTAURANT, ROLE_ATTENDANT],
            'ssss'
        );
        $userStats = [
            'users' => (int)($allUsers['total'] ?? 0),
            'restaurants' => $this->restaurantModel->count(),
            'active_staff' => (int)($activeStaff['total'] ?? 0),
            'invites' => null
        ];

        $activity = [
            'labels' => [],
            'registrations' => [],
            'accesses' => []
        ];
        $createdByDay = [];
        foreach ($database->fetchAll(
            "SELECT DATE(created_at) AS activity_day, COUNT(*) AS total
             FROM users
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(created_at)"
        ) as $entry) {
            $createdByDay[$entry['activity_day']] = (int)$entry['total'];
        }
        $accessByDay = [];
        foreach ($database->fetchAll(
            "SELECT DATE(last_login_at) AS activity_day, COUNT(*) AS total
             FROM users
             WHERE last_login_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(last_login_at)"
        ) as $entry) {
            $accessByDay[$entry['activity_day']] = (int)$entry['total'];
        }
        for ($dayOffset = 6; $dayOffset >= 0; $dayOffset--) {
            $date = date('Y-m-d', strtotime("-{$dayOffset} days"));
            $activity['labels'][] = date('d/m', strtotime($date));
            $activity['registrations'][] = $createdByDay[$date] ?? 0;
            $activity['accesses'][] = $accessByDay[$date] ?? 0;
        }

        $profiles = [
            ROLE_ADMIN_DELICACY => 0,
            ROLE_ADMIN_RESTAURANT => 0,
            ROLE_ATTENDANT => 0,
            ROLE_CUSTOMER => 0
        ];
        foreach ($database->fetchAll("SELECT role, COUNT(*) AS total FROM users GROUP BY role") as $entry) {
            if (array_key_exists($entry['role'], $profiles)) {
                $profiles[$entry['role']] = (int)$entry['total'];
            }
        }

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-delicacy/list-users.php';
    }

    /**
     * Exibe os controles e o monitoramento global da plataforma.
     */
    public function showSettings() {
        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-delicacy/settings.php';
    }

    /**
     * Exibe a performance financeira consolidada da plataforma.
     */
    public function showFinancial() {
        $database = Database::getInstance();
        $currentPeriod = date('Y-m');

        $gmvRow = $database->fetchOne(
            "SELECT COALESCE(SUM(total_value), 0) AS total
             FROM orders
             WHERE status != 'cancelled'"
        );
        $revenueRow = $database->fetchOne(
            "SELECT COALESCE(SUM(commission_value), 0) AS total
             FROM commissions
             WHERE status IN ('charged', 'paid')"
        );
        $mrrRow = $database->fetchOne(
            "SELECT COALESCE(SUM(commission_value), 0) AS total
             FROM commissions
             WHERE billing_period = ? AND status IN ('charged', 'paid')",
            [$currentPeriod],
            's'
        );
        $pendingRow = $database->fetchOne(
            "SELECT COUNT(*) AS total, COALESCE(SUM(commission_value), 0) AS value
             FROM commissions
             WHERE status = 'calculated'"
        );
        $paidRow = $database->fetchOne(
            "SELECT COUNT(*) AS paid, (SELECT COUNT(*) FROM commissions) AS total
             FROM commissions
             WHERE status = 'paid'"
        );

        $monthlyRows = $database->fetchAll(
             "SELECT billing_period AS period, COALESCE(SUM(commission_value), 0) AS revenue
             FROM commissions
             WHERE billing_period >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m')
               AND status IN ('charged', 'paid')
             GROUP BY billing_period
             ORDER BY billing_period ASC"
        );
        $monthlyRevenueByPeriod = [];
        foreach ($monthlyRows as $row) {
            $monthlyRevenueByPeriod[$row['period']] = (float)$row['revenue'];
        }

        $chartLabels = [];
        $chartRevenue = [];
        $chartForecast = [];
        $lastActual = 0.0;
        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $periodDate = new DateTimeImmutable("first day of -{$monthsAgo} month");
            $period = $periodDate->format('Y-m');
            $actual = $monthlyRevenueByPeriod[$period] ?? 0.0;
            $lastActual = $actual > 0 ? $actual : $lastActual;
            $chartLabels[] = $periodDate->format('m/Y');
            $chartRevenue[] = $actual;
            $chartForecast[] = $actual > 0 ? round($actual * 1.08, 2) : round($lastActual * 1.08, 2);
        }

        $distributionRows = $database->fetchAll(
            "SELECT r.commission_type AS type, COALESCE(SUM(c.commission_value), 0) AS total
             FROM restaurants r
             LEFT JOIN commissions c ON c.restaurant_id = r.id AND c.status IN ('charged', 'paid')
             GROUP BY r.commission_type"
        );
        $distribution = ['plan_only' => 0.0, 'commission_only' => 0.0, 'hybrid' => 0.0];
        foreach ($distributionRows as $row) {
            if (array_key_exists($row['type'], $distribution)) {
                $distribution[$row['type']] = (float)$row['total'];
            }
        }

        $planRows = $database->fetchAll(
            "SELECT r.plan_type AS type, COALESCE(SUM(c.commission_value), 0) AS total
             FROM restaurants r
             LEFT JOIN commissions c ON c.restaurant_id = r.id AND c.status IN ('charged', 'paid')
             GROUP BY r.plan_type"
        );
        $monetizationByPlan = ['basic' => 0.0, 'premium' => 0.0, 'custom' => 0.0];
        foreach ($planRows as $row) {
            if (array_key_exists($row['type'], $monetizationByPlan)) {
                $monetizationByPlan[$row['type']] = (float)$row['total'];
            }
        }

        $financial = [
            'gmv' => (float)($gmvRow['total'] ?? 0),
            'net_revenue' => (float)($revenueRow['total'] ?? 0),
            'forecast' => round((float)($mrrRow['total'] ?? 0) * 1.08, 2),
            'mrr' => (float)($mrrRow['total'] ?? 0),
            'pending_invoices' => (int)($pendingRow['total'] ?? 0),
            'pending_value' => (float)($pendingRow['value'] ?? 0),
            'settlement_rate' => (int)($paidRow['total'] ?? 0) > 0
                ? round(((int)$paidRow['paid'] / (int)$paidRow['total']) * 100, 2)
                : 0.0,
            'labels' => $chartLabels,
            'revenue_series' => $chartRevenue,
            'forecast_series' => $chartForecast,
            'distribution' => $distribution,
            'plans' => $monetizationByPlan,
        ];

        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/admin-delicacy/financial.php';
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
