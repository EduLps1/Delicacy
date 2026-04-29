<?php
/**
 * =============================================
 * DELICACY - Model de Log Administrativo
 * =============================================
 * 
 * Gerencia a tabela 'admin_logs' para auditoria de ações administrativas.
 * Registra TODAS as ações dos administradores (Delicacy e restaurante).
 * 
 * Tabela: admin_logs
 * Campos: id, admin_id, action, entity_type, entity_id, changes, created_at, updated_at
 * 
 * Importância da auditoria:
 * - Rastreabilidade: saber quem fez o quê e quando
 * - Segurança: detectar ações suspeitas ou não autorizadas
 * - Compliance: atender requisitos legais de registro de alterações
 * - Resolução de disputas: evidência do que aconteceu
 */

namespace Delicacy\Models;

class AdminLog extends BaseModel
{
    /**
     * @var string Nome da tabela no banco de dados
     */
    protected $table = 'admin_logs';

    /**
     * Registra uma ação administrativa no log.
     * 
     * @param int         $adminId    ID do admin que executou a ação
     * @param string      $action     Tipo da ação (usar constantes LOG_ACTION_*)
     * @param string      $entityType Tipo da entidade afetada (usar constantes LOG_ENTITY_*)
     * @param int|null    $entityId   ID da entidade afetada (null para ações globais)
     * @param array|null  $changes    Detalhes da alteração (será convertido para JSON)
     * @return int|false ID do log criado ou false em caso de erro
     */
    public function createLog(
        int $adminId, 
        string $action, 
        string $entityType, 
        ?int $entityId = null, 
        ?array $changes = null
    ) {
        $data = [
            'admin_id'    => $adminId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'changes'     => $changes ? json_encode($changes, JSON_UNESCAPED_UNICODE) : null,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    /**
     * Retorna todos os logs de um admin específico.
     * Ordenados do mais recente para o mais antigo.
     * 
     * @param int $adminId ID do administrador
     * @param int $limit   Número máximo de registros (padrão: 50)
     * @return array Lista de logs
     */
    public function getLogsByAdmin(int $adminId, int $limit = 50): array
    {
        $sql = "SELECT al.*, u.name as admin_name
                FROM `{$this->table}` al
                INNER JOIN users u ON al.admin_id = u.id
                WHERE al.admin_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (getLogsByAdmin): " . $this->db->error);
            return [];
        }

        $stmt->bind_param("ii", $adminId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $logs;
    }

    /**
     * Retorna os logs mais recentes do sistema (todos os admins).
     * Usado no dashboard do Admin Delicacy para monitoramento.
     * 
     * @param int $limit Número máximo de registros (padrão: 20)
     * @return array Lista de logs recentes
     */
    public function getRecentLogs(int $limit = 20): array
    {
        $sql = "SELECT al.*, u.name as admin_name
                FROM `{$this->table}` al
                INNER JOIN users u ON al.admin_id = u.id
                ORDER BY al.created_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (getRecentLogs): " . $this->db->error);
            return [];
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $logs;
    }

    /**
     * Retorna logs filtrados por tipo de entidade.
     * Útil para ver todas as ações sobre restaurantes, menus, etc.
     * 
     * @param string $entityType Tipo da entidade (usar constantes LOG_ENTITY_*)
     * @param int    $limit      Número máximo de registros
     * @return array Lista de logs filtrados
     */
    public function getLogsByEntityType(string $entityType, int $limit = 50): array
    {
        $sql = "SELECT al.*, u.name as admin_name
                FROM `{$this->table}` al
                INNER JOIN users u ON al.admin_id = u.id
                WHERE al.entity_type = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            error_log("DELICACY DB: Erro no prepare (getLogsByEntityType): " . $this->db->error);
            return [];
        }

        $stmt->bind_param("si", $entityType, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $logs;
    }
}
