<?php
/**
 * DELICACY - Menu Model
 *
 * Centraliza cardápios tradicionais, editor visual e URLs públicas.
 */

require_once __DIR__ . '/../../config/database.php';

class Menu
{
    public function findById($id)
    {
        $this->ensureEditorSchemaSupport();

        $query = "SELECT m.*, r.name AS restaurant_name, r.phone AS restaurant_phone,
                         r.email AS restaurant_email, r.logo_url AS restaurant_logo
                  FROM menus m
                  LEFT JOIN restaurants r ON r.id = m.restaurant_id
                  WHERE m.id = ?
                  LIMIT 1";
        return Database::getInstance()->fetchOne($query, [(int)$id], 'i');
    }

    public function findByPublicId($publicId)
    {
        $this->ensureEditorSchemaSupport();

        $query = "SELECT m.*, r.name AS restaurant_name, r.phone AS restaurant_phone,
                         r.email AS restaurant_email, r.logo_url AS restaurant_logo
                  FROM menus m
                  LEFT JOIN restaurants r ON r.id = m.restaurant_id
                  WHERE m.public_id = ?
                  LIMIT 1";
        return Database::getInstance()->fetchOne($query, [strtoupper((string)$publicId)], 's');
    }

    public function findBySlug($slug)
    {
        $this->ensureEditorSchemaSupport();

        $query = "SELECT m.*, r.name AS restaurant_name, r.phone AS restaurant_phone,
                         r.email AS restaurant_email, r.logo_url AS restaurant_logo
                  FROM menus m
                  LEFT JOIN restaurants r ON r.id = m.restaurant_id
                  WHERE (m.slug = ? OR m.public_url = ?) AND m.is_published = 1
                  LIMIT 1";
        return Database::getInstance()->fetchOne($query, [(string)$slug, (string)$slug], 'ss');
    }

    public function findByRestaurantId($restaurantId)
    {
        $this->ensureEditorSchemaSupport();

        $query = "SELECT m.*,
                    (SELECT COUNT(*) FROM menu_items WHERE menu_id = m.id) AS items_count
                  FROM menus m
                  WHERE m.restaurant_id = ?
                  ORDER BY m.created_at DESC";
        return Database::getInstance()->fetchAll($query, [(int)$restaurantId], 'i');
    }

    public function count($filters = [])
    {
        $query = "SELECT COUNT(*) AS total FROM menus WHERE 1=1";
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

    public function countDraftsByRestaurantId($restaurantId)
    {
        $result = Database::getInstance()->fetchOne(
            "SELECT COUNT(*) AS total FROM menus WHERE restaurant_id = ? AND is_published = 0",
            [(int)$restaurantId],
            'i'
        );
        return (int)($result['total'] ?? 0);
    }

    public function countByRestaurantId($restaurantId)
    {
        $result = Database::getInstance()->fetchOne(
            "SELECT COUNT(*) AS total FROM menus WHERE restaurant_id = ?",
            [(int)$restaurantId],
            'i'
        );
        return (int)($result['total'] ?? 0);
    }

    public function getPlanMenuLimit($planType)
    {
        $limits = [
            PLAN_BASIC => 1,
            PLAN_PREMIUM => 3,
            PLAN_CUSTOM => 10,
            PLAN_TEST => 1,
        ];

        return $limits[$planType] ?? 1;
    }

    public function canCreateDraft($restaurant)
    {
        if (empty($restaurant['id'])) {
            return false;
        }

        return $this->countByRestaurantId((int)$restaurant['id']) < $this->getPlanMenuLimit($restaurant['plan_type'] ?? PLAN_BASIC);
    }

    public function ensurePublicId($menu)
    {
        if (!empty($menu['public_id'])) {
            return $menu['public_id'];
        }

        $publicId = $this->generatePublicId();
        Database::getInstance()->execute(
            "UPDATE menus SET public_id = ?, updated_at = NOW() WHERE id = ?",
            [$publicId, (int)$menu['id']],
            'si'
        );

        return $publicId;
    }

    public function create($data)
    {
        $this->ensureEditorSchemaSupport();

        if (empty($data['restaurant_id']) || empty($data['name'])) {
            throw new Exception('Restaurante e nome são obrigatórios');
        }

        $publicId = $data['public_id'] ?? $this->generatePublicId();
        $slug = $this->generateSlug($data['name'], (int)$data['restaurant_id']);
        $editorData = $data['editor_data'] ?? $this->defaultEditorData($data['name']);

        $query = "INSERT INTO menus
                    (public_id, restaurant_id, name, description, cover_image_url, editor_data, type, unit_number, slug)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = Database::getInstance()->execute(
            $query,
            [
                $publicId,
                (int)$data['restaurant_id'],
                $data['name'],
                $data['description'] ?? null,
                $data['cover_image_url'] ?? null,
                is_string($editorData) ? $editorData : json_encode($editorData, JSON_UNESCAPED_UNICODE),
                $data['type'] ?? MENU_TYPE_BOTH,
                (int)($data['unit_number'] ?? 1),
                $slug,
            ],
            'sisssssis'
        );

        if ($stmt === false) {
            throw new Exception('Erro ao criar cardápio');
        }

        return Database::getInstance()->lastInsertId();
    }

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

        if (array_key_exists('cover_image_url', $data)) {
            $updates[] = 'cover_image_url = ?';
            $params[] = $data['cover_image_url'];
            $types .= 's';
        }

        if (isset($data['editor_data'])) {
            $updates[] = 'editor_data = ?';
            $params[] = is_string($data['editor_data'])
                ? $data['editor_data']
                : json_encode($data['editor_data'], JSON_UNESCAPED_UNICODE);
            $types .= 's';
        }

        if (isset($data['type'])) {
            $updates[] = 'type = ?';
            $params[] = $data['type'];
            $types .= 's';
        }

        if (isset($data['unit_number'])) {
            $updates[] = 'unit_number = ?';
            $params[] = (int)$data['unit_number'];
            $types .= 'i';
        }

        if (empty($updates)) {
            return true;
        }

        $updates[] = 'updated_at = NOW()';
        $query = "UPDATE menus SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = (int)$id;
        $types .= 'i';

        $stmt = Database::getInstance()->execute($query, $params, $types);
        return $stmt !== false;
    }

