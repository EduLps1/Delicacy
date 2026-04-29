<?php
/**
 * =============================================
 * DELICACY - Classe de Conexão com o Banco de Dados
 * =============================================
 * 
 * Implementa o padrão Singleton para garantir que apenas UMA conexão
 * com o MySQL seja aberta por requisição HTTP.
 * 
 * Raciocínio do padrão Singleton:
 * - Evita overhead de múltiplas conexões simultâneas
 * - Garante consistência (todos os queries usam a mesma conexão)
 * - Facilita gerenciamento de transações
 * 
 * Uso:
 *   $db = Connection::getInstance()->getConnection();
 *   $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
 */

namespace Delicacy\Database;

class Connection
{
    /**
     * @var Connection|null Instância única da classe (Singleton)
     */
    private static $instance = null;

    /**
     * @var \mysqli Objeto de conexão com o MySQL
     */
    private $connection;

    /**
     * Construtor privado — impede criação de instância via 'new Connection()'.
     * A conexão é estabelecida aqui, no momento da primeira chamada.
     * 
     * Configurações de segurança aplicadas:
     * - charset utf8mb4: suporte completo a Unicode (previne problemas de encoding)
     * - MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT: erros de MySQL lançam exceções
     *   em vez de falhar silenciosamente
     * 
     * @throws \Exception Se a conexão com o banco falhar
     */
    private function __construct()
    {
        // Ativa o modo de relatório de erros do mysqli como exceções
        // Raciocínio: Sem isso, erros de SQL são silenciosos e difíceis de debugar
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // Cria a conexão com o MySQL usando as constantes definidas em config.php
            $this->connection = new \mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME,
                (int)DB_PORT
            );

            // Define o charset para utf8mb4
            // Raciocínio: utf8mb4 é o charset correto para suporte completo a Unicode
            // O utf8 padrão do MySQL suporta apenas 3 bytes (não suporta emojis)
            $this->connection->set_charset('utf8mb4');

        } catch (\mysqli_sql_exception $e) {
            // Em produção, NÃO expor detalhes do erro de conexão ao usuário
            // Detalhes do erro vão para o log do servidor
            error_log("DELICACY DB ERROR: Falha na conexão - " . $e->getMessage());

            if (APP_DEBUG) {
                throw new \Exception("Erro ao conectar com o banco de dados: " . $e->getMessage());
            } else {
                throw new \Exception("Erro interno do servidor. Tente novamente mais tarde.");
            }
        }
    }

    /**
     * Retorna a instância única da Connection (Singleton).
     * Na primeira chamada, cria a instância. Nas próximas, retorna a existente.
     * 
     * @return Connection Instância única
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retorna o objeto mysqli para executar queries.
     * 
     * @return \mysqli Conexão ativa com o MySQL
     */
    public function getConnection(): \mysqli
    {
        return $this->connection;
    }

    /**
     * Inicia uma transação no banco de dados.
     * Usado quando múltiplas operações devem ser atômicas
     * (ex: criar user + restaurant deve funcionar tudo ou nada).
     */
    public function beginTransaction(): void
    {
        $this->connection->begin_transaction();
    }

    /**
     * Confirma a transação atual (aplica todas as alterações).
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * Desfaz a transação atual (reverte todas as alterações).
     * Chamado quando ocorre um erro em qualquer etapa da transação.
     */
    public function rollback(): void
    {
        $this->connection->rollback();
    }

    /**
     * Fecha a conexão com o banco de dados.
     * Chamado automaticamente no final da requisição, mas pode ser
     * invocado manualmente se necessário.
     */
    public function closeConnection(): void
    {
        if ($this->connection) {
            $this->connection->close();
            self::$instance = null;
        }
    }

    /**
     * Impede clonagem da instância (requisito do Singleton).
     * Sem isso, alguém poderia clonar a instância e quebrar o padrão.
     */
    private function __clone() {}

    /**
     * Impede desserialização da instância (requisito do Singleton).
     * Sem isso, a instância poderia ser recriada via unserialize().
     * 
     * @throws \Exception Sempre lança exceção se alguém tentar desserializar
     */
    public function __wakeup()
    {
        throw new \Exception("Não é permitido desserializar a conexão com o banco.");
    }
}
