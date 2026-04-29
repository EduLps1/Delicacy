<?php
/**
 * =============================================
 * DELICACY - Sistema de Rotas (Router)
 * =============================================
 * 
 * Implementa um sistema de rotas simples para PHP puro.
 * Mapeia URLs para controllers/métodos específicos.
 * 
 * Funcionalidades:
 * - Registro de rotas GET, POST, PUT e DELETE
 * - Suporte a parâmetros dinâmicos na URL (ex: /api/restaurants/{id})
 * - Middleware de autenticação por rota
 * - Dispatch automático para o controller correto
 * - Resposta 404 para rotas não encontradas
 * - Suporte a method override via _method (PUT/DELETE em formulários HTML)
 * 
 * Raciocínio: Sem um Router, cada funcionalidade precisaria de um arquivo
 * PHP separado (login.php, dashboard.php, etc.), gerando duplicação de
 * código de inicialização e dificultando manutenção. O Router centraliza
 * a lógica de roteamento em um único ponto de entrada (index.php).
 * 
 * Uso:
 *   $router = new Router();
 *   $router->get('/admin/login', [AdminDelicacyController::class, 'loginPage']);
 *   $router->post('/admin/login', [AdminDelicacyController::class, 'loginAction']);
 *   $router->dispatch();
 */

namespace Delicacy\Utils;

class Router
{
    /**
     * @var array Armazena todas as rotas registradas, agrupadas por método HTTP.
     * Estrutura: ['GET' => ['/path' => ['handler' => callable, 'middleware' => string|null]], ...]
     */
    private $routes = [];

    /**
     * @var string Prefixo de grupo de rotas (para agrupamento hierárquico).
     * Usado internamente pelo método group().
     */
    private $groupPrefix = '';

    // =============================================
    // REGISTRO DE ROTAS
    // =============================================

    /**
     * Registra uma rota GET.
     * Usado para: exibição de páginas, consulta de dados (API).
     * 
     * @param string $path       Caminho da rota (ex: '/admin/dashboard')
     * @param array  $handler    [ClasseController::class, 'nomeDoMetodo']
     * @param string|null $middleware Middleware de autenticação (opcional)
     * @return self Para encadeamento (method chaining)
     */
    public function get(string $path, array $handler, ?string $middleware = null): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Registra uma rota POST.
     * Usado para: envio de formulários, criação de recursos (API).
     * 
     * @param string $path       Caminho da rota
     * @param array  $handler    [ClasseController::class, 'nomeDoMetodo']
     * @param string|null $middleware Middleware de autenticação (opcional)
     * @return self
     */
    public function post(string $path, array $handler, ?string $middleware = null): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Registra uma rota PUT.
     * Usado para: atualização de recursos (API).
     * 
     * @param string $path       Caminho da rota
     * @param array  $handler    [ClasseController::class, 'nomeDoMetodo']
     * @param string|null $middleware Middleware de autenticação (opcional)
     * @return self
     */
    public function put(string $path, array $handler, ?string $middleware = null): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Registra uma rota DELETE.
     * Usado para: remoção de recursos (API).
     * 
     * @param string $path       Caminho da rota
     * @param array  $handler    [ClasseController::class, 'nomeDoMetodo']
     * @param string|null $middleware Middleware de autenticação (opcional)
     * @return self
     */
    public function delete(string $path, array $handler, ?string $middleware = null): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Agrupa rotas sob um prefixo comum.
     * Evita repetição do prefixo em cada rota individual.
     * 
     * Exemplo:
     *   $router->group('/api/admin', function($router) {
     *       $router->get('/restaurants', [...]);  // Registra /api/admin/restaurants
     *       $router->get('/logs', [...]);         // Registra /api/admin/logs
     *   });
     * 
     * @param string   $prefix   Prefixo do grupo (ex: '/api/admin')
     * @param callable $callback Função que recebe o router para registrar sub-rotas
     */
    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $this->groupPrefix .= $prefix;

        $callback($this);

