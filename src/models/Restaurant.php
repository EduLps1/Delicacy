<?php
/**
 * DELICACY - Restaurant Model (Unificado)
 *
 * Gerencia operações com restaurantes no banco de dados.
 * Utiliza prepared statements para segurança contra SQL injection.
 */

require_once __DIR__ . '/../../config/database.php';

class Restaurant
{
    /**
     * Encontra restaurante por ID (com dados do dono)
     */
    public function findById($id)
    {
        $query = "SELECT r.*, u.email as user_email, u.name as user_name 
                  FROM restaurants r 
                  LEFT JOIN users u ON r.user_id = u.id 
                  WHERE r.id = ?";
        return Database::getInstance()->fetchOne($query, [$id], 'i');
    }

    /**
     * Encontra restaurante por user_id
     */
    public function findByUserId($userId)
    {
        $query = "SELECT * FROM restaurants WHERE user_id = ? LIMIT 1";
        return Database::getInstance()->fetchOne($query, [$userId], 'i');
    }

    /**
     * Garante um restaurante técnico para contas em experimentação.
     */
    public function ensureTestRestaurantForUser($userId, $ownerName = 'Teste')
    {
        $this->ensureTestPlanTypeSupport();

        $existing = $this->findByUserId($userId);
        if ($existing) {
            if (($existing['plan_type'] ?? '') !== PLAN_TEST) {
                $this->update($existing['id'], [
                    'plan_type' => PLAN_TEST,
                    'commission_type' => COMMISSION_PLAN_ONLY,
                    'active_commission_rate' => 0.00,
                ]);
                return $this->findByUserId($userId);
            }

            return $existing;
        }

        $restaurantName = trim((string)$ownerName) ?: 'Teste';
        $restaurantId = $this->create([
            'user_id' => (int)$userId,
            'name' => $restaurantName,
            'description' => 'Restaurante de teste criado automaticamente para experimentação da plataforma.',
            'cnpj' => $this->buildTestCnpj((int)$userId),
            'phone' => null,
            'email' => null,
            'commission_type' => COMMISSION_PLAN_ONLY,
            'commission_rate' => 0.00,
            'plan_type' => PLAN_TEST,
        ]);

        return $this->findById($restaurantId);
    }

    private function buildTestCnpj($userId)
    {
        return '99' . str_pad((string)$userId, 12, '0', STR_PAD_LEFT);
    }

