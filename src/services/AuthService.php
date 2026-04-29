<?php
/**
 * =============================================
 * DELICACY - Serviço de Autenticação
 * =============================================
 * 
 * Centraliza TODA a lógica de autenticação do sistema.
 * Separado dos controllers seguindo o princípio de Single Responsibility:
 * - Controllers: lidam com HTTP (request/response)
 * - Services: lidam com lógica de negócio
 * 
 * Funcionalidades:
 * - Login com verificação de credenciais e role
 * - Logout com destruição segura de sessão
 * - Registro de novos usuários com validação
 * - Proteção contra brute force (limite de tentativas)
 * - Logging de ações de autenticação
 * 
 * Segurança implementada:
 * - Regeneração de session ID após login (previne Session Fixation)
 * - Limite de tentativas de login (previne brute force)
 * - Mensagens de erro genéricas (não revelam se o email existe)
 * - Verificação de status do usuário (bloqueio de inativos)
 */

namespace Delicacy\Services;

use Delicacy\Models\User;
use Delicacy\Models\AdminLog;
use Delicacy\Utils\Session;

class AuthService
{
    /**
     * @var User Instância do model de usuário
     */
    private $userModel;

    /**
     * @var AdminLog Instância do model de log administrativo
     */
    private $adminLogModel;

    /**
     * Construtor — inicializa os models necessários.
     */
    public function __construct()
    {
        $this->userModel = new User();
        $this->adminLogModel = new AdminLog();
    }

    /**
     * Realiza o login do usuário.
     * 
     * Fluxo completo:
     * 1. Verifica se há bloqueio por tentativas excessivas
     * 2. Verifica credenciais (email + senha)
     * 3. Verifica se o status do usuário é 'active'
     * 4. Verifica se a role do usuário corresponde à esperada
     * 5. Regenera o session ID (segurança)
     * 6. Armazena dados do usuário na sessão
     * 7. Registra o login no log de auditoria
     * 8. Reseta o contador de tentativas
     * 
     * Raciocínio das mensagens de erro genéricas:
     * Não informar se o email existe ou não previne enumeração de usuários.
     * Um atacante não consegue descobrir quais emails estão cadastrados.
     * 
     * @param string $email        Email do usuário
     * @param string $password     Senha em texto plano
     * @param string $expectedRole Role esperada (ex: ROLE_ADMIN_DELICACY)
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function login(string $email, string $password, string $expectedRole): array
    {
        // 1. Verificar bloqueio por tentativas excessivas
        if ($this->isLockedOut()) {
            $remainingTime = $this->getRemainingLockoutTime();
            return [
                'success' => false,
                'message' => "Muitas tentativas de login. Tente novamente em {$remainingTime} minutos.",
                'user'    => null
            ];
        }

        // 2. Verificar credenciais
        $user = $this->userModel->verifyPassword($email, $password);

        if (!$user) {
            // Incrementar contador de tentativas falhas
            $this->incrementLoginAttempts();
            return [
                'success' => false,
                'message' => 'Email ou senha incorretos.',
                'user'    => null
            ];
        }

        // 3. Verificar se o usuário está ativo
        if ($user['status'] !== USER_ACTIVE) {
            return [
                'success' => false,
                'message' => 'Sua conta está desativada. Entre em contato com o suporte.',
                'user'    => null
            ];
        }

        // 4. Verificar se a role corresponde
        if ($user['role'] !== $expectedRole) {
            return [
                'success' => false,
                'message' => 'Você não tem permissão para acessar esta área.',
                'user'    => null
            ];
        }

        // 5. Regenerar session ID (proteção contra Session Fixation)
        Session::regenerate();

        // 6. Armazenar dados do usuário na sessão
        // NOTA: Nunca armazenar a senha na sessão
        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_name', $user['name']);
        Session::set('user_role', $user['role']);
        Session::set('login_time', time());

        // 7. Atualizar último login no banco
        $this->userModel->updateLastLogin($user['id']);

        // 8. Registrar login no log de auditoria
        $this->adminLogModel->createLog(
            $user['id'],
            LOG_ACTION_LOGIN,
            LOG_ENTITY_USER,
            $user['id'],
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );

        // 9. Resetar contador de tentativas
        $this->resetLoginAttempts();

        return [
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'user'    => [
                'id'    => $user['id'],
                'email' => $user['email'],
                'name'  => $user['name'],
                'role'  => $user['role']
            ]
        ];
    }

    /**
     * Realiza o logout do usuário.
     * Registra a ação no log e destrói a sessão.
     */
    public function logout(): void
    {
        // Registrar logout no log antes de destruir a sessão
        $userId = Session::get('user_id');
        if ($userId) {
            $this->adminLogModel->createLog(
                $userId,
                LOG_ACTION_LOGOUT,
                LOG_ENTITY_USER,
                $userId
            );
        }

        // Destroi a sessão completamente
        Session::destroy();
    }

