<?php
/**
 * DELICACY - Menu Model
 *
 * Gerencia operações com cardápios no banco de dados.
 */

require_once __DIR__ . '/../../config/database.php';

class Menu
{
    /**
     * Encontra cardápio por ID
     */
    public function findById($id)
    {
        $query = "SELECT m.*, r.name as restaurant_name 
                  FROM menus m 
                  LEFT JOIN restaurants r ON m.restaurant_id = r.id 
                  WHERE m.id = ?";
        return Database::getInstance()->fetchOne($query, [$id], 'i');
    }

    /**
     * Encontra cardápio por slug (URL pública)
     */
    public function findBySlug($slug)
    {
        $query = "SELECT m.*, r.name as restaurant_name, r.phone as restaurant_phone,
                         r.logo_url as restaurant_logo
                  FROM menus m 
                  LEFT JOIN restaurants r ON m.restaurant_id = r.id 
                  WHERE m.slug = ? AND m.is_published = 1";
        return Database::getInstance()->fetchOne($query, [$slug], 's');
    }

    /**
     * Lista cardápios de um restaurante
     */
    public function findByRestaurantId($restaurantId)
    {
        $query = "SELECT m.*, 
                    (SELECT COUNT(*) FROM menu_items WHERE menu_id = m.id) as items_count
                  FROM menus m 
                  WHERE m.restaurant_id = ? 
                  ORDER BY m.created_at DESC";
        return Database::getInstance()->fetchAll($query, [$restaurantId], 'i');
    }

    /**
     * Conta cardapios cadastrados.
     */
    public function count($filters = [])
    {
        $query = "SELECT COUNT(*) as total FROM menus WHERE 1=1";
        $params = [];
        $types = '';

        if (isset($filters['is_published'])) {
            $query .= " AND is_published = ?";
            $params[] = (int)$filters['is_published'];
            $types .= 'i';
        }

        $result = Database::getInstance()->fetchOne($query, $params, $types);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Cria novo cardápio
     */
    public function create($data)
    {
        if (empty($data['restaurant_id']) || empty($data['name'])) {
            throw new Exception('Restaurante e nome são obrigatórios');
        }

        $slug = $this->generateSlug($data['name'], $data['restaurant_id']);

        $query = "INSERT INTO menus (restaurant_id, name, description, type, unit_number, slug) 
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = Database::getInstance()->execute(
            $query,
            [
                $data['restaurant_id'],
                $data['name'],
                $data['description'] ?? null,
                $data['type'] ?? MENU_TYPE_BOTH,
                $data['unit_number'] ?? 1,
                $slug
            ],
            'isssis'
        );

        if ($stmt === false) {
            throw new Exception('Erro ao criar cardápio');
        }

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Atualiza cardápio
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

        if (isset($data['type'])) {
            $updates[] = 'type = ?';
            $params[] = $data['type'];
            $types .= 's';
        }

        if (isset($data['unit_number'])) {
            $updates[] = 'unit_number = ?';
            $params[] = $data['unit_number'];
            $types .= 'i';
        }

        if (empty($updates)) {
            return true;
        }

        $updates[] = 'updated_at = NOW()';
        $query = "UPDATE menus SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= 'i';

        $stmt = Database::getInstance()->execute($query, $params, $types);
        return $stmt !== false;
    }

    /**
     * Deleta cardápio
     */
    public function delete($id)
    {
        $query = "DELETE FROM menus WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [$id], 'i');
        return $stmt !== false;
    }

    /**
     * Publica cardápio (valida se tem pelo menos 1 prato)
     */
    public function publish($id)
    {
        // Verifica se tem pelo menos 1 item
        $count = Database::getInstance()->fetchOne(
            "SELECT COUNT(*) as total FROM menu_items WHERE menu_id = ? AND is_active = 1",
            [$id],
            'i'
        );

        if (!$count || (int)$count['total'] === 0) {
            throw new Exception('O cardápio precisa ter pelo menos 1 prato ativo para ser publicado');
        }

        $stmt = Database::getInstance()->execute(
            "UPDATE menus SET is_published = 1, updated_at = NOW() WHERE id = ?",
            [$id],
            'i'
        );
        return $stmt !== false;
    }

    /**
     * Despublica cardápio
     */
    public function unpublish($id)
    {
        $stmt = Database::getInstance()->execute(
            "UPDATE menus SET is_published = 0, updated_at = NOW() WHERE id = ?",
            [$id],
            'i'
        );
        return $stmt !== false;
    }

    /**
     * Gera slug único para URL pública
     */
    private function generateSlug($name, $restaurantId)
    {
        // Converte para lowercase, remove acentos, substitui espaços por hífens
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[áàãâä]/u', 'a', $slug);
        $slug = preg_replace('/[éèêë]/u', 'e', $slug);
        $slug = preg_replace('/[íìîï]/u', 'i', $slug);
        $slug = preg_replace('/[óòõôö]/u', 'o', $slug);
        $slug = preg_replace('/[úùûü]/u', 'u', $slug);
        $slug = preg_replace('/[ç]/u', 'c', $slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Adiciona sufixo único
        $slug = $slug . '-' . substr(md5($restaurantId . time()), 0, 6);

        return $slug;
    }

    /**
     * Verifica se o cardápio pertence ao restaurante
     */
    public function belongsToRestaurant($menuId, $restaurantId)
    {
        $result = Database::getInstance()->fetchOne(
            "SELECT id FROM menus WHERE id = ? AND restaurant_id = ?",
            [$menuId, $restaurantId],
            'ii'
        );
        return $result !== null;
    }
}
