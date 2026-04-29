<?php
/**
 * =============================================
 * DELICACY - Controller do Admin Empresa (Contratante)
 * =============================================
 * 
 * Gerencia o painel do restaurante (admin_restaurant).
 * Este controller é restrito a usuários com role 'admin_restaurant'.
 * 
 * Rotas Web (renderizam views):
 * - GET  /empresa/login     → Página de login
 * - POST /empresa/login     → Processar login
 * - GET  /empresa/register  → Página de cadastro
 * - POST /empresa/register  → Processar cadastro
 * - GET  /empresa/dashboard → Dashboard do restaurante
 * - GET  /empresa/logout    → Processar logout
 * 
 * Rotas API (retornam JSON):
 * - POST /api/restaurants       → Criar restaurante
 * - PUT  /api/restaurants/{id}  → Editar restaurante
 * - GET  /api/restaurants/{id}  → Detalhes do restaurante
 * - GET  /api/dashboard/metrics → Métricas do restaurante
 */

namespace Delicacy\Controllers;

use Delicacy\Services\AuthService;
use Delicacy\Services\EmailService;
use Delicacy\Models\Restaurant;
use Delicacy\Models\User;
use Delicacy\Models\AdminLog;
use Delicacy\Database\Connection;
use Delicacy\Utils\Session;
use Delicacy\Utils\Validator;

