<?php
/**
 * =============================================
 * DELICACY - Arquivo de Configuração Principal
 * =============================================
 * 
 * Este arquivo é responsável por:
 * 1. Carregar as variáveis do arquivo .env
 * 2. Definir constantes de conexão com o banco de dados
 * 3. Definir caminhos base da aplicação (BASE_PATH, BASE_URL)
 * 4. Configurar timezone, error_reporting e display_errors
 * 
 * Deve ser incluído ANTES de qualquer outro arquivo da aplicação.
 */

// =============================================
// 1. CARREGAMENTO DO ARQUIVO .ENV
// =============================================

/**
 * Carrega as variáveis de ambiente do arquivo .env na raiz do projeto.
 * Cada linha do .env no formato CHAVE=VALOR é parseada e definida
 * tanto em $_ENV quanto via putenv() para acesso global.
 * 
 * Linhas vazias e comentários (iniciando com #) são ignorados.
 * Valores entre aspas (simples ou duplas) têm as aspas removidas.
 */
$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Ignora comentários (linhas que começam com #)
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Separa a chave do valor no primeiro '='
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        
        $key   = trim($parts[0]);
        $value = trim($parts[1]);
        
        // Remove aspas ao redor do valor, se existirem
        $value = trim($value, '"\'');
        
        // Define a variável de ambiente de duas formas para garantir acesso
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

// =============================================
// 2. CAMINHOS BASE DA APLICAÇÃO
// =============================================

/**
 * BASE_PATH: Caminho absoluto do diretório raiz do projeto no sistema de arquivos.
 * Usado para includes/requires de arquivos internos.
 * 
 * Exemplo: C:\Users\...\Eng_Soft ou /var/www/delicacy
 */
define('BASE_PATH', dirname(__DIR__));

/**
 * BASE_URL: URL base da aplicação para links e redirecionamentos.
 * Carregada do .env para flexibilidade entre ambientes (dev, staging, produção).
 */
define('BASE_URL', getenv('APP_URL') ?: 'http://localhost:8000');

// =============================================
// 3. CONFIGURAÇÕES DO BANCO DE DADOS
// =============================================

/**
 * Constantes de conexão com o MySQL.
 * Valores carregados do .env para manter credenciais fora do código-fonte.
 * 
 * Raciocínio: Usar constantes em vez de variáveis globais garante
 * que os valores não possam ser alterados em runtime acidentalmente.
 */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'delicacy_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// =============================================
// 4. CONFIGURAÇÕES DA APLICAÇÃO
// =============================================

/**
 * APP_ENV: Ambiente atual da aplicação.
 * Valores possíveis: 'development', 'staging', 'production'
 * Controla comportamentos como exibição de erros e logging.
 */
define('APP_ENV', getenv('APP_ENV') ?: 'development');

/**
 * APP_DEBUG: Modo de debug ativo ou inativo.
 * Em produção DEVE ser false para não expor informações sensíveis.
 */
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));

/**
 * APP_KEY: Chave secreta da aplicação usada para hashing e tokens.
 * Deve ser uma string aleatória longa e única por instalação.
 */
define('APP_KEY', getenv('APP_KEY') ?: 'CHAVE_INSEGURA_TROCAR');

// =============================================
// 5. CONFIGURAÇÕES DE EMAIL
// =============================================

define('MAIL_FROM', getenv('MAIL_FROM') ?: 'noreply@delicacy.com.br');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Delicacy');

// =============================================
// 6. TIMEZONE
// =============================================

/**
 * Define o timezone padrão do PHP.
 * Importante para que funções como date() e timestamps
 * usem o horário correto do Brasil.
 */
$timezone = getenv('APP_TIMEZONE') ?: 'America/Sao_Paulo';
date_default_timezone_set($timezone);

// =============================================
// 7. ERROR REPORTING
// =============================================

/**
 * Raciocínio da configuração de erros por ambiente:
 * 
 * - Development: Exibe TODOS os erros na tela para facilitar debug.
 * - Production: NUNCA exibe erros na tela (risco de segurança).
 *   Erros são logados em arquivo para análise posterior.
 * 
 * Exibir erros em produção pode revelar:
 *   - Caminhos de arquivos do servidor
 *   - Nomes de tabelas e colunas do banco
 *   - Versões de software
 *   - Credenciais em mensagens de erro de conexão
 */
if (APP_ENV === 'production') {
    // Produção: reporta erros mas NÃO exibe na tela
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/logs/php_errors.log');
} else {
    // Desenvolvimento: exibe todos os erros para debug
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
}
