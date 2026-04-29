<?php
/**
 * =============================================
 * DELICACY - Controller Base
 * =============================================
 * 
 * Classe abstrata com métodos auxiliares compartilhados por todos os controllers.
 * Fornece helpers para: renderização de views, respostas JSON, redirecionamentos.
 * 
 * Raciocínio: Extrair funcionalidades comuns em um BaseController evita
 * duplicação de código em AdminDelicacyController e AdminEmpresaController.
 * Ambos herdam os mesmos helpers de resposta HTTP.
 */

namespace Delicacy\Controllers;

use Delicacy\Utils\Session;

abstract class BaseController
{
    /**
     * Renderiza uma view PHP passando dados como variáveis.
     * 
     * Fluxo:
     * 1. Extrai o array $data em variáveis individuais
     *    Ex: ['name' => 'João'] cria $name = 'João' dentro da view
     * 2. Adiciona automaticamente mensagens flash e token CSRF
     * 3. Inclui o arquivo da view
     * 
     * @param string $viewPath Caminho da view relativo à pasta 'viewes/'
     * @param array  $data     Dados a passar para a view [chave => valor]
     */
    protected function render(string $viewPath, array $data = []): void
    {
        // Adiciona dados globais disponíveis em todas as views
        $data['csrf_token']    = Session::generateCsrfToken();
        $data['flash_success'] = Session::getFlash('success');
        $data['flash_error']   = Session::getFlash('error');
        $data['flash_warning'] = Session::getFlash('warning');
        $data['current_user']  = [
            'id'    => Session::get('user_id'),
            'name'  => Session::get('user_name'),
            'email' => Session::get('user_email'),
            'role'  => Session::get('user_role')
        ];

        // Extrai as variáveis do array para uso direto na view
        // EXTR_SKIP: não sobrescreve variáveis já definidas (segurança)
        extract($data, EXTR_SKIP);

        // Monta o caminho completo do arquivo da view
        $fullPath = BASE_PATH . '/viewes/' . $viewPath;

        if (file_exists($fullPath)) {
            include $fullPath;
        } else {
            error_log("DELICACY: View não encontrada: {$fullPath}");
            http_response_code(HTTP_INTERNAL_ERROR);
            echo "Erro interno: view não encontrada.";
        }
    }

    /**
     * Retorna uma resposta JSON para requisições de API.
     * Define os headers corretos e o código HTTP.
     * 
     * @param array $data     Dados a retornar como JSON
     * @param int   $httpCode Código HTTP da resposta (padrão: 200)
     */
    protected function jsonResponse(array $data, int $httpCode = HTTP_OK): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');

        // JSON_UNESCAPED_UNICODE: mantém caracteres UTF-8 legíveis
        // JSON_UNESCAPED_SLASHES: não escapa barras (URLs ficam legíveis)
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Redireciona o usuário para outra URL.
     * Usa header Location e encerra a execução.
     * 
     * @param string $url URL de destino (relativa ou absoluta)
     */
    protected function redirect(string $url): void
    {
        // Se a URL é relativa (começa com /), prepende a BASE_URL
        if (strpos($url, '/') === 0) {
            $url = BASE_URL . $url;
        }

        header("Location: {$url}");
        exit;
    }

    /**
     * Obtém dados do corpo da requisição POST.
     * Para requisições JSON (API), decodifica o body.
     * Para formulários, usa $_POST.
     * 
     * @return array Dados da requisição
     */
    protected function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // Se o content type é JSON (requisição de API)
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            return is_array($data) ? $data : [];
        }

        // Se é formulário HTML (application/x-www-form-urlencoded ou multipart)
        return $_POST;
    }

    /**
     * Valida o token CSRF da requisição POST.
     * DEVE ser chamado em todo processamento de formulário.
     * 
     * Se o token for inválido, define uma mensagem flash de erro
     * e retorna false. O controller deve então redirecionar.
     * 
     * @return bool True se o token é válido
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? '';

        if (!Session::validateCsrfToken($token)) {
            Session::setFlash('error', 'Token de segurança inválido. Tente novamente.');
            return false;
        }

        return true;
    }
}
