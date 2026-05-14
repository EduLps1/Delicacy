<?php
/**
 * DELICACY - Database Connection
 * 
 * Gerencia a conexão com MySQL.
 * Utiliza mysqli com suporte a prepared statements.
 */

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            // Cria conexão com MySQL
            $this->connection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME,
                DB_PORT
            );

            // Verifica erros de conexão
            if ($this->connection->connect_error) {
                throw new Exception('Erro de conexão: ' . $this->connection->connect_error);
            }

            // Define charset
            $this->connection->set_charset(DB_CHARSET);

            // Define timezone no MySQL
            $this->connection->query("SET time_zone = '-03:00'");

        } catch (Exception $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new Exception('Erro ao conectar ao banco de dados. Verifique as credenciais do .env.');
        }
    }

    /**
     * Retorna a instância singleton da conexão
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Retorna a conexão MySQLi
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Executa query preparada com segurança contra SQL injection
     * 
     * @param string $query Query SQL com placeholders ?
     * @param array $params Parâmetros para bind
     * @param string $types Tipos dos parâmetros (s=string, i=int, d=double, b=blob)
     * @return mysqli_result|bool
     */
    public function execute($query, $params = [], $types = '') {
        try {
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception('Erro ao preparar query: ' . $this->connection->error);
            }

            if (!empty($params)) {
                // Auto-detect tipos se não fornecidos
                if (empty($types)) {
                    $types = '';
                    foreach ($params as $param) {
                        if (is_int($param)) {
                            $types .= 'i';
                        } elseif (is_float($param)) {
                            $types .= 'd';
                        } else {
                            $types .= 's';
                        }
                    }
                }

                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            return $stmt;

        } catch (Exception $e) {
            error_log('Database Execute Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna uma linha como array associativo
     */
    public function fetchOne($query, $params = [], $types = '') {
        $stmt = $this->execute($query, $params, $types);
        
        if ($stmt === false) {
            return null;
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row;
    }

    /**
     * Retorna todas as linhas como array de arrays
     */
    public function fetchAll($query, $params = [], $types = '') {
        $stmt = $this->execute($query, $params, $types);
        
        if ($stmt === false) {
            return [];
        }

        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }

    /**
     * Retorna o ID da última linha inserida
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    /**
     * Retorna o número de linhas afetadas pela última query
     */
    public function affectedRows() {
        return $this->connection->affected_rows;
    }

    /**
     * Inicia uma transação
     */
    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }

    /**
     * Faz commit da transação
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Faz rollback da transação
     */
    public function rollback() {
        return $this->connection->rollback();
    }

    /**
     * Fecha a conexão
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    // Previne clonagem
    private function __clone() {}

    // Previne desserialização
    public function __wakeup() {
        throw new Exception('Impossível desserializar instância de Database');
    }
}
