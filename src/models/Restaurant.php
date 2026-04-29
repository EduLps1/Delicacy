<?php
/**
 * =============================================
 * DELICACY - Model de Restaurante
 * =============================================
 * 
 * Gerencia operações na tabela 'restaurants'.
 * Responsável por: criação, busca, listagem e atualização de restaurantes.
 * 
 * Tabela: restaurants
 * Campos: id, user_id, name, description, logo_url, phone, email, cnpj,
 *         total_revenue, commission_type, active_commission_rate, plan_type,
 *         is_active, created_at, updated_at
 * 
 * Nota: Arquivo renomeado de Restaurante.php para Restaurant.php
 * para conformidade com PSR-4 (nome do arquivo = nome da classe).
 */

namespace Delicacy\Models;

class Restaurant extends BaseModel
{
    /**
     * @var string Nome da tabela no banco de dados
     */
    protected $table = 'restaurants';

    /**
     * Busca o restaurante vinculado a um usuário específico.
     * Cada usuário admin_restaurant tem exatamente um restaurante.
     * 
     * @param int $userId ID do usuário (admin_restaurant)
     * @return array|null Dados do restaurante ou null se não encontrado
     */
    public function findByUserId(int $userId): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (findByUserId): " . $this->db->error);
            return null;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $restaurant = $result->fetch_assoc();
        $stmt->close();

        return $restaurant ?: null;
    }

    /**
     * Busca um restaurante pelo CNPJ.
     * Usado para verificar duplicidade no cadastro.
     * 
     * @param string $cnpj CNPJ sem formatação (14 dígitos)
     * @return array|null Dados do restaurante ou null se não encontrado
     */
    public function findByCnpj(string $cnpj): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE cnpj = ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (findByCnpj): " . $this->db->error);
            return null;
        }

        $stmt->bind_param("s", $cnpj);
        $stmt->execute();
        $result = $stmt->get_result();
        $restaurant = $result->fetch_assoc();
        $stmt->close();

        return $restaurant ?: null;
    }

    /**
     * Cria um novo restaurante no banco de dados.
     * 
     * @param int    $userId ID do usuário dono (admin_restaurant)
     * @param string $name   Nome do restaurante
     * @param string $email  Email comercial
     * @param string $phone  Telefone com DDD
     * @param string $cnpj   CNPJ sem formatação
     * @return int|false ID do restaurante criado ou false em caso de erro
     */
    public function createRestaurant(int $userId, string $name, string $email, string $phone, string $cnpj)
    {
        $data = [
            'user_id'                => $userId,
            'name'                   => $name,
            'email'                  => $email,
            'phone'                  => $phone,
            'cnpj'                   => $cnpj,
            'commission_type'        => COMMISSION_HYBRID,
            'active_commission_rate' => 2.5,
            'plan_type'              => PLAN_BASIC,
            'is_active'              => RESTAURANT_ACTIVE,
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    /**
     * Retorna todos os restaurantes ativos.
     * Usa prepared statement em vez de concatenação direta.
     * 
     * CORREÇÃO: Versão anterior usava concatenação direta na query:
     *   "WHERE is_active = " . RESTAURANT_ACTIVE
     * Substituído por prepared statement para consistência de segurança.
     * 
     * @return array Lista de restaurantes ativos
     */
    public function getActiveRestaurants(): array
    {
        $sql = "SELECT r.*, u.name as owner_name, u.email as owner_email
                FROM `{$this->table}` r
                INNER JOIN users u ON r.user_id = u.id
                WHERE r.is_active = ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (getActiveRestaurants): " . $this->db->error);
            return [];
        }

        $active = RESTAURANT_ACTIVE;
        $stmt->bind_param("i", $active);
        $stmt->execute();
        $result = $stmt->get_result();
        $restaurants = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $restaurants;
    }

    /**
     * Busca restaurantes por nome (busca parcial com LIKE).
     * Usado na funcionalidade de busca do Admin Delicacy.
     * 
     * @param string $name Termo de busca
     * @return array Lista de restaurantes que correspondem à busca
     */
    public function searchByName(string $name): array
    {
        $sql = "SELECT r.*, u.name as owner_name, u.email as owner_email
                FROM `{$this->table}` r
                INNER JOIN users u ON r.user_id = u.id
                WHERE r.name LIKE ? OR r.cnpj LIKE ?
                ORDER BY r.name ASC";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (searchByName): " . $this->db->error);
            return [];
        }

        $searchTerm = "%{$name}%";
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $restaurants = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $restaurants;
    }

    /**
     * Lista todos os restaurantes com dados do proprietário (JOIN com users).
     * Usado no dashboard do Admin Delicacy.
     * 
     * @return array Lista de restaurantes com dados do dono
     */
    public function getAllWithOwner(): array
    {
        $sql = "SELECT r.*, u.name as owner_name, u.email as owner_email
                FROM `{$this->table}` r
                INNER JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC";
        $result = $this->db->query($sql);

        if (!$result) {
            error_log("DELICACY DB: Erro em getAllWithOwner: " . $this->db->error);
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Atualiza o status de ativação do restaurante.
     * Usado pelo Admin Delicacy para suspender/ativar restaurantes.
     * 
     * @param int  $restaurantId ID do restaurante
     * @param bool $isActive     True para ativar, false para suspender
     * @return bool True se atualizado com sucesso
     */
    public function updateStatus(int $restaurantId, bool $isActive): bool
    {
        return $this->update($restaurantId, [
            'is_active'  => $isActive ? RESTAURANT_ACTIVE : RESTAURANT_INACTIVE,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}