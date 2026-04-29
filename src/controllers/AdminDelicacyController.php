<?php
/**
 * =============================================
 * DELICACY - Controller do Admin Delicacy
 * =============================================
 * 
 * Gerencia o painel administrativo da plataforma Delicacy.
 * Este controller é restrito a usuários com role 'admin_delicacy'.
 * 
 * Rotas Web (renderizam views):
 * - GET  /admin/login     → Página de login
 * - POST /admin/login     → Processar login
 * - GET  /admin/dashboard → Dashboard (listar restaurantes)
 * - GET  /admin/logout    → Processar logout
 * 
 * Rotas API (retornam JSON):
 * - GET  /api/admin/restaurants         → Listar restaurantes
 * - GET  /api/admin/restaurants/{id}    → Detalhes de um restaurante
 * - POST /api/admin/restaurants/{id}/suspend → Suspender restaurante
 * - GET  /api/admin/metrics             → Métricas globais
 * - GET  /api/admin/logs                → Ver logs de ações
 */

namespace Delicacy\Controllers;

use Delicacy\Services\AuthService;
use Delicacy\Models\Restaurant;
use Delicacy\Models\User;
use Delicacy\Models\AdminLog;
use Delicacy\Utils\Session;
use Delicacy\Utils\Validator;

class AdminDelicacyController extends BaseController
{
    /**
     * @var AuthService Serviço de autenticação
     */
    private $authService;

    /**
     * @var Restaurant Model de restaurante
     */
    private $restaurantModel;

    /**
     * @var AdminLog Model de log administrativo
     */
    private $adminLogModel;

    /**
     * Construtor — inicializa os serviços e models necessários.
     */
    public function __construct()
    {
        $this->authService     = new AuthService();
        $this->restaurantModel = new Restaurant();
        $this->adminLogModel   = new AdminLog();
    }

    // =============================================
    // ROTAS WEB (Views)
    // =============================================

    /**
     * Exibe a página de login do Admin Delicacy.
     * Se já estiver logado como admin_delicacy, redireciona para o dashboard.
     * 
     * Rota: GET /admin/login
     */
    public function loginPage(): void
    {
        // Se já está logado como admin, redireciona para dashboard
        if ($this->authService->isAuthenticated() && $this->authService->hasRole(ROLE_ADMIN_DELICACY)) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $this->render('AdminDelicacy/login.php');
    }

