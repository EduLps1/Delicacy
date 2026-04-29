<?php
/**
 * =============================================
 * DELICACY - Entry Point da Aplicação
 * =============================================
 * 
 * Este é o ÚNICO ponto de entrada da aplicação.
 * Todas as requisições HTTP passam por aqui.
 * 
 * Responsabilidades:
 * 1. Carregar configurações (config.php + constants.php)
 * 2. Registrar o autoloader de classes (PSR-4 simplificado)
 * 3. Iniciar a sessão segura
 * 4. Registrar todas as rotas da aplicação
 * 5. Despachar a requisição para o controller correto
 * 
 * Para funcionar corretamente, o servidor web deve direcionar
 * TODAS as requisições para este arquivo. Opções:
 * 
 * PHP Built-in Server (desenvolvimento):
 *   php -S localhost:8000 -t public
 * 
 * Apache (.htaccess na pasta public/):
 *   RewriteEngine On
 *   RewriteCond %{REQUEST_FILENAME} !-f
 *   RewriteCond %{REQUEST_FILENAME} !-d
 *   RewriteRule ^(.*)$ index.php [L,QSA]
 * 
 * Nginx:
 *   location / {
 *       try_files $uri $uri/ /index.php?$query_string;
 *   }
 */

// =============================================
// 1. CARREGAR CONFIGURAÇÕES
// =============================================

/**
 * config.php: Carrega variáveis do .env, define constantes de DB,
 * configura timezone e error_reporting.
 */
require_once dirname(__DIR__) . '/config/config.php';

/**
 * constants.php: Define todas as constantes do sistema
 * (roles, status, tipos de comissão, HTTP codes, etc.)
 */
require_once dirname(__DIR__) . '/config/constants.php';

// =============================================
// 2. AUTOLOADER DE CLASSES (PSR-4 Simplificado)
// =============================================

/**
 * Registra uma função de autoload que mapeia namespaces para diretórios.
 * 
 * Mapeamento:
 *   Delicacy\Database\    → src/database/
 *   Delicacy\Models\      → src/models/
 *   Delicacy\Services\    → src/services/
 *   Delicacy\Utils\       → src/utils/
 *   Delicacy\Controllers\ → src/controllers/
 * 
 * Raciocínio: Sem Composer (PHP puro), precisamos de um autoloader manual.
 * Seguimos a convenção PSR-4 onde o namespace corresponde à estrutura
 * de diretórios. Isso permite usar 'use' e 'new' sem require manual.
 * 
 * Exemplo: 'new Delicacy\Models\User()' carrega automaticamente
 * o arquivo 'src/models/User.php'.
 */