        // Restaura o prefixo anterior (permite grupos aninhados)
        $this->groupPrefix = $previousPrefix;
    }

    /**
     * Método interno para adicionar uma rota ao registro.
     * 
     * @param string      $method     Método HTTP (GET, POST, PUT, DELETE)
     * @param string      $path       Caminho da rota
     * @param array       $handler    [Classe, 'metodo']
     * @param string|null $middleware Nome do middleware a aplicar
     * @return self
     */
    private function addRoute(string $method, string $path, array $handler, ?string $middleware): self
    {
        $fullPath = $this->groupPrefix . $path;
        $this->routes[$method][$fullPath] = [
            'handler'    => $handler,
            'middleware'  => $middleware
        ];
        return $this;
    }

    // =============================================
    // DISPATCH (DESPACHO DA REQUISIÇÃO)
    // =============================================

    /**
     * Processa a requisição HTTP atual e direciona para o controller correto.
     * 
     * Fluxo do dispatch:
     * 1. Obtém o método HTTP e a URI da requisição
     * 2. Suporta method override via POST + _method (para PUT/DELETE em forms HTML)
     * 3. Busca a rota correspondente (primeiro rotas exatas, depois com parâmetros)
     * 4. Executa o middleware se definido (ex: verificar autenticação)
     * 5. Instancia o controller e chama o método
     * 6. Se nenhuma rota corresponder, retorna 404
     */
    public function dispatch(): void
    {
        // Obtém o método HTTP da requisição
        $method = $_SERVER['REQUEST_METHOD'];

        // Suporte a method override via formulário HTML
        // Raciocínio: Forms HTML suportam apenas GET e POST.
        // Para PUT/DELETE, usamos um campo hidden '_method' no POST.
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'DELETE'])) {
                $method = $override;
            }
        }

        // Obtém e limpa a URI (remove query string e normaliza)
        $uri = $this->getCleanUri();

        // Tenta encontrar e executar a rota correspondente
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $config) {
                $params = $this->matchRoute($route, $uri);

                if ($params !== false) {
                    // Rota encontrada — executar middleware se definido
                    if ($config['middleware'] !== null) {
                        if (!$this->executeMiddleware($config['middleware'])) {
                            return; // Middleware bloqueou a requisição
                        }
                    }

                    // Instancia o controller e chama o método
                    $controllerClass = $config['handler'][0];
                    $controllerMethod = $config['handler'][1];

                    $controller = new $controllerClass();

                    // Passa os parâmetros da URL como argumentos do método
                    call_user_func_array([$controller, $controllerMethod], $params);
                    return;
                }
            }
        }

        // Nenhuma rota encontrada — retorna 404
        $this->handleNotFound();
    }

    /**
     * Obtém a URI limpa da requisição.
     * Remove a query string (?param=value) e normaliza barras.
     * 
     * @return string URI limpa (ex: '/admin/dashboard')
     */
    private function getCleanUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove a query string (tudo após ?)
        $uri = parse_url($uri, PHP_URL_PATH);

        // Remove barra final (exceto para a raiz '/')
        $uri = rtrim($uri, '/');

        // URI vazia vira '/'
        return $uri ?: '/';
    }

    /**
     * Tenta fazer match de uma rota registrada com a URI atual.
     * Suporta parâmetros dinâmicos no formato {nome}.
     * 
     * Exemplo:
     *   Rota registrada: '/api/restaurants/{id}'
     *   URI atual:       '/api/restaurants/42'
     *   Retorna:         ['id' => '42']
     * 
     * @param string $route Padrão da rota registrada
     * @param string $uri   URI da requisição atual
     * @return array|false Array de parâmetros extraídos ou false se não houver match
     */
    private function matchRoute(string $route, string $uri)
    {
        // Normaliza barras finais para comparação
        $route = rtrim($route, '/') ?: '/';

        // Match exato (sem parâmetros dinâmicos)
        if ($route === $uri) {
            return [];
        }

        // Match com parâmetros dinâmicos ({id}, {slug}, etc.)
        // Converte {param} em grupo de captura regex
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Filtra apenas os matches nomeados (remove os numéricos)
            $params = array_filter($matches, function ($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);

            return $params;
        }

        return false;
    }

    // =============================================
    // MIDDLEWARE
    // =============================================

    /**
     * Executa o middleware associado à rota.
     * Middleware são verificações que devem passar antes do controller ser executado.
     * 
     * Middlewares disponíveis:
     * - 'auth': Verifica se o usuário está logado
     * - 'admin_delicacy': Verifica se é admin Delicacy
     * - 'admin_restaurant': Verifica se é admin do restaurante
     * 
     * @param string $middleware Nome do middleware
     * @return bool True se o middleware permitiu continuar
     */
    private function executeMiddleware(string $middleware): bool
    {
        switch ($middleware) {
            case 'auth':
                // Verifica se há sessão de usuário ativa
                if (!Session::isLoggedIn()) {
                    $this->redirectToLogin();
                    return false;
                }
                return true;

            case 'admin_delicacy':
                // Verifica se está logado E é admin Delicacy
                if (!Session::isLoggedIn() || Session::get('user_role') !== ROLE_ADMIN_DELICACY) {
                    $this->redirectToLogin('/admin/login');
                    return false;
                }
                return true;

            case 'admin_restaurant':
                // Verifica se está logado E é admin do restaurante
                if (!Session::isLoggedIn() || Session::get('user_role') !== ROLE_ADMIN_RESTAURANT) {
                    $this->redirectToLogin('/empresa/login');
                    return false;
                }
                return true;

            default:
                // Middleware desconhecido — bloqueia por segurança
                error_log("DELICACY: Middleware desconhecido: {$middleware}");
                http_response_code(HTTP_INTERNAL_ERROR);
                echo json_encode(['error' => 'Erro interno do servidor']);
                return false;
        }
    }

    /**
     * Redireciona para a página de login apropriada.
     * Se a requisição é API (espera JSON), retorna 401 em vez de redirecionar.
     * 
     * @param string $loginUrl URL de login (padrão: /admin/login)
     */
    private function redirectToLogin(string $loginUrl = '/admin/login'): void
    {
        $uri = $this->getCleanUri();

        // Se é uma requisição de API, retorna JSON 401 em vez de redirecionar
        if (strpos($uri, '/api/') === 0) {
            http_response_code(HTTP_UNAUTHORIZED);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error'   => 'Autenticação necessária. Faça login para continuar.'
            ]);
            return;
        }

        // Para requisições web, redireciona para a página de login
        Session::setFlash('error', 'Você precisa estar logado para acessar esta página.');
        header("Location: " . BASE_URL . $loginUrl);
        exit;
    }

    /**
     * Trata requisições para rotas não encontradas (404).
     * Para API retorna JSON, para web carrega a view de erro.
     */
    private function handleNotFound(): void
    {
        http_response_code(HTTP_NOT_FOUND);

        $uri = $this->getCleanUri();

        // API: retorna JSON
        if (strpos($uri, '/api/') === 0) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error'   => 'Recurso não encontrado.'
            ]);
            return;
        }

        // Web: carrega a view de 404
        $errorView = BASE_PATH . '/viewes/Erros/404.php';
        if (file_exists($errorView)) {
            include $errorView;
        } else {
            echo '<h1>404 - Página não encontrada</h1>';
        }
    }
}
