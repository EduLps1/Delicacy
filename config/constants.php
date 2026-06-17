<?php
/**
 * DELICACY - Constantes e Funções Globais
 *
 * Define constantes da aplicação e funções auxiliares reutilizáveis.
 */

// =============================================
// CONSTANTES DE ROLES
// =============================================
define('ROLE_ADMIN_DELICACY', 'admin_delicacy');
define('ROLE_ADMIN_RESTAURANT', 'admin_restaurant');
define('ROLE_ATTENDANT', 'attendant');
define('ROLE_CUSTOMER', 'customer');

// =============================================
// CONSTANTES DE STATUS
// =============================================
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');

// =============================================
// CONSTANTES DE COMISSÃO
// =============================================
define('COMMISSION_PLAN_ONLY', 'plan_only');
define('COMMISSION_ONLY', 'commission_only');
define('COMMISSION_HYBRID', 'hybrid');

// =============================================
// CONSTANTES DE PLANO
// =============================================
define('PLAN_BASIC', 'basic');
define('PLAN_PREMIUM', 'premium');
define('PLAN_CUSTOM', 'custom');
define('PLAN_TEST', 'test');

// =============================================
// CONSTANTES DE CARDÁPIO
// =============================================
define('MENU_TYPE_ONLINE', 'online');
define('MENU_TYPE_PRESENCIAL', 'presencial');
define('MENU_TYPE_BOTH', 'both');

// =============================================
// CONSTANTES DE PEDIDO
// =============================================
define('ORDER_STATUS_PENDING', 'pending');
define('ORDER_STATUS_CONFIRMED', 'confirmed');
define('ORDER_STATUS_PREPARING', 'preparing');
define('ORDER_STATUS_READY', 'ready');
define('ORDER_STATUS_DELIVERED', 'delivered');
define('ORDER_STATUS_CANCELLED', 'cancelled');

// =============================================
// CONSTANTES DE COMISSÃO (STATUS)
// =============================================
define('COMMISSION_STATUS_CALCULATED', 'calculated');
define('COMMISSION_STATUS_CHARGED', 'charged');
define('COMMISSION_STATUS_PAID', 'paid');

// =============================================
// FUNÇÕES AUXILIARES DE AUTENTICAÇÃO
// =============================================

/**
 * Verifica se o usuário está autenticado
 */
function isAuthenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtém os dados do usuário autenticado
 */
function getAuthUser()
{
    if (!isAuthenticated()) {
        return null;
    }
    return $_SESSION['user'] ?? null;
}

/**
 * Obtém o ID do usuário autenticado
 */
function getAuthUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Verifica o papel do usuário autenticado
 */
function hasRole($role)
{
    $user = getAuthUser();
    return $user && ($user['role'] === $role);
}

/**
 * Verifica se o usuário tem um dos papéis fornecidos
 */
function hasAnyRole(...$roles)
{
    $user = getAuthUser();
    return $user && in_array($user['role'], $roles, true);
}

/**
 * Redireciona para login se não autenticado
 */
function requireAuth()
{
    if (!isAuthenticated()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Redireciona se não tiver o papel especificado
 */
function requireRole($role)
{
    requireAuth();
    if (!hasRole($role)) {
        header('Location: ' . BASE_URL . '/access-denied.php');
        exit;
    }
}

/**
 * Redireciona se não tiver um dos papéis especificados
 */
function requireAnyRole(...$roles)
{
    requireAuth();
    if (!hasAnyRole(...$roles)) {
        header('Location: ' . BASE_URL . '/access-denied.php');
        exit;
    }
}

// =============================================
// FUNÇÕES AUXILIARES DE VALIDAÇÃO
// =============================================

/**
 * Valida email
 */
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida CNPJ (básico)
 */
function validateCNPJ($cnpj)
{
    $cnpj = preg_replace('/[^\d]/', '', (string)$cnpj);

    if (strlen($cnpj) !== 14) {
        return false;
    }

    // Validação simplificada (verificar se não é tudo igual)
    if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }

    return true;
}

/**
 * Valida telefone brasileiro
 */
function validatePhoneBR($phone)
{
    $phone = preg_replace('/[^\d]/', '', (string)$phone);
    return strlen($phone) === 11 || strlen($phone) === 10;
}

/**
 * Sanitiza entrada de texto
 */
function sanitizeText($text)
{
    return htmlspecialchars(trim((string)$text), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitiza email
 */
function sanitizeEmail($email)
{
    // FILTER_SANITIZE_EMAIL foi deprecated em versões novas, mas aqui mantemos o comportamento original.
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

/**
 * Remove formatação de CNPJ
 */
function formatCNPJ($cnpj)
{
    return preg_replace('/[^\d]/', '', (string)$cnpj);
}

// =============================================
// FUNÇÕES AUXILIARES DE HASH/SEGURANÇA
// =============================================

/**
 * Faz hash de senha
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_HASH_ALGO, [
        'cost' => PASSWORD_HASH_COST,
    ]);
}

/**
 * Verifica senha contra hash
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Gera token CSRF
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF
 */
function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], (string)$token);
}

// =============================================
// FUNÇÕES AUXILIARES DE FORMATAÇÃO
// =============================================

/**
 * Formata valor monetário
 */
function formatCurrency($value)
{
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

/**
 * Formata data/hora
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i')
{
    if (!$datetime) {
        return '-';
    }
    return date($format, strtotime($datetime));
}

/**
 * Formata data
 */
function formatDate($date, $format = 'd/m/Y')
{
    if (!$date) {
        return '-';
    }
    return date($format, strtotime($date));
}

// =============================================
// FUNÇÕES AUXILIARES DE RESPOSTA
// =============================================

/**
 * Redireciona com mensagem
 */
function redirectWithMessage($url, $message, $type = 'success')
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: ' . $url);
    exit;
}

/**
 * Obtém mensagem de sessão
 */
function getSessionMessage()
{
    if (isset($_SESSION['message'])) {
        $message = [
            'text' => $_SESSION['message'],
            'type' => $_SESSION['message_type'] ?? 'success',
        ];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return $message;
    }
    return null;
}

/**
 * Retorna resposta JSON
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code((int)$statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}