    public function saveEditorState($id, $data)
    {
        return $this->update($id, [
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'cover_image_url' => $data['cover_image_url'] ?? null,
            'editor_data' => $data['editor_data'] ?? [],
        ]);
    }

    public function uploadCoverImage($file)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no upload da capa');
        }

        if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
            throw new Exception('Imagem muito grande. O limite é 5MB.');
        }

        $mime = $this->detectImageMimeType($file['tmp_name']);
        if (!in_array($mime, ALLOWED_MIME_TYPES, true)) {
            throw new Exception('Formato inválido. Use JPG, PNG, GIF ou WebP.');
        }

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        $uploadPath = UPLOAD_DIR . '/cardapios';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $filename = 'cardapio_' . bin2hex(random_bytes(8)) . '.' . ($extensions[$mime] ?? 'jpg');
        $destination = $uploadPath . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Não foi possível salvar a imagem da capa.');
        }

        return '/uploads/cardapios/' . $filename;
    }

    private function detectImageMimeType($path)
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $path);
                finfo_close($finfo);
                if ($mime) {
                    return $mime;
                }
            }
        }

        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($path);
            if ($mime) {
                return $mime;
            }
        }

        $imageInfo = @getimagesize($path);
        if (is_array($imageInfo) && !empty($imageInfo['mime'])) {
            return $imageInfo['mime'];
        }

        return '';
    }

    public function delete($id)
    {
        $query = "DELETE FROM menus WHERE id = ?";
        $stmt = Database::getInstance()->execute($query, [(int)$id], 'i');
        return $stmt !== false;
    }

    public function publish($id)
    {
        $menu = $this->findById($id);

        if (!$menu) {
            throw new Exception('Cardápio não encontrado');
        }

        $editorData = $this->decodeEditorData($menu['editor_data'] ?? null, $menu['name'] ?? 'Cardápio');
        if (!$this->editorDataHasProducts($editorData)) {
            $count = Database::getInstance()->fetchOne(
                "SELECT COUNT(*) AS total FROM menu_items WHERE menu_id = ? AND is_active = 1",
                [(int)$id],
                'i'
            );

            if (!$count || (int)$count['total'] === 0) {
                throw new Exception('O cardápio precisa ter pelo menos 1 produto ativo para ser publicado');
            }
        }

        $publicationNumber = $menu['publication_number'] ?: $this->nextPublicationNumber((int)$menu['restaurant_id']);
        $publicUrl = $menu['public_url'] ?: $this->buildPublicUrl($menu['restaurant_name'] ?? $menu['name'], $publicationNumber);

        $stmt = Database::getInstance()->execute(
            "UPDATE menus SET is_published = 1, publication_number = ?, public_url = ?, updated_at = NOW() WHERE id = ?",
            [(int)$publicationNumber, $publicUrl, (int)$id],
            'isi'
        );

        return $stmt !== false ? $publicUrl : false;
    }

    public function unpublish($id)
    {
        $stmt = Database::getInstance()->execute(
            "UPDATE menus SET is_published = 0, updated_at = NOW() WHERE id = ?",
            [(int)$id],
            'i'
        );
        return $stmt !== false;
    }

    public function belongsToRestaurant($menuId, $restaurantId)
    {
        $result = Database::getInstance()->fetchOne(
            "SELECT id FROM menus WHERE id = ? AND restaurant_id = ?",
            [(int)$menuId, (int)$restaurantId],
            'ii'
        );
        return $result !== null;
    }

    public function publicIdBelongsToRestaurant($publicId, $restaurantId)
    {
        $result = Database::getInstance()->fetchOne(
            "SELECT id FROM menus WHERE public_id = ? AND restaurant_id = ?",
            [strtoupper((string)$publicId), (int)$restaurantId],
            'si'
        );
        return $result !== null;
    }

    public function generatePublicId()
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        do {
            $id = '';
            for ($i = 0; $i < 5; $i++) {
                $id .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            $exists = Database::getInstance()->fetchOne(
                "SELECT id FROM menus WHERE public_id = ? LIMIT 1",
                [$id],
                's'
            );
        } while ($exists);

        return $id;
    }

    public function decodeEditorData($json, $fallbackName = 'Cardápio')
    {
        if (!$json) {
            return $this->defaultEditorData($fallbackName);
        }

        $data = json_decode((string)$json, true);
        if (!is_array($data)) {
            return $this->defaultEditorData($fallbackName);
        }

        return array_replace_recursive($this->defaultEditorData($fallbackName), $data);
    }

    public function defaultEditorData($name = 'Cardápio')
    {
        return [
            'restaurant' => [
                'name' => $name,
                'address' => '',
                'logo_url' => '',
                'status' => 'Aberto',
            ],
            'theme' => [
                'background_type' => 'solid',
                'background_color' => '#ffffff',
                'background_image' => '',
                'banner_color' => '#2b0d15',
                'banner_transparent' => false,
                'banner_size' => 'medium',
                'zoom' => 100,
            ],
            'categories' => [
                [
                    'id' => 'geral',
                    'name' => 'GERAL',
                    'titles' => [
                        [
                            'id' => 'titulo-inicial',
                            'name' => 'GERAL',
                            'subtitles' => [
                                [
                                    'id' => 'subtitulo-inicial',
                                    'name' => 'Descricao da categoria',
                                    'products' => [
                                        [
                                            'id' => 'produto-demo',
                                            'title' => 'Novo produto',
                                            'description' => 'Descricao do produto. Clique no lapis para editar foto, texto e preco.',
                                            'image_url' => '',
                                            'pricing_type' => 'single',
                                            'price' => 0,
                                            'variations' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'promotions' => [],
            'loyalty_enabled' => false,
        ];
    }

    private function editorDataHasProducts($editorData)
    {
        foreach (($editorData['categories'] ?? []) as $category) {
            foreach (($category['titles'] ?? []) as $title) {
                foreach (($title['subtitles'] ?? []) as $subtitle) {
                    if (!empty($subtitle['products'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function nextPublicationNumber($restaurantId)
    {
        $result = Database::getInstance()->fetchOne(
            "SELECT COALESCE(MAX(publication_number), 0) + 1 AS next_number FROM menus WHERE restaurant_id = ?",
            [(int)$restaurantId],
            'i'
        );
        return (int)($result['next_number'] ?? 1);
    }

    private function buildPublicUrl($restaurantName, $publicationNumber)
    {
        $base = $this->toAsciiLower((string)$restaurantName);
        $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base) ?: $base;
        $base = preg_replace('/[^a-z0-9]+/', '_', $base);
        $base = trim($base, '_') ?: 'restaurante';
        return $base . '_' . (int)$publicationNumber;
    }

    private function generateSlug($name, $restaurantId)
    {
        $slug = $this->toAsciiLower((string)$name);
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return ($slug ?: 'cardapio') . '-' . substr(md5($restaurantId . microtime(true)), 0, 6);
    }

    private function toAsciiLower($value)
    {
        $value = strtr($value, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ç' => 'c', 'Ç' => 'C',
            'Ã¡' => 'a', 'Ã ' => 'a', 'Ã£' => 'a', 'Ã¢' => 'a', 'Ã¤' => 'a',
            'Ã©' => 'e', 'Ã¨' => 'e', 'Ãª' => 'e', 'Ã«' => 'e',
            'Ã­' => 'i', 'Ã¬' => 'i', 'Ã®' => 'i', 'Ã¯' => 'i',
            'Ã³' => 'o', 'Ã²' => 'o', 'Ãµ' => 'o', 'Ã´' => 'o', 'Ã¶' => 'o',
            'Ãº' => 'u', 'Ã¹' => 'u', 'Ã»' => 'u', 'Ã¼' => 'u',
            'Ã§' => 'c',
        ]);

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }

    private function ensureEditorSchemaSupport()
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        $checked = true;
        $db = Database::getInstance();
        $columns = array_column($db->fetchAll('SHOW COLUMNS FROM menus'), 'Field');
        $alter = [];

        if (!in_array('public_id', $columns, true)) {
            $alter[] = "ADD COLUMN public_id CHAR(5) NULL UNIQUE AFTER id";
        }

        if (!in_array('cover_image_url', $columns, true)) {
            $alter[] = "ADD COLUMN cover_image_url VARCHAR(500) NULL AFTER description";
        }

        if (!in_array('editor_data', $columns, true)) {
            $alter[] = "ADD COLUMN editor_data JSON NULL AFTER cover_image_url";
        }

        if (!in_array('publication_number', $columns, true)) {
            $alter[] = "ADD COLUMN publication_number SMALLINT UNSIGNED NULL AFTER editor_data";
        }

        if (!in_array('public_url', $columns, true)) {
            $alter[] = "ADD COLUMN public_url VARCHAR(180) NULL UNIQUE AFTER publication_number";
        }

        if ($alter) {
            Database::getInstance()->execute('ALTER TABLE menus ' . implode(', ', $alter));
        }
    }
}
