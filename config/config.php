<?php
/**
 * DELICACY - Configuração Principal
 * 
 * Arquivo de configuração centralizado.
 * Carrega variáveis do .env e define constantes da aplicação.
 */

// Define APP_ROOT primeiro (antes de incluir outros arquivos)
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(__FILE__)));
}

// Carrega variáveis de ambiente
$env_file = APP_ROOT . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
        }
    }
}

// =============================================
// CONFIGURAÇÕES DE AMBIENTE
// =============================================
define('APP_NAME', 'Delicacy');
define('APP_VERSION', '1.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('DEBUG_MODE', (getenv('DEBUG_MODE') === 'true'));

// =============================================
// CONFIGURAÇÕES DE URL E PATHS
// =============================================
define('BASE_URL', rtrim(getenv('BASE_URL') ?: 'http://localhost:8000', '/'));
define('PUBLIC_PATH', APP_ROOT . '/public');
define('SRC_PATH', APP_ROOT . '/src');
define('VIEWS_PATH', APP_ROOT . '/src/views');
define('CONFIG_PATH', APP_ROOT . '/config');

// =============================================
// CONFIGURAÇÕES DE BANCO DE DADOS
// =============================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'delicacy_db');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_CHARSET', 'utf8mb4');

// =============================================
// CONFIGURAÇÕES DE SESSÃO
// =============================================
define('SESSION_TIMEOUT', 3600); // 1 hora em segundos
define('SESSION_NAME', 'DELICACY_SESSION');

// =============================================
// CONFIGURAÇÕES DE SEGURANÇA
// =============================================
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 12);
define('CSRF_TOKEN_LENGTH', 32);

// =============================================
// CONFIGURAÇÕES DE UPLOAD
// =============================================
define('UPLOAD_DIR', PUBLIC_PATH . '/uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// =============================================
// TIMEZONE
// =============================================
date_default_timezone_set('America/Sao_Paulo');

// =============================================
// AUTOLOAD E INICIALIZAÇÃO
// =============================================
require_once CONFIG_PATH . '/constants.php';

// Inicia sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Tratamento de erros em desenvolvimento
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', APP_ROOT . '/logs/error.log');
}

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