    /**
     * Processa o formulário de login do Admin Delicacy.
     * 
     * Fluxo:
     * 1. Valida token CSRF
     * 2. Sanitiza e valida inputs
     * 3. Tenta login via AuthService
     * 4. Redireciona para dashboard (sucesso) ou login (erro)
     * 
     * Rota: POST /admin/login
     */
    public function loginAction(): void
    {
        // 1. Validar CSRF
        if (!$this->validateCsrf()) {
            $this->redirect('/admin/login');
            return;
        }

        // 2. Sanitizar inputs
        $email    = Validator::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? ''; // Não sanitizar senha (pode ter chars especiais)

        // 3. Validar inputs
        $validator = new Validator();
        $validator->validateRequired($email, 'email');
        $validator->validateRequired($password, 'senha');
        $validator->validateEmail($email);

        if ($validator->hasErrors()) {
            Session::setFlash('error', $validator->getFirstError());
            $this->redirect('/admin/login');
            return;
        }

        // 4. Tentar login
        $result = $this->authService->login($email, $password, ROLE_ADMIN_DELICACY);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            $this->redirect('/admin/dashboard');
        } else {
            Session::setFlash('error', $result['message']);
            $this->redirect('/admin/login');
        }
    }

    /**
     * Exibe o dashboard do Admin Delicacy.
     * Mostra a lista de todos os restaurantes cadastrados com busca.
     * 
     * Rota: GET /admin/dashboard
     * Middleware: admin_delicacy
     */
    public function dashboard(): void
    {
        // Verificar se há busca
        $searchTerm = isset($_GET['search']) ? Validator::sanitize($_GET['search']) : '';

        // Buscar restaurantes (com ou sem filtro)
        if (!empty($searchTerm)) {
            $restaurants = $this->restaurantModel->searchByName($searchTerm);
        } else {
            $restaurants = $this->restaurantModel->getAllWithOwner();
        }

        // Métricas para o dashboard
        $totalRestaurants = $this->restaurantModel->count();
        $activeRestaurants = $this->restaurantModel->count(['is_active' => RESTAURANT_ACTIVE]);

        // Logs recentes
        $recentLogs = $this->adminLogModel->getRecentLogs(10);

        $this->render('AdminDelicacy/dashboard.php', [
            'restaurants'        => $restaurants,
            'searchTerm'         => $searchTerm,
            'totalRestaurants'   => $totalRestaurants,
            'activeRestaurants'  => $activeRestaurants,
            'recentLogs'         => $recentLogs
        ]);
    }

    /**
     * Processa o logout do Admin Delicacy.
     * 
     * Rota: GET /admin/logout
     */
    public function logout(): void
    {
        $this->authService->logout();
        Session::start(); // Reinicia sessão para flash message
        Session::setFlash('success', 'Logout realizado com sucesso.');
        $this->redirect('/admin/login');
    }

    // =============================================
    // ROTAS API (JSON)
    // =============================================

    /**
     * API: Lista todos os restaurantes.
     * Suporta filtro por busca via query parameter ?search=termo
     * 
     * Rota: GET /api/admin/restaurants
     */
    public function apiListRestaurants(): void
    {
        $searchTerm = isset($_GET['search']) ? Validator::sanitize($_GET['search']) : '';

        if (!empty($searchTerm)) {
            $restaurants = $this->restaurantModel->searchByName($searchTerm);
        } else {
            $restaurants = $this->restaurantModel->getAllWithOwner();
        }

        $this->jsonResponse([
            'success' => true,
            'data'    => $restaurants,
            'total'   => count($restaurants)
        ]);
    }

    /**
     * API: Retorna detalhes de um restaurante específico.
     * 
     * Rota: GET /api/admin/restaurants/{id}
     * 
     * @param string $id ID do restaurante (vem como string da URL)
     */
    public function apiGetRestaurant(string $id): void
    {
        $restaurant = $this->restaurantModel->getById((int)$id);

        if (!$restaurant) {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Restaurante não encontrado.'
            ], HTTP_NOT_FOUND);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'data'    => $restaurant
        ]);
    }

    /**
     * API: Suspende um restaurante.
     * Registra a ação no log de auditoria.
     * 
     * Rota: POST /api/admin/restaurants/{id}/suspend
     * 
     * @param string $id ID do restaurante
     */
    public function apiSuspendRestaurant(string $id): void
    {
        $restaurantId = (int)$id;
        $restaurant = $this->restaurantModel->getById($restaurantId);

        if (!$restaurant) {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Restaurante não encontrado.'
            ], HTTP_NOT_FOUND);
            return;
        }

        // Inverte o status atual (ativo → suspenso, suspenso → ativo)
        $newStatus = $restaurant['is_active'] ? false : true;
        $success = $this->restaurantModel->updateStatus($restaurantId, $newStatus);

        if ($success) {
            // Registrar ação no log
            $adminId = Session::get('user_id');
            $action = $newStatus ? LOG_ACTION_ACTIVATE : LOG_ACTION_SUSPEND;
            $this->adminLogModel->createLog(
                $adminId,
                $action,
                LOG_ENTITY_RESTAURANT,
                $restaurantId,
                ['previous_status' => $restaurant['is_active'], 'new_status' => $newStatus ? 1 : 0]
            );

            $statusText = $newStatus ? 'ativado' : 'suspenso';
            $this->jsonResponse([
                'success' => true,
                'message' => "Restaurante {$statusText} com sucesso."
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Erro ao atualizar status do restaurante.'
            ], HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * API: Retorna métricas globais da plataforma.
     * 
     * Rota: GET /api/admin/metrics
     */
    public function apiGetMetrics(): void
    {
        $userModel = new User();

        $metrics = [
            'total_restaurants'  => $this->restaurantModel->count(),
            'active_restaurants' => $this->restaurantModel->count(['is_active' => RESTAURANT_ACTIVE]),
            'total_users'        => $userModel->count(),
            'admin_restaurants'  => $userModel->count(['role' => ROLE_ADMIN_RESTAURANT])
        ];

        $this->jsonResponse([
            'success' => true,
            'data'    => $metrics
        ]);
    }

    /**
     * API: Retorna logs de ações administrativas.
     * 
     * Rota: GET /api/admin/logs
     */
    public function apiGetLogs(): void
    {
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;
        $logs = $this->adminLogModel->getRecentLogs($limit);

        $this->jsonResponse([
            'success' => true,
            'data'    => $logs,
            'total'   => count($logs)
        ]);
    }
}