    private function ensureTestPlanTypeSupport()
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        $checked = true;
        Database::getInstance()->execute(
            "ALTER TABLE restaurants MODIFY plan_type ENUM('basic', 'premium', 'custom', 'test') NOT NULL DEFAULT 'basic'"
        );
    }

    /**
     * Verifica se CNPJ já existe
     */
    public function cnpjExists($cnpj, $excludeId = null)
    {
        $cnpj_clean = formatCNPJ($cnpj);

        if ($excludeId) {
            $query = "SELECT id FROM restaurants WHERE cnpj = ? AND id != ?";
            return Database::getInstance()->fetchOne($query, [$cnpj_clean, $excludeId], 'si') !== null;
        }

        $query = "SELECT id FROM restaurants WHERE cnpj = ?";
        return Database::getInstance()->fetchOne($query, [$cnpj_clean], 's') !== null;
    }

    /**
     * Cria novo restaurante
     */
    public function create($data)
    {
        // Validações
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            throw new Exception('user_id é obrigatório');
        }

        if (!isset($data['name']) || empty($data['name'])) {
            throw new Exception('Nome do restaurante é obrigatório');
        }

        if (!isset($data['cnpj']) || empty($data['cnpj'])) {
            throw new Exception('CNPJ é obrigatório');
        }

        if (!validateCNPJ($data['cnpj'])) {
            throw new Exception('CNPJ inválido');
        }

        $cnpj_clean = formatCNPJ($data['cnpj']);

        if ($this->cnpjExists($cnpj_clean)) {
            throw new Exception('CNPJ já registrado');
        }

        // Validar email se fornecido
        if (isset($data['email']) && !empty($data['email'])) {
            if (!validateEmail($data['email'])) {
                throw new Exception('Email do restaurante inválido');
            }
        }

        $query = "INSERT INTO restaurants 
                  (user_id, name, description, phone, email, cnpj, commission_type, 
                   active_commission_rate, plan_type, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $commission_type = $data['commission_type'] ?? COMMISSION_HYBRID;
        $commission_rate = $data['commission_rate'] ?? 2.50;
        $plan_type = $data['plan_type'] ?? PLAN_BASIC;

        $stmt = Database::getInstance()->execute(
            $query,
            [
                $data['user_id'],
                $data['name'],
                $data['description'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $cnpj_clean,
                $commission_type,
                $commission_rate,
                $plan_type,
                1  // is_active = 1
            ],
            'issssssdsi'
        );

        if ($stmt === false) {
            throw new Exception('Erro ao criar restaurante');
        }

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Lista todos os restaurantes com filtros
     */
    public function findAll($filters = [], $limit = 100, $offset = 0)
    {
        $query = "SELECT r.*, u.name AS owner_name, u.email AS owner_email
                  FROM restaurants r
                  LEFT JOIN users u ON u.id = r.user_id
                  WHERE 1=1";
        $params = [];
        $types = '';

        if (isset($filters['is_active'])) {
            $query .= " AND r.is_active = ?";
            $params[] = $filters['is_active'];
            $types .= 'i';
        }

        if (isset($filters['commission_type'])) {
            $query .= " AND r.commission_type = ?";
            $params[] = $filters['commission_type'];
            $types .= 's';
        }

        if (isset($filters['search'])) {
            $query .= " AND (r.name LIKE ? OR r.cnpj LIKE ? OR u.name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= 'sss';
        }

        $query .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        return Database::getInstance()->fetchAll($query, $params, $types);
    }

    /**
     * Conta total de restaurantes com filtros
     */
    public function count($filters = [])
    {
        $query = "SELECT COUNT(*) as total
                  FROM restaurants r
                  LEFT JOIN users u ON u.id = r.user_id
                  WHERE 1=1";
        $params = [];
        $types = '';

        if (isset($filters['is_active'])) {
            $query .= " AND r.is_active = ?";
            $params[] = $filters['is_active'];
            $types .= 'i';
        }

        if (isset($filters['commission_type'])) {
            $query .= " AND r.commission_type = ?";
            $params[] = $filters['commission_type'];
            $types .= 's';
        }

        if (isset($filters['search'])) {
            $query .= " AND (r.name LIKE ? OR r.cnpj LIKE ? OR u.name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= 'sss';
        }

        $result = Database::getInstance()->fetchOne($query, $params, $types);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Atualiza restaurante
     */
    public function update($id, $data)
    {
        $updates = [];
        $params = [];
        $types = '';

        if (isset($data['name'])) {
            $updates[] = 'name = ?';
            $params[] = $data['name'];
            $types .= 's';
        }

        if (isset($data['description'])) {
            $updates[] = 'description = ?';
            $params[] = $data['description'];
            $types .= 's';
        }

        if (isset($data['phone'])) {
            $updates[] = 'phone = ?';
            $params[] = $data['phone'];
            $types .= 's';
        }

        if (isset($data['email'])) {
            if (!empty($data['email']) && !validateEmail($data['email'])) {
                throw new Exception('Email inválido');
            }
            $updates[] = 'email = ?';
            $params[] = $data['email'];
            $types .= 's';
        }

        if (isset($data['commission_type'])) {
            $updates[] = 'commission_type = ?';
            $params[] = $data['commission_type'];
            $types .= 's';
        }

        if (isset($data['active_commission_rate'])) {
            $updates[] = 'active_commission_rate = ?';
            $params[] = $data['active_commission_rate'];
            $types .= 'd';
        }

        if (isset($data['plan_type'])) {
            $updates[] = 'plan_type = ?';
            $params[] = $data['plan_type'];
            $types .= 's';
        }

        if (isset($data['is_active'])) {
            $updates[] = 'is_active = ?';
            $params[] = $data['is_active'];
            $types .= 'i';
        }

        if (empty($updates)) {
            return true;
        }

        $updates[] = 'updated_at = NOW()';
        $query = "UPDATE restaurants SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= 'i';

        $stmt = Database::getInstance()->execute($query, $params, $types);
        return $stmt !== false;
    }

    /**
     * Deleta restaurante
     */
    public function delete($id)
    {
        $query = "DELETE FROM restaurants WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [$id], 'i');
        return $stmt !== false;
    }

    /**
     * Alterna is_active (0 <-> 1)
     */
    public function toggleIsActive($restaurantId)
    {
        $row = Database::getInstance()->fetchOne(
            "SELECT is_active FROM restaurants WHERE id = ? LIMIT 1",
            [$restaurantId],
            'i'
        );

        if (!$row) {
            return false;
        }

        $current = (int)($row['is_active'] ?? 0);
        $newValue = $current === 1 ? 0 : 1;

        Database::getInstance()->execute(
            "UPDATE restaurants SET is_active = ?, updated_at = NOW() WHERE id = ?",
            [$newValue, $restaurantId],
            'ii'
        );

        return true;
    }
}
