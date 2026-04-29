<?php
/**
 * =============================================
 * DELICACY - Gerenciamento Seguro de Sessão
 * =============================================
 * 
 * Responsável por:
 * 1. Iniciar sessões com configurações de segurança (httponly, samesite)
 * 2. Armazenar e recuperar dados da sessão
 * 3. Gerar e validar tokens CSRF (proteção contra Cross-Site Request Forgery)
 * 4. Gerenciar mensagens flash (feedback entre redirects)
 * 5. Regenerar ID de sessão após login (proteção contra Session Fixation)
 * 
 * CSRF - O que é e por que proteger:
 * Um atacante pode criar um formulário em outro site que envia dados
 * para o nosso. O token CSRF garante que o formulário veio do nosso site.
 * 
 * Session Fixation - O que é:
 * Um atacante força o usuário a usar um session ID conhecido.
 * Após login, regeneramos o ID para invalidar o ID fixado.
 */

namespace Delicacy\Utils;

class Session
{
    /**
     * Inicia a sessão PHP com configurações de segurança.
     * Deve ser chamado UMA VEZ no início de cada requisição (em index.php).
     * 
     * Configurações de segurança aplicadas:
     * - cookie_httponly: Impede acesso ao cookie de sessão via JavaScript (previne XSS)
     * - cookie_samesite: 'Strict' impede envio do cookie em requisições cross-site (previne CSRF)
     * - cookie_secure: Em produção, exige HTTPS (previne interceptação)
     * - use_strict_mode: Rejeita session IDs não inicializados pelo servidor
     * - use_only_cookies: Impede session ID via URL (previne Session Fixation)
     */
    public static function start(): void
    {
        // Evita iniciar sessão duplicada
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Configura parâmetros de segurança do cookie de sessão
        $isProduction = defined('APP_ENV') && APP_ENV === 'production';

        session_set_cookie_params([
            'lifetime' => 0,                      // Cookie expira ao fechar o navegador
            'path'     => '/',                     // Cookie válido para todo o site
            'domain'   => '',                      // Domínio atual
            'secure'   => $isProduction,           // HTTPS obrigatório em produção
            'httponly'  => true,                    // Bloqueia acesso via JavaScript
            'samesite'  => 'Strict'                // Bloqueia envio cross-site
        ]);

        // Configurações adicionais de segurança
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        // Nome customizado do cookie (esconde que usamos PHP)
        session_name('DELICACY_SESSION');

        session_start();
    }

    /**
     * Armazena um valor na sessão.
     * 
     * @param string $key   Chave identificadora
     * @param mixed  $value Valor a armazenar
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Recupera um valor da sessão.
     * 
     * @param string $key     Chave identificadora
     * @param mixed  $default Valor padrão se a chave não existir
     * @return mixed Valor armazenado ou o valor padrão
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica se uma chave existe na sessão.
     * 
     * @param string $key Chave a verificar
     * @return bool True se existe
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove uma chave da sessão.
     * 
     * @param string $key Chave a remover
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destrói a sessão completamente.
     * Remove todos os dados e invalida o cookie de sessão.
     * Usado no logout.
     */
    public static function destroy(): void
    {
        // Limpa todos os dados da sessão
        $_SESSION = [];

        // Remove o cookie de sessão do navegador
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destrói a sessão no servidor
        session_destroy();
    }

    /**
     * Regenera o ID da sessão mantendo os dados.
     * DEVE ser chamado após login bem-sucedido para prevenir Session Fixation.
     * 
     * Raciocínio: Se um atacante definiu o session ID antes do login,
     * ao regenerar, o ID antigo se torna inválido e o atacante
     * perde acesso à sessão autenticada.
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true); // true = deleta a sessão antiga
    }

    /**
     * Verifica se o usuário está logado.
     * Um usuário está logado se existir um user_id na sessão.
     * 
     * @return bool True se logado
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // =============================================
    // PROTEÇÃO CSRF (Cross-Site Request Forgery)
    // =============================================

    /**
     * Gera um token CSRF e armazena na sessão.
     * O token deve ser incluído em todos os formulários POST como campo hidden.
     * 
     * Exemplo de uso no formulário:
     *   <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
     * 
     * @return string Token CSRF (64 caracteres hexadecimais)
     */
    public static function generateCsrfToken(): string
    {
        // Gera 32 bytes aleatórios e converte para hexadecimal (64 chars)
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;

        return $token;
    }

    /**
     * Valida o token CSRF enviado no formulário contra o armazenado na sessão.
     * Usa hash_equals() para comparação time-safe (previne timing attacks).
     * 
     * @param string $token Token recebido do formulário
     * @return bool True se o token é válido
     */
    public static function validateCsrfToken(string $token): bool
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // hash_equals() previne timing attacks na comparação
        // Raciocínio: strcmp() normal pode revelar informação pelo tempo de execução
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // =============================================
    // MENSAGENS FLASH
    // =============================================

    /**
     * Define uma mensagem flash — mensagem exibida UMA VEZ após um redirect.
     * 
     * Fluxo típico:
     * 1. Controller processa formulário
     * 2. Define flash: Session::setFlash('success', 'Cadastro realizado!')
     * 3. Redireciona para outra página
     * 4. Na próxima página, getFlash() retorna a mensagem e a remove
     * 
     * @param string $key  Tipo da mensagem (success, error, warning, info)
     * @param string $message Texto da mensagem
     */
    public static function setFlash(string $key, string $message): void
    {
        $_SESSION['flash_messages'][$key] = $message;
    }

    /**
     * Recupera e REMOVE uma mensagem flash.
     * Retorna null se não houver mensagem do tipo solicitado.
     * 
     * @param string $key Tipo da mensagem
     * @return string|null Mensagem ou null
     */
    public static function getFlash(string $key): ?string
    {
        if (isset($_SESSION['flash_messages'][$key])) {
            $message = $_SESSION['flash_messages'][$key];
            unset($_SESSION['flash_messages'][$key]);
            return $message;
        }

        return null;
    }

    /**
     * Verifica se existe uma mensagem flash de determinado tipo.
     * 
     * @param string $key Tipo da mensagem
     * @return bool True se existe
     */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['flash_messages'][$key]);
    }
}
