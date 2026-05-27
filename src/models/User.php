<?php
/**
 * DELICACY - Model User
 * 
 * Gerencia operações com usuários no banco de dados.
 */

require_once __DIR__ . '/../../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Encontra usuário por ID
     */
    public function findById($id) {
        $query = "SELECT id, email, name, role, status, created_at, updated_at 
                  FROM users WHERE id = ?";
        return Database::getInstance()->fetchOne($query, [$id], 'i');
    }

    /**
     * Encontra usuário por email
     */
    public function findByEmail($email) {
        $query = "SELECT id, email, password, name, role, status, created_at, updated_at 
                  FROM users WHERE email = ?";
        return Database::getInstance()->fetchOne($query, [$email], 's');
    }

    /**
     * Verifica se email já existe
     */
    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $query = "SELECT id FROM users WHERE email = ? AND id != ?";
            return Database::getInstance()->fetchOne($query, [$email, $excludeId], 'si') !== null;
        }
        
        $query = "SELECT id FROM users WHERE email = ?";
        return Database::getInstance()->fetchOne($query, [$email], 's') !== null;
    }

    /**
     * Cria novo usuário
     */
    public function create($data) {
        // Validações básicas
        if (!validateEmail($data['email'])) {
            throw new Exception('Email inválido');
        }

        if (strlen($data['password']) < 8) {
            throw new Exception('Senha deve ter no mínimo 8 caracteres');
        }

        if (!preg_match('/[A-Z]/', $data['password']) || !preg_match('/[a-z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
            throw new Exception('Senha deve conter letras maiúscula, minúscula e número');
        }

        if ($this->emailExists($data['email'])) {
            throw new Exception('Email já registrado');
        }

        $query = "INSERT INTO users (email, password, name, role, status) 
                  VALUES (?, ?, ?, ?, ?)";

        $password_hash = hashPassword($data['password']);
        $role = $data['role'] ?? ROLE_CUSTOMER;
        $status = STATUS_ACTIVE;

        $stmt = Database::getInstance()->execute(
            $query,
            [
                $data['email'],
                $password_hash,
                $data['name'],
                $role,
                $status
            ],
            'sssss'
        );

        if ($stmt === false) {
            throw new Exception('Erro ao criar usuário');
        }

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Autentica usuário por email e senha
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        if ($user['status'] !== STATUS_ACTIVE) {
            return false;
        }

        if (!verifyPassword($password, $user['password'])) {
            return false;
        }

        // Atualiza last_login_at
        $this->updateLastLogin($user['id']);

        // Remove senha da resposta
        unset($user['password']);

        return $user;
    }

    /**
     * Atualiza last_login_at
     */
    public function updateLastLogin($userId) {
        $query = "UPDATE users SET last_login_at = NOW() WHERE id = ?";
        Database::getInstance()->execute($query, [$userId], 'i');
    }

    /**
     * Lista usuários por papel
     */
    public function findByRole($role, $limit = 100, $offset = 0) {
        $query = "SELECT id, email, name, role, status, created_at 
                  FROM users WHERE role = ? 
                  LIMIT ? OFFSET ?";
        return Database::getInstance()->fetchAll($query, [$role, $limit, $offset], 'sii');
    }

    /**
     * Atualiza usuário
     */
    public function update($id, $data) {
        $updates = [];
        $params = [];
        $types = '';

        if (isset($data['name'])) {
            $updates[] = 'name = ?';
            $params[] = $data['name'];
            $types .= 's';
        }

        if (isset($data['email'])) {
            if (!validateEmail($data['email'])) {
                throw new Exception('Email inválido');
            }
            if ($this->emailExists($data['email'], $id)) {
                throw new Exception('Email já está em uso');
            }
            $updates[] = 'email = ?';
            $params[] = $data['email'];
            $types .= 's';
        }

        if (isset($data['status'])) {
            $updates[] = 'status = ?';
            $params[] = $data['status'];
            $types .= 's';
        }

        if (isset($data['password'])) {
            if (strlen($data['password']) < 8) {
                throw new Exception('Senha deve ter no mínimo 8 caracteres');
            }
            if (!preg_match('/[A-Z]/', $data['password']) || !preg_match('/[a-z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
                throw new Exception('Senha deve conter letras maiúscula, minúscula e número');
            }
            $updates[] = 'password = ?';
            $params[] = hashPassword($data['password']);
            $types .= 's';
        }

        if (empty($updates)) {
            return true;
        }

        $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= 'i';

        $stmt = Database::getInstance()->execute($query, $params, $types);
        return $stmt !== false;
    }

    /**
     * Deleta usuário (marca como inativo)
     */
    public function delete($id) {
        $query = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [STATUS_INACTIVE, $id], 'si');
        return $stmt !== false;
    }

    /**
     * Conta total de usuários por papel
     */
    public function countByRole($role) {
        $query = "SELECT COUNT(*) as count FROM users WHERE role = ?";
        $result = Database::getInstance()->fetchOne($query, [$role], 's');
        return $result['count'] ?? 0;
    }
}