    /**
     * Verifica se o usuário atual está autenticado.
     * 
     * @return bool True se logado
     */
    public function isAuthenticated(): bool
    {
        return Session::isLoggedIn();
    }

    /**
     * Retorna os dados do usuário atualmente logado.
     * 
     * @return array|null Dados do usuário ou null se não logado
     */
    public function getCurrentUser(): ?array
    {
        if (!Session::isLoggedIn()) {
            return null;
        }

        return [
            'id'    => Session::get('user_id'),
            'email' => Session::get('user_email'),
            'name'  => Session::get('user_name'),
            'role'  => Session::get('user_role')
        ];
    }

    /**
     * Verifica se o usuário atual possui uma role específica.
     * 
     * @param string $role Role a verificar
     * @return bool True se o usuário tem a role
     */
    public function hasRole(string $role): bool
    {
        return Session::get('user_role') === $role;
    }

    // =============================================
    // PROTEÇÃO CONTRA BRUTE FORCE
    // =============================================

    /**
     * Verifica se o usuário está bloqueado por tentativas excessivas.
     * 
     * Raciocínio: Armazenar tentativas na sessão (em vez do banco)
     * é mais simples para o MVP. Limitação: limpar cookies reseta
     * o contador. Para produção, considerar armazenar no banco por IP.
     * 
     * @return bool True se bloqueado
     */
    private function isLockedOut(): bool
    {
        $attempts = Session::get('login_attempts', 0);
        $lockoutUntil = Session::get('lockout_until', 0);

        // Se há um lockout ativo e não expirou
        if ($lockoutUntil > 0 && time() < $lockoutUntil) {
            return true;
        }

        // Se o lockout expirou, resetar
        if ($lockoutUntil > 0 && time() >= $lockoutUntil) {
            $this->resetLoginAttempts();
        }

        return false;
    }

    /**
     * Incrementa o contador de tentativas de login falhas.
     * Se atingir o limite, ativa o bloqueio temporário.
     */
    private function incrementLoginAttempts(): void
    {
        $attempts = Session::get('login_attempts', 0) + 1;
        Session::set('login_attempts', $attempts);

        // Se atingiu o limite, ativar lockout
        $maxAttempts = defined('MAX_LOGIN_ATTEMPTS') ? MAX_LOGIN_ATTEMPTS : 5;
        $lockoutTime = defined('LOGIN_LOCKOUT_TIME') ? LOGIN_LOCKOUT_TIME : 900;

        if ($attempts >= $maxAttempts) {
            Session::set('lockout_until', time() + $lockoutTime);
        }
    }

    /**
     * Reseta o contador de tentativas de login.
     * Chamado após login bem-sucedido ou quando o lockout expira.
     */
    private function resetLoginAttempts(): void
    {
        Session::remove('login_attempts');
        Session::remove('lockout_until');
    }

    /**
     * Calcula o tempo restante do bloqueio em minutos.
     * 
     * @return int Minutos restantes
     */
    private function getRemainingLockoutTime(): int
    {
        $lockoutUntil = Session::get('lockout_until', 0);
        $remaining = $lockoutUntil - time();
        return max(1, (int)ceil($remaining / 60));
    }
}
