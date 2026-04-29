<?php
/**
 * =============================================
 * DELICACY - Model de Usuário
 * =============================================
 * 
 * Gerencia operações na tabela 'users'.
 * Responsável por: criação de usuários, autenticação, busca por email/role.
 * 
 * Tabela: users
 * Campos: id, email, password, name, role, status, last_login_at, created_at, updated_at
 * 
 * Segurança:
 * - Senhas hasheadas com bcrypt (custo definido em BCRYPT_COST)
 * - Verificação de senha com password_verify() (time-safe)
 * - Prepared statements em todas as queries
 */

namespace Delicacy\Models;

class User extends BaseModel
{
    /**
     * @var string Nome da tabela no banco de dados
     */
    protected $table = 'users';

    /**
     * Busca um usuário pelo email.
     * Usado no login para verificar se o email existe.
     * 
     * @param string $email Email do usuário
     * @return array|null Dados do usuário ou null se não encontrado
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE email = ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (findByEmail): " . $this->db->error);
            return null;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    /**
     * Busca todos os usuários com uma role específica.
     * Útil para o Admin Delicacy listar todos os admin_restaurant.
     * 
     * @param string $role Role a filtrar (usar constantes ROLE_*)
     * @return array Lista de usuários com a role especificada
     */
    public function findByRole(string $role): array
    {
        $sql = "SELECT id, email, name, role, status, created_at, updated_at 
                FROM `{$this->table}` WHERE role = ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (findByRole): " . $this->db->error);
            return [];
        }

        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $users;
    }

    /**
     * Cria um novo usuário com senha hasheada.
     * 
     * Raciocínio do hash:
     * - PASSWORD_BCRYPT é o algoritmo recomendado para senhas
     * - O custo (cost) define quantas iterações o algoritmo faz
     * - Custo 12 = ~250ms por hash — lento o suficiente para dificultar
     *   brute force, rápido o suficiente para não impactar o usuário
     * - O salt é gerado automaticamente pelo PHP (não usar salt manual)
     * 
     * @param string $email    Email do usuário
     * @param string $password Senha em texto plano (será hasheada)
     * @param string $name     Nome completo
     * @param string $role     Role do usuário (usar constantes ROLE_*)
     * @return int|false ID do usuário criado ou false em caso de erro
     */
    public function createUser(string $email, string $password, string $name, string $role)
    {
        // Gera o hash da senha com bcrypt e custo configurável
        $cost = defined('BCRYPT_COST') ? BCRYPT_COST : 12;
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);

        $data = [
            'email'      => $email,
            'password'   => $hashedPassword,
            'name'       => $name,
            'role'       => $role,
            'status'     => USER_ACTIVE,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    /**
     * Verifica se a senha fornecida corresponde ao hash armazenado.
     * Retorna os dados do usuário se a senha estiver correta.
     * 
     * Raciocínio do password_verify():
     * - Faz comparação time-safe (previne timing attacks)
     * - Suporta qualquer algoritmo de hash suportado pelo password_hash()
     * - Retorna false para hash inválido sem revelar qual parte está errada
     * 
     * @param string $email    Email do usuário
     * @param string $password Senha em texto plano para verificar
     * @return array|false Dados do usuário ou false se credenciais inválidas
     */
    public function verifyPassword(string $email, string $password)
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Atualiza a senha de um usuário.
     * Gera novo hash bcrypt para a nova senha.
     * 
     * @param int    $userId      ID do usuário
     * @param string $newPassword Nova senha em texto plano
     * @return bool True se atualizado com sucesso
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $cost = defined('BCRYPT_COST') ? BCRYPT_COST : 12;
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => $cost]);

        return $this->update($userId, [
            'password'   => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Atualiza o status de um usuário (ativar/desativar).
     * 
     * @param int    $userId ID do usuário
     * @param string $status Novo status (usar constantes USER_ACTIVE ou USER_INACTIVE)
     * @return bool True se atualizado com sucesso
     */
    public function updateStatus(int $userId, string $status): bool
    {
        return $this->update($userId, [
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Registra a data/hora do último login do usuário.
     * Útil para auditoria de segurança e detecção de contas inativas.
     * 
     * @param int $userId ID do usuário
     * @return bool True se atualizado com sucesso
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);
    }
}