<?php
/**
 * DELICACY - Auth Controller
 * 
 * Gerencia login, logout e autenticação de usuários.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Restaurant.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = null;
    }

    private function getUserModel() {
        if ($this->userModel === null) {
            $this->userModel = new User();
        }

        return $this->userModel;
    }

    /**
     * Exibe página de login
     */
    public function showLoginForm() {
        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/auth/login.php';
    }

    /**
     * Processa login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        // Validar CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/login.php', 'Token de segurança inválido', 'error');
        }

        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validações básicas
        if (empty($email) || empty($password)) {
            redirectWithMessage(BASE_URL . '/login.php', 'Email e senha são obrigatórios', 'error');
        }

        try {
            // Autentica usuário
            $user = $this->getUserModel()->authenticate($email, $password);

            if (!$user) {
                redirectWithMessage(BASE_URL . '/login.php', 'Email ou senha incorretos', 'error');
            }

            // Inicia sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;

            if ($user['role'] === ROLE_ADMIN_RESTAURANT) {
                (new Restaurant())->ensureTestRestaurantForUser($user['id'], 'Teste');
            }

            // Registra login em admin_logs
            $this->logAdminAction($user['id'], 'login', 'users', $user['id']);

            // Redireciona conforme papel
            switch ($user['role']) {
                case ROLE_ADMIN_DELICACY:
                    header('Location: ' . BASE_URL . '/admin-delicacy/dashboard.php');
                    break;
                case ROLE_ADMIN_RESTAURANT:
                    header('Location: ' . BASE_URL . '/admin-contratante/');
                    break;
                default:
                    header('Location: ' . BASE_URL . '/');
            }
            exit;

        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/login.php', 'Erro ao fazer login: ' . $e->getMessage(), 'error');
        }
    }
    
    
    /**
     * Exibe página de cadastro
     */
    public function showRegisterForm() {
        $csrf_token = generateCSRFToken();
        require_once VIEWS_PATH . '/auth/register.php';
    }

    /**
     * Processa cadastro de novo usuário (restaurante)
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/register.php');
            exit;
        }

        // Validar CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/register.php', 'Token de segurança inválido', 'error');
        }

        $name = sanitizeText($_POST['name'] ?? '');
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validações
        if (empty($name) || empty($email) || empty($password)) {
            redirectWithMessage(BASE_URL . '/register.php', 'Todos os campos são obrigatórios', 'error');
        }

        if (!validateEmail($email)) {
            redirectWithMessage(BASE_URL . '/register.php', 'Email inválido', 'error');
        }

        if (strlen($password) < 8) {
            redirectWithMessage(BASE_URL . '/register.php', 'Senha deve ter no mínimo 8 caracteres', 'error');
        }

        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            redirectWithMessage(BASE_URL . '/register.php', 'Senha deve conter letras maiúscula, minúscula e número', 'error');
        }

        if ($password !== $confirm_password) {
            redirectWithMessage(BASE_URL . '/register.php', 'Senhas não conferem', 'error');
        }

        try {
            // Cria novo usuário como admin_restaurant
            $userId = $this->getUserModel()->create([
                'email' => $email,
                'name' => $name,
                'password' => $password,
                'role' => ROLE_ADMIN_RESTAURANT
            ]);

            (new Restaurant())->ensureTestRestaurantForUser($userId, 'Teste');

            redirectWithMessage(
                BASE_URL . '/login.php',
                'Cadastro realizado com sucesso! Faça login para continuar.',
                'success'
            );

        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/register.php', $e->getMessage(), 'error');
        }
    }

    /**
     * Faz logout
     */
    public function logout() {
        // Registra logout em admin_logs
        $userId = getAuthUserId();
        if ($userId) {
            $this->logAdminAction($userId, 'logout', 'users', $userId);
        }

        // Destroi sessão
        session_destroy();

        redirectWithMessage(BASE_URL . '/login.php', 'Você foi desconectado', 'success');
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
