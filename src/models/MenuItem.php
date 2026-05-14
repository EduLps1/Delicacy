<?php
/**
 * DELICACY - MenuItem Model
 *
 * Gerencia operações com itens de cardápio (pratos, bebidas, etc).
 */

require_once __DIR__ . '/../../config/database.php';

class MenuItem
{
    /**
     * Encontra item por ID
     */
    public function findById($id)
    {
        $query = "SELECT * FROM menu_items WHERE id = ?";
        return Database::getInstance()->fetchOne($query, [$id], 'i');
    }

    /**
     * Lista itens de um cardápio
     */
    public function findByMenuId($menuId, $activeOnly = false)
    {
        $query = "SELECT * FROM menu_items WHERE menu_id = ?";
        $params = [$menuId];
        $types = 'i';

        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }

        $query .= " ORDER BY category ASC, name ASC";

        return Database::getInstance()->fetchAll($query, $params, $types);
    }

    /**
     * Lista itens agrupados por categoria
     */
    public function findByMenuIdGrouped($menuId)
    {
        $items = $this->findByMenuId($menuId, true);
        $grouped = [];

        foreach ($items as $item) {
            $category = $item['category'] ?? 'outros';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $item;
        }

        return $grouped;
    }

    /**
     * Cria novo item
     */
    public function create($data)
    {
        if (empty($data['menu_id']) || empty($data['name'])) {
            throw new Exception('Cardápio e nome são obrigatórios');
        }

        if (!isset($data['price']) || (float)$data['price'] < 0) {
            throw new Exception('Preço deve ser maior ou igual a zero');
        }

        $query = "INSERT INTO menu_items (menu_id, name, description, price, category, image_url, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = Database::getInstance()->execute(
            $query,
            [
                $data['menu_id'],
                $data['name'],
                $data['description'] ?? null,
                (float)$data['price'],
                $data['category'] ?? 'outros',
                $data['image_url'] ?? null,
                1
            ],
            'issdssi'
        );

        if ($stmt === false) {
            throw new Exception('Erro ao criar item do cardápio');
        }

        return Database::getInstance()->lastInsertId();
    }

    /**
     * Atualiza item
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

        if (isset($data['price'])) {
            if ((float)$data['price'] < 0) {
                throw new Exception('Preço deve ser maior ou igual a zero');
            }
            $updates[] = 'price = ?';
            $params[] = (float)$data['price'];
            $types .= 'd';
        }

        if (isset($data['category'])) {
            $updates[] = 'category = ?';
            $params[] = $data['category'];
            $types .= 's';
        }

        if (isset($data['image_url'])) {
            $updates[] = 'image_url = ?';
            $params[] = $data['image_url'];
            $types .= 's';
        }

        if (isset($data['is_active'])) {
            $updates[] = 'is_active = ?';
            $params[] = (int)$data['is_active'];
            $types .= 'i';
        }

        if (empty($updates)) {
            return true;
        }

        $updates[] = 'updated_at = NOW()';
        $query = "UPDATE menu_items SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $id;
        $types .= 'i';

        $stmt = Database::getInstance()->execute($query, $params, $types);
        return $stmt !== false;
    }

    /**
     * Deleta item
     */
    public function delete($id)
    {
        $query = "DELETE FROM menu_items WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [$id], 'i');
        return $stmt !== false;
    }

    /**
     * Alterna is_active
     */
    public function toggleActive($id)
    {
        $item = $this->findById($id);
        if (!$item) {
            return false;
        }

        $newValue = $item['is_active'] ? 0 : 1;
        return $this->update($id, ['is_active' => $newValue]);
    }

    /**
     * Conta itens ativos de um cardápio
     */
    public function countByMenuId($menuId, $activeOnly = true)
    {
        $query = "SELECT COUNT(*) as total FROM menu_items WHERE menu_id = ?";
        $params = [$menuId];
        $types = 'i';

        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }

        $result = Database::getInstance()->fetchOne($query, $params, $types);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Verifica se o item pertence ao cardápio
     */
    public function belongsToMenu($itemId, $menuId)
    {
        $result = Database::getInstance()->fetchOne(
            "SELECT id FROM menu_items WHERE id = ? AND menu_id = ?",
            [$itemId, $menuId],
            'ii'
        );
        return $result !== null;
    }

    /**
     * Upload de imagem do prato
     */
    public function uploadImage($file)
    {
        // Validações
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no upload do arquivo');
        }

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            throw new Exception('Arquivo muito grande (máximo 5MB)');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, ALLOWED_MIME_TYPES)) {
            throw new Exception('Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP');
        }

        // Gera nome único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'prato_' . uniqid() . '.' . $extension;
        $uploadPath = UPLOAD_DIR . '/pratos';

        // Cria diretório se não existir
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $destination = $uploadPath . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Erro ao salvar o arquivo');
        }

        return '/uploads/pratos/' . $filename;
    }
}