spl_autoload_register(function (string $class) {
    // Prefixo base do namespace do projeto
    $prefix = 'Delicacy\\';

    // Verifica se a classe pertence ao nosso namespace
    if (strpos($class, $prefix) !== 0) {
        return; // Não é uma classe Delicacy, ignora
    }

    // Remove o prefixo do namespace para obter o caminho relativo
    $relativeClass = substr($class, strlen($prefix));

    // Mapeamento de namespace para diretório
    // Raciocínio: O primeiro segmento do namespace (Database, Models, etc.)
    // é mapeado para a pasta correspondente em src/
    $namespaceMap = [
        'Database\\'    => 'src/database/',
        'Models\\'      => 'src/models/',
        'Services\\'    => 'src/services/',
        'Utils\\'       => 'src/utils/',
        'Controllers\\' => 'src/controllers/'
    ];

    foreach ($namespaceMap as $namespace => $directory) {
        if (strpos($relativeClass, $namespace) === 0) {
            // Remove o namespace intermediário e monta o caminho do arquivo
            $className = substr($relativeClass, strlen($namespace));
            $file = BASE_PATH . '/' . $directory . $className . '.php';

            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }

    // Log de debug se a classe não for encontrada
    error_log("DELICACY AUTOLOADER: Classe não encontrada: {$class}");
});

// =============================================
// 3. INICIAR SESSÃO
// =============================================

/**
 * Inicia a sessão com configurações de segurança (httponly, samesite).
 * Deve ser chamado antes de qualquer output HTTP.
 */
use Delicacy\Utils\Session;
Session::start();

// =============================================
// 4. REGISTRAR ROTAS
// =============================================

use Delicacy\Utils\Router;
use Delicacy\Controllers\AdminDelicacyController;
use Delicacy\Controllers\AdminEmpresaController;

$router = new Router();

/**
 * Rota raiz — redireciona para login do admin ou empresa.
 * Em produção, pode redirecionar para uma landing page.
 */
$router->get('/', [AdminEmpresaController::class, 'loginPage']);

// -------------------------------------------------
// ROTAS WEB: Admin Delicacy (Super Admin)
// -------------------------------------------------
// Rotas públicas (sem middleware)
$router->get('/admin/login', [AdminDelicacyController::class, 'loginPage']);
$router->post('/admin/login', [AdminDelicacyController::class, 'loginAction']);

// Rotas protegidas (middleware: admin_delicacy)
$router->get('/admin/dashboard', [AdminDelicacyController::class, 'dashboard'], 'admin_delicacy');
$router->get('/admin/logout', [AdminDelicacyController::class, 'logout'], 'admin_delicacy');

// -------------------------------------------------
// ROTAS WEB: Admin Empresa (Restaurante)
// -------------------------------------------------
// Rotas públicas (sem middleware)
$router->get('/empresa/login', [AdminEmpresaController::class, 'loginPage']);
$router->post('/empresa/login', [AdminEmpresaController::class, 'loginAction']);
$router->get('/empresa/register', [AdminEmpresaController::class, 'registerPage']);
$router->post('/empresa/register', [AdminEmpresaController::class, 'registerAction']);

// Rotas protegidas (middleware: admin_restaurant)
$router->get('/empresa/dashboard', [AdminEmpresaController::class, 'dashboard'], 'admin_restaurant');
$router->get('/empresa/logout', [AdminEmpresaController::class, 'logout'], 'admin_restaurant');

// -------------------------------------------------
// ROTAS API: Admin Delicacy
// -------------------------------------------------
$router->group('/api/admin', function (Router $router) {
    $router->get('/restaurants', [AdminDelicacyController::class, 'apiListRestaurants'], 'admin_delicacy');
    $router->get('/restaurants/{id}', [AdminDelicacyController::class, 'apiGetRestaurant'], 'admin_delicacy');
    $router->post('/restaurants/{id}/suspend', [AdminDelicacyController::class, 'apiSuspendRestaurant'], 'admin_delicacy');
    $router->get('/metrics', [AdminDelicacyController::class, 'apiGetMetrics'], 'admin_delicacy');
    $router->get('/logs', [AdminDelicacyController::class, 'apiGetLogs'], 'admin_delicacy');
});

// -------------------------------------------------
// ROTAS API: Admin Empresa (Restaurante)
// -------------------------------------------------
$router->post('/api/restaurants', [AdminEmpresaController::class, 'apiCreateRestaurant']);
$router->put('/api/restaurants/{id}', [AdminEmpresaController::class, 'apiUpdateRestaurant'], 'admin_restaurant');
$router->get('/api/restaurants/{id}', [AdminEmpresaController::class, 'apiGetRestaurant'], 'auth');
$router->get('/api/dashboard/metrics', [AdminEmpresaController::class, 'apiGetDashboardMetrics'], 'admin_restaurant');

// =============================================
// 5. DESPACHAR A REQUISIÇÃO
// =============================================

/**
 * O Router analisa a URL e método HTTP da requisição atual,
 * encontra a rota correspondente, executa o middleware (se houver),
 * e chama o controller/método apropriado.
 * 
 * Se nenhuma rota corresponder, retorna 404.
 */
$router->dispatch();