class AdminEmpresaController extends BaseController
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
     * @var User Model de usuário
     */
    private $userModel;

    /**
     * @var AdminLog Model de log administrativo
     */
    private $adminLogModel;

    /**
     * Construtor — inicializa serviços e models.
     */
    public function __construct()
    {
        $this->authService     = new AuthService();
        $this->restaurantModel = new Restaurant();
        $this->userModel       = new User();
        $this->adminLogModel   = new AdminLog();
    }

    // =============================================
    // ROTAS WEB (Views)
    // =============================================

    /**
     * Exibe a página de login do restaurante.
     * Se já logado como admin_restaurant, redireciona para dashboard.
     * 
     * Rota: GET /empresa/login
     */
    public function loginPage(): void
    {
        if ($this->authService->isAuthenticated() && $this->authService->hasRole(ROLE_ADMIN_RESTAURANT)) {
            $this->redirect('/empresa/dashboard');
            return;
        }

        $this->render('AdminEmpresa/login.php');
    }

    /**
     * Processa o login do restaurante.
     * 
     * Rota: POST /empresa/login
     */
    public function loginAction(): void
    {
        // 1. Validar CSRF
        if (!$this->validateCsrf()) {
            $this->redirect('/empresa/login');
            return;
        }

        // 2. Sanitizar inputs
        $email    = Validator::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // 3. Validar inputs
        $validator = new Validator();
        $validator->validateRequired($email, 'email');
        $validator->validateRequired($password, 'senha');
        $validator->validateEmail($email);

        if ($validator->hasErrors()) {
            Session::setFlash('error', $validator->getFirstError());
            $this->redirect('/empresa/login');
            return;
        }

        // 4. Tentar login
        $result = $this->authService->login($email, $password, ROLE_ADMIN_RESTAURANT);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            $this->redirect('/empresa/dashboard');
        } else {
            Session::setFlash('error', $result['message']);
            $this->redirect('/empresa/login');
        }
    }

    /**
     * Exibe a página de cadastro de restaurante.
     * 
     * Rota: GET /empresa/register
     */
    public function registerPage(): void
    {
        // Se já está logado, redireciona para dashboard
        if ($this->authService->isAuthenticated() && $this->authService->hasRole(ROLE_ADMIN_RESTAURANT)) {
            $this->redirect('/empresa/dashboard');
            return;
        }

        $this->render('AdminEmpresa/register.php');
    }

    /**
     * Processa o cadastro de um novo restaurante.
     * 
     * Fluxo completo:
     * 1. Valida token CSRF
     * 2. Sanitiza todos os inputs
     * 3. Valida todos os campos (email, CNPJ, telefone, senha)
     * 4. Verifica duplicidade de email e CNPJ
     * 5. Cria User + Restaurant em TRANSAÇÃO atômica
     * 6. Envia email de confirmação
     * 7. Registra no log de auditoria
     * 
     * Raciocínio da transação:
     * Se a criação do restaurante falhar, o usuário também não deve
     * ser criado. Sem transação, teríamos um usuário órfão no banco.
     * 
     * Rota: POST /empresa/register
     */
    public function registerAction(): void
    {
        // 1. Validar CSRF
        if (!$this->validateCsrf()) {
            $this->redirect('/empresa/register');
            return;
        }

        // 2. Sanitizar inputs
        $name            = Validator::sanitize($_POST['name'] ?? '');
        $email           = Validator::sanitize($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $restaurantName  = Validator::sanitize($_POST['restaurant_name'] ?? '');
        $phone           = Validator::sanitizePhone($_POST['phone'] ?? '');
        $cnpj            = Validator::sanitizeCNPJ($_POST['cnpj'] ?? '');
        $restaurantEmail = Validator::sanitize($_POST['restaurant_email'] ?? '');

        // 3. Validar todos os campos
        $validator = new Validator();
        $validator->validateRequired($name, 'nome');
        $validator->validateMinLength($name, 3, 'nome');
        $validator->validateEmail($email);
        $validator->validatePassword($password);
        $validator->validateRequired($restaurantName, 'nome do restaurante');
        $validator->validatePhone($phone);
        $validator->validateCNPJ($cnpj);

        // Validar email do restaurante se informado
        if (!empty($restaurantEmail)) {
            $validator->validateEmail($restaurantEmail);
        }

        // Validar confirmação de senha
        if ($password !== $passwordConfirm) {
            $validator->addError('As senhas não conferem.');
        }

        // Se há erros de validação, retorna com os erros
        if ($validator->hasErrors()) {
            Session::setFlash('error', implode('<br>', $validator->getErrors()));
            $this->redirect('/empresa/register');
            return;
        }

        // 4. Verificar duplicidade
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser) {
            Session::setFlash('error', 'Este email já está cadastrado.');
            $this->redirect('/empresa/register');
            return;
        }

        $existingRestaurant = $this->restaurantModel->findByCnpj($cnpj);
        if ($existingRestaurant) {
            Session::setFlash('error', 'Este CNPJ já está cadastrado na plataforma.');
            $this->redirect('/empresa/register');
            return;
        }

        // 5. Criar User + Restaurant em transação
        $connection = Connection::getInstance();
        $connection->beginTransaction();

        try {
            // Criar o usuário (admin_restaurant)
            $userId = $this->userModel->createUser($email, $password, $name, ROLE_ADMIN_RESTAURANT);

            if (!$userId) {
                throw new \Exception('Erro ao criar o usuário.');
            }

            // Criar o restaurante vinculado ao usuário
            $emailRestaurante = !empty($restaurantEmail) ? $restaurantEmail : $email;
            $restaurantId = $this->restaurantModel->createRestaurant(
                $userId,
                $restaurantName,
                $emailRestaurante,
                $phone,
                $cnpj
            );

            if (!$restaurantId) {
                throw new \Exception('Erro ao criar o restaurante.');
            }

            // Confirmar transação — ambos criados com sucesso
            $connection->commit();

            // 6. Enviar email de confirmação (fora da transação)
            $emailService = new EmailService();
            $emailService->sendConfirmationEmail($email, $restaurantName);

            // 7. Registrar no log
            $this->adminLogModel->createLog(
                $userId,
                LOG_ACTION_CREATE,
                LOG_ENTITY_RESTAURANT,
                $restaurantId,
                ['restaurant_name' => $restaurantName, 'cnpj' => $cnpj]
            );

            Session::setFlash('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
            $this->redirect('/empresa/login');

        } catch (\Exception $e) {
            // Desfazer tudo se qualquer etapa falhar
            $connection->rollback();
            error_log("DELICACY: Erro no registro - " . $e->getMessage());
            Session::setFlash('error', 'Erro ao realizar o cadastro. Tente novamente.');
            $this->redirect('/empresa/register');
        }
    }

    /**
     * Exibe o dashboard do restaurante.
     * Mostra dados do restaurante e opções de gerenciamento.
     * 
     * Rota: GET /empresa/dashboard
     * Middleware: admin_restaurant
     */
    public function dashboard(): void
    {
        $userId = Session::get('user_id');
        $restaurant = $this->restaurantModel->findByUserId($userId);

        $this->render('AdminEmpresa/dashboard.php', [
            'restaurant' => $restaurant
        ]);
    }

    /**
     * Processa o logout do restaurante.
     * 
     * Rota: GET /empresa/logout
     */
    public function logout(): void
    {
        $this->authService->logout();
        Session::start();
        Session::setFlash('success', 'Logout realizado com sucesso.');
        $this->redirect('/empresa/login');
    }

    // =============================================
    // ROTAS API (JSON)
    // =============================================

    /**
     * API: Cria um novo restaurante.
     * 
     * Rota: POST /api/restaurants
     */
    public function apiCreateRestaurant(): void
    {
        $data = $this->getRequestData();

        // Validar dados
        $validator = new Validator();
        $validator->validateRequired($data['name'] ?? '', 'nome');
        $validator->validateRequired($data['email'] ?? '', 'email');
        $validator->validateEmail($data['email'] ?? '');
        $validator->validateRequired($data['password'] ?? '', 'senha');
        $validator->validatePassword($data['password'] ?? '');
        $validator->validateRequired($data['restaurant_name'] ?? '', 'nome do restaurante');
        $validator->validatePhone($data['phone'] ?? '');
        $validator->validateCNPJ($data['cnpj'] ?? '');

        if ($validator->hasErrors()) {
            $this->jsonResponse([
                'success' => false,
                'errors'  => $validator->getErrors()
            ], HTTP_BAD_REQUEST);
            return;
        }

        // Verificar duplicidade
        $existingUser = $this->userModel->findByEmail(Validator::sanitize($data['email']));
        if ($existingUser) {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Este email já está cadastrado.'
            ], HTTP_CONFLICT);
            return;
        }

        $cnpj = Validator::sanitizeCNPJ($data['cnpj']);
        $existingRestaurant = $this->restaurantModel->findByCnpj($cnpj);
        if ($existingRestaurant) {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Este CNPJ já está cadastrado.'
            ], HTTP_CONFLICT);
            return;
        }

        // Criar em transação
        $connection = Connection::getInstance();
        $connection->beginTransaction();

        try {
            $userId = $this->userModel->createUser(
                Validator::sanitize($data['email']),
                $data['password'],
                Validator::sanitize($data['name']),
                ROLE_ADMIN_RESTAURANT
            );

            if (!$userId) {
                throw new \Exception('Erro ao criar usuário.');
            }

            $restaurantEmail = !empty($data['restaurant_email']) 
                ? Validator::sanitize($data['restaurant_email']) 
                : Validator::sanitize($data['email']);

            $restaurantId = $this->restaurantModel->createRestaurant(
                $userId,
                Validator::sanitize($data['restaurant_name']),
                $restaurantEmail,
                Validator::sanitizePhone($data['phone']),
                $cnpj
            );

            if (!$restaurantId) {
                throw new \Exception('Erro ao criar restaurante.');
            }

            $connection->commit();

            // Email de confirmação
            $emailService = new EmailService();
            $emailService->sendConfirmationEmail($data['email'], $data['restaurant_name']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Restaurante cadastrado com sucesso!',
                'data'    => [
                    'user_id'       => $userId,
                    'restaurant_id' => $restaurantId
                ]
            ], HTTP_CREATED);

        } catch (\Exception $e) {
            $connection->rollback();
            error_log("DELICACY API: Erro ao criar restaurante - " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Erro interno ao cadastrar restaurante.'
            ], HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * API: Atualiza dados de um restaurante.
     * 
     * Rota: PUT /api/restaurants/{id}
     * 
     * @param string $id ID do restaurante
     */
    public function apiUpdateRestaurant(string $id): void
    {
        $restaurantId = (int)$id;
        $userId = Session::get('user_id');

        // Verificar se o restaurante pertence ao usuário logado
        $restaurant = $this->restaurantModel->getById($restaurantId);
        if (!$restaurant || $restaurant['user_id'] != $userId) {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Restaurante não encontrado ou acesso negado.'
            ], HTTP_FORBIDDEN);
            return;
        }

        $data = $this->getRequestData();
        $updateData = [];

        // Atualiza apenas os campos enviados (PATCH-like)
        if (isset($data['name'])) {
            $updateData['name'] = Validator::sanitize($data['name']);
        }
        if (isset($data['description'])) {
            $updateData['description'] = Validator::sanitize($data['description']);
        }
        if (isset($data['phone'])) {
            $updateData['phone'] = Validator::sanitizePhone($data['phone']);
        }
        if (isset($data['email'])) {
            $updateData['email'] = Validator::sanitize($data['email']);
        }

        if (empty($updateData)) {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Nenhum dado para atualizar.'
            ], HTTP_BAD_REQUEST);
            return;
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');
        $success = $this->restaurantModel->update($restaurantId, $updateData);

        if ($success) {
            // Log da alteração
            $this->adminLogModel->createLog(
                $userId,
                LOG_ACTION_UPDATE,
                LOG_ENTITY_RESTAURANT,
                $restaurantId,
                ['updated_fields' => array_keys($updateData)]
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Restaurante atualizado com sucesso!'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Erro ao atualizar restaurante.'
            ], HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * API: Retorna detalhes do restaurante do usuário logado.
     * 
     * Rota: GET /api/restaurants/{id}
     * 
     * @param string $id ID do restaurante
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

        // Verificar se o usuário tem acesso (é dono ou admin_delicacy)
        $userRole = Session::get('user_role');
        $userId = Session::get('user_id');

        if ($userRole !== ROLE_ADMIN_DELICACY && $restaurant['user_id'] != $userId) {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Acesso negado.'
            ], HTTP_FORBIDDEN);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'data'    => $restaurant
        ]);
    }

    /**
     * API: Retorna métricas do dashboard do restaurante.
     * 
     * Rota: GET /api/dashboard/metrics
     */
    public function apiGetDashboardMetrics(): void
    {
        $userId = Session::get('user_id');
        $restaurant = $this->restaurantModel->findByUserId($userId);

        if (!$restaurant) {
            $this->jsonResponse([
                'success' => false,
                'error'   => 'Restaurante não encontrado.'
            ], HTTP_NOT_FOUND);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'data'    => [
                'restaurant_name'  => $restaurant['name'],
                'is_active'        => (bool)$restaurant['is_active'],
                'commission_type'  => $restaurant['commission_type'],
                'commission_rate'  => $restaurant['active_commission_rate'],
                'plan_type'        => $restaurant['plan_type'],
                'total_revenue'    => $restaurant['total_revenue']
            ]
        ]);
    }
}
