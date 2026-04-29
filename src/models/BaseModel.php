<?php
/**
 * =============================================
 * DELICACY - Model Base (Classe Abstrata)
 * =============================================
 * 
 * Classe abstrata que fornece operações CRUD genéricas para todos os models.
 * Todos os models do sistema estendem esta classe.
 * 
 * Funcionalidades:
 * - getAll()      → Lista todos os registros da tabela
 * - getById($id)  → Busca um registro por ID
 * - insert($data) → Insere um novo registro
 * - update($id, $data) → Atualiza um registro existente
 * - delete($id)   → Remove um registro
 * - search($column, $term) → Busca com LIKE
 * - count($conditions)     → Conta registros com filtros
 * 
 * Segurança:
 * - Todas as queries usam prepared statements (proteção contra SQL Injection)
 * - Verificação de retorno do prepare() em todos os métodos
 * - Nome da tabela validado contra whitelist
 * 
 * Raciocínio: Uma classe base evita repetição de código CRUD em cada model.
 * Cada model filho define apenas $table e seus métodos específicos.
 */

namespace Delicacy\Models;

use Delicacy\Database\Connection;

abstract class BaseModel
{
    /**
     * @var \mysqli Instância da conexão com o banco de dados
     */
    protected $db;

    /**
     * @var string Nome da tabela no banco de dados.
     * Deve ser definido em cada classe filha.
     */
    protected $table = '';

    /**
     * Lista de nomes de tabelas permitidos (whitelist).
     * Raciocínio: Previne SQL Injection via nome de tabela.
     * Mesmo que $table seja definido internamente, esta validação
     * adiciona uma camada extra de segurança defensiva.
     */
    private const ALLOWED_TABLES = [
        'users',
        'restaurants',
        'menus',
        'menu_items',
        'orders',
        'order_items',
        'customers',
        'loyalty_rules',
        'loyalty_transactions',
        'commissions',
        'admin_logs'
    ];

    /**
     * Construtor — inicializa a conexão com o banco via Singleton.
     * Valida se o nome da tabela está na whitelist permitida.
     * 
     * @throws \Exception Se o nome da tabela não for permitido
     */
    public function __construct()
    {
        $this->db = Connection::getInstance()->getConnection();

        // Validação defensiva do nome da tabela
        if (!empty($this->table) && !in_array($this->table, self::ALLOWED_TABLES)) {
            throw new \Exception("Tabela não permitida: {$this->table}");
        }
    }

    /**
     * Retorna todos os registros da tabela.
     * 
     * Raciocínio: Usar prepared statement mesmo sem parâmetros
     * seria ideal, mas para SELECT * sem WHERE, query() é aceitável
     * pois o nome da tabela já está validado pela whitelist.
     * 
     * @return array Lista de registros como arrays associativos
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        $result = $this->db->query($sql);

        if (!$result) {
            error_log("DELICACY DB: Erro ao buscar todos de {$this->table}: " . $this->db->error);
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Busca um registro pelo ID (chave primária).
     * 
     * @param int $id ID do registro
     * @return array|null Registro encontrado ou null se não existir
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        // Verificação de segurança: prepare() pode falhar
        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (getById) - {$this->table}: " . $this->db->error);
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    /**
     * Insere um novo registro na tabela.
     * 
     * Recebe um array associativo onde as chaves são nomes de colunas
     * e os valores são os dados a inserir.
     * 
     * Exemplo:
     *   $model->insert(['name' => 'Pizza', 'price' => 29.90]);
     *   // Gera: INSERT INTO tabela (name, price) VALUES (?, ?)
     * 
     * @param array $data Array associativo [coluna => valor]
     * @return int|false ID do registro inserido ou false em caso de erro
     */
    public function insert(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (insert) - {$this->table}: " . $this->db->error);
            return false;
        }

        // Determina os tipos dos parâmetros dinamicamente
        $types = $this->getParamTypes($data);
        $values = array_values($data);
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            $insertId = $this->db->insert_id;
            $stmt->close();
            return $insertId;
        }

        error_log("DELICACY DB: Erro ao inserir em {$this->table}: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /**
     * Atualiza um registro existente pelo ID.
     * 
     * @param int   $id   ID do registro a atualizar
     * @param array $data Array associativo [coluna => novo_valor]
     * @return bool True se atualizado com sucesso
     */
    public function update(int $id, array $data): bool
    {
        $setClauses = [];
        foreach (array_keys($data) as $column) {
            $setClauses[] = "{$column} = ?";
        }
        $set = implode(', ', $setClauses);

        $sql = "UPDATE `{$this->table}` SET {$set} WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (update) - {$this->table}: " . $this->db->error);
            return false;
        }

        // Tipos dos valores + tipo do ID (integer)
        $types = $this->getParamTypes($data) . 'i';
        $values = array_values($data);
        $values[] = $id;
        $stmt->bind_param($types, ...$values);

        $success = $stmt->execute();

        if (!$success) {
            error_log("DELICACY DB: Erro ao atualizar {$this->table} id={$id}: " . $stmt->error);
        }

        $stmt->close();
        return $success;
    }

    /**
     * Remove um registro pelo ID.
     * 
     * ATENÇÃO: Exclusão permanente. Considerar soft delete para entidades
     * críticas (restaurantes, pedidos) em releases futuras.
     * 
     * @param int $id ID do registro a remover
     * @return bool True se removido com sucesso
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (delete) - {$this->table}: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $id);
        $success = $stmt->execute();

        if (!$success) {
            error_log("DELICACY DB: Erro ao deletar de {$this->table} id={$id}: " . $stmt->error);
        }

        $stmt->close();
        return $success;
    }

    /**
     * Busca registros com LIKE em uma coluna específica.
     * Útil para funcionalidades de busca/filtro.
     * 
     * Exemplo:
     *   $model->search('name', 'pizza');
     *   // Gera: SELECT * FROM tabela WHERE name LIKE '%pizza%'
     * 
     * @param string $column Nome da coluna para buscar
     * @param string $term   Termo de busca
     * @return array Lista de registros que correspondem à busca
     */
    public function search(string $column, string $term): array
    {
        // Sanitiza o nome da coluna (permite apenas letras, números e underscore)
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            error_log("DELICACY DB: Nome de coluna inválido: {$column}");
            return [];
        }

        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` LIKE ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (search) - {$this->table}: " . $this->db->error);
            return [];
        }

        $searchTerm = "%{$term}%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    /**
     * Conta registros na tabela, opcionalmente filtrados por condições.
     * 
     * @param array $conditions Array associativo de condições [coluna => valor]
     *                          Usa igualdade (=) para cada condição, combinadas com AND.
     * @return int Número de registros
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach (array_keys($conditions) as $column) {
                $whereClauses[] = "`{$column}` = ?";
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (count) - {$this->table}: " . $this->db->error);
            return 0;
        }

        if (!empty($conditions)) {
            $types = $this->getParamTypes($conditions);
            $values = array_values($conditions);
            $stmt->bind_param($types, ...$values);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int)($row['total'] ?? 0);
    }

    /**
     * Determina automaticamente os tipos de parâmetros para bind_param.
     * 
     * Raciocínio: O código anterior usava 's' (string) para tudo.
     * Isso funciona na maioria dos casos, mas pode causar problemas
     * com comparações numéricas e performance de índices.
     * 
     * Tipos: i = integer, d = double/float, s = string, b = blob
     * 
     * @param array $data Array de dados
     * @return string String de tipos (ex: 'isd' = int, string, double)
     */
    protected function getParamTypes(array $data): string
    {
        $types = '';
        foreach (array_values($data) as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }
}