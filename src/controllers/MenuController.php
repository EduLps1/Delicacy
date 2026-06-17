<?php
/**
 * DELICACY - Menu Controller
 * 
 * Gerencia CRUD de cardÃ¡pios e pratos.
 * Acesso restrito a admin_restaurant.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/Menu.php';
require_once __DIR__ . '/../models/MenuItem.php';
require_once __DIR__ . '/../services/JwtService.php';

class MenuController
{
    private $restaurantModel;
    private $menuModel;
    private $menuItemModel;
    private $userModel;
    private $restaurant;

    public function __construct()
    {
        requireRole(ROLE_ADMIN_RESTAURANT);
        $this->userModel = new User();
        $this->restaurantModel = new Restaurant();
        $this->menuModel = new Menu();
        $this->menuItemModel = new MenuItem();

        // Carrega restaurante do usuÃ¡rio logado
        $userId = getAuthUserId();
        $this->restaurant = $this->restaurantModel->findByUserId($userId);
        if (!$this->restaurant) {
            $this->restaurant = $this->restaurantModel->ensureTestRestaurantForUser($userId, 'Teste');
        }
    }

    /**
     * Lista cardÃ¡pios do restaurante
     */
    public function listMenus()
    {
        if (!$this->restaurant) {
            $user = getAuthUser() ?: ['name' => 'Restaurante'];
            $restaurant = [
                'id' => 0,
                'name' => $user['name'] ?? 'Restaurante'
            ];
            $menus = [];
            $draftsCount = 0;
            $menusCount = 0;
            $menuLimit = 0;
            $canCreateDraft = false;
            $csrf_token = generateCSRFToken();

            require_once VIEWS_PATH . '/dashboard/cardapios.php';
            return;
        }

        $menus = $this->menuModel->findByRestaurantId($this->restaurant['id']);
        foreach ($menus as &$menu) {
            if (empty($menu['public_id'])) {
                $menu['public_id'] = $this->menuModel->ensurePublicId($menu);
            }
        }
        unset($menu);
        $restaurant = $this->restaurant;
        $draftsCount = $this->menuModel->countDraftsByRestaurantId($this->restaurant['id']);
        $menusCount = $this->menuModel->countByRestaurantId($this->restaurant['id']);
        $menuLimit = $this->menuModel->getPlanMenuLimit($this->restaurant['plan_type'] ?? PLAN_BASIC);
        $canCreateDraft = $this->menuModel->canCreateDraft($this->restaurant);
        $csrf_token = generateCSRFToken();

        require_once VIEWS_PATH . '/dashboard/cardapios.php';
    }

    /**
     * Exibe formulÃ¡rio de novo cardÃ¡pio
     */
    public function showCreateForm()
    {
        if (!$this->restaurant) {
            $this->restaurant = $this->restaurantModel->ensureTestRestaurantForUser(getAuthUserId(), 'Teste');
        }

        $restaurant = $this->restaurant;
        $menu = null; // Novo cardÃ¡pio
        $csrf_token = generateCSRFToken();

        require_once VIEWS_PATH . '/dashboard/cardapio-form.php';
    }

    /**
     * Cria um rascunho e abre o Editor Visual em modo criacao.
     */
    public function showVisualCreate()
    {
        if (!$this->restaurant) {
            $this->restaurant = $this->restaurantModel->ensureTestRestaurantForUser(getAuthUserId(), 'Teste');
        }

        if (!$this->menuModel->canCreateDraft($this->restaurant)) {
            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapios.php',
                'Limite de rascunhos atingido. Publique ou exclua um rascunho para liberar um novo cardapio.',
                'warning'
            );
        }

        $name = 'Novo Cardapio';
        $menuId = $this->menuModel->create([
            'restaurant_id' => $this->restaurant['id'],
            'name' => $name,
            'description' => '',
            'type' => MENU_TYPE_BOTH,
            'unit_number' => 1,
            'editor_data' => $this->menuModel->defaultEditorData($this->restaurant['name'] ?? $name),
        ]);

        $menu = $this->menuModel->findById($menuId);
        redirectWithMessage(
            BASE_URL . '/admin_contratante/cardapios/' . rawurlencode($menu['public_id']) . '/editar',
            'Rascunho criado. Monte seu cardapio no Editor Visual.',
            'success'
        );
    }

    /**
     * Abre Editor Visual por ID curto.
     */
    public function showVisualEditor($publicId)
    {
        if (!$this->restaurant) {
            $this->restaurant = $this->restaurantModel->ensureTestRestaurantForUser(getAuthUserId(), 'Teste');
        }

        $menu = $this->menuModel->findByPublicId($publicId);

        if (!$menu || (int)$menu['restaurant_id'] !== (int)$this->restaurant['id']) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Cardapio nao encontrado para este restaurante', 'error');
        }

        $restaurant = $this->restaurant;
        $editorData = $this->menuModel->decodeEditorData($menu['editor_data'] ?? null, $menu['name'] ?? 'Cardapio');
        $csrf_token = generateCSRFToken();
        $editor_token = JwtService::issue([
            'scope' => 'menu_editor',
            'restaurant_id' => (int)$this->restaurant['id'],
            'menu_public_id' => $menu['public_id'],
            'user_id' => (int)getAuthUserId(),
        ]);
        $publicUrl = $this->buildMenuPublicUrl($menu);

        require_once VIEWS_PATH . '/dashboard/menu-editor.php';
    }

    /**
     * Salva o estado JSON do Editor Visual.
     */
    public function saveVisualEditor()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Metodo nao permitido'], 405);
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            jsonResponse(['error' => 'Token invalido'], 403);
        }

        $publicId = strtoupper((string)($_POST['public_id'] ?? ''));
        $this->assertEditorToken($publicId);
        $menu = $this->menuModel->findByPublicId($publicId);

        if (!$menu || !$this->menuModel->publicIdBelongsToRestaurant($publicId, $this->restaurant['id'])) {
            jsonResponse(['error' => 'Cardapio nao encontrado'], 404);
        }

        $payload = json_decode($_POST['editor_data'] ?? '', true);
        if (!is_array($payload)) {
            jsonResponse(['error' => 'Estado do editor invalido'], 422);
        }

        $name = sanitizeText($payload['restaurant']['name'] ?? $menu['name']);
        $cover = sanitizeText($payload['cover_image_url'] ?? ($menu['cover_image_url'] ?? ''));

        $this->menuModel->saveEditorState($menu['id'], [
            'name' => $name ?: $menu['name'],
            'description' => sanitizeText($payload['description'] ?? ($menu['description'] ?? '')),
            'cover_image_url' => $cover ?: null,
            'editor_data' => $payload,
        ]);

        jsonResponse(['ok' => true, 'message' => 'Cardapio salvo', 'public_id' => $publicId]);
    }

    /**
     * Publica cardapio pelo editor visual e retorna URL publica.
     */
    public function publishVisualMenu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Metodo nao permitido'], 405);
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            jsonResponse(['error' => 'Token invalido'], 403);
        }

        $publicId = strtoupper((string)($_POST['public_id'] ?? ''));
        $this->assertEditorToken($publicId);
        $menu = $this->menuModel->findByPublicId($publicId);

        if (!$menu || !$this->menuModel->publicIdBelongsToRestaurant($publicId, $this->restaurant['id'])) {
            jsonResponse(['error' => 'Cardapio nao encontrado'], 404);
        }

        try {
            $publicSlug = $this->menuModel->publish($menu['id']);
            $menu = $this->menuModel->findByPublicId($publicId);
            jsonResponse([
                'ok' => true,
                'message' => 'Cardapio publicado com sucesso',
                'url' => $this->buildMenuPublicUrl($menu ?: ['public_url' => $publicSlug]),
                'publication_number' => (int)($menu['publication_number'] ?? 0),
            ]);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Upload de assets do editor visual (logo e imagem de fundo).
     */
    public function uploadEditorAsset()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Metodo nao permitido'], 405);
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            jsonResponse(['error' => 'Token invalido'], 403);
        }

        $publicId = strtoupper((string)($_POST['public_id'] ?? ''));
        $this->assertEditorToken($publicId);

        if (!$this->menuModel->publicIdBelongsToRestaurant($publicId, $this->restaurant['id'])) {
            jsonResponse(['error' => 'Cardapio nao encontrado'], 404);
        }

        if (empty($_FILES['asset']) || ($_FILES['asset']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            jsonResponse(['error' => 'Nenhum arquivo enviado'], 422);
        }

        try {
            $url = $this->menuModel->uploadCoverImage($_FILES['asset']);
            jsonResponse(['ok' => true, 'url' => $url]);
        } catch (Exception $e) {
            jsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Exclusao segura com CONFIRMAR e senha quando publicado.
     */
    public function secureDeleteMenu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Token invalido', 'error');
        }

        $publicId = strtoupper((string)($_POST['public_id'] ?? ''));
        $confirmation = trim((string)($_POST['confirmation'] ?? ''));
        $menu = $this->menuModel->findByPublicId($publicId);

        if (!$menu || !$this->menuModel->publicIdBelongsToRestaurant($publicId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Cardapio nao encontrado', 'error');
        }

        if ($confirmation !== 'CONFIRMAR') {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Digite CONFIRMAR para excluir', 'error');
        }

        if ((int)$menu['is_published'] === 1) {
            $authUser = getAuthUser();
            $user = $authUser && !empty($authUser['email']) ? $this->userModel->findByEmail($authUser['email']) : null;
            $password = (string)($_POST['password'] ?? '');

            if (!$user || !verifyPassword($password, $user['password'])) {
                redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Senha invalida para excluir cardapio publicado', 'error');
            }
        }

        $this->menuModel->delete($menu['id']);
        redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Cardapio excluido com seguranca', 'success');
    }

    /**
     * Atualizacao rapida de nome e capa diretamente no card da listagem.
     */
    public function quickUpdateMenu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Token invalido', 'error');
        }

        $publicId = strtoupper((string)($_POST['public_id'] ?? ''));
        $menu = $this->menuModel->findByPublicId($publicId);

        if (!$menu || !$this->menuModel->publicIdBelongsToRestaurant($publicId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Cardapio nao encontrado', 'error');
        }

        $coverImage = $menu['cover_image_url'] ?? null;

        try {
            if (isset($_FILES['cover_image']) && ($_FILES['cover_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $coverImage = $this->menuModel->uploadCoverImage($_FILES['cover_image']);
            }

            $this->menuModel->update($menu['id'], [
                'name' => sanitizeText($_POST['name'] ?? $menu['name']),
                'cover_image_url' => $coverImage,
            ]);
        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', $e->getMessage(), 'error');
        }

        redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Cardapio atualizado', 'success');
    }

    private function buildMenuPublicUrl($menu)
    {
        $slug = $menu['public_url'] ?? $menu['slug'] ?? '';
        return BASE_URL . '/' . rawurlencode($slug);
    }

    /**
     * Processa criaÃ§Ã£o de cardÃ¡pio
     */
    public function createMenu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin-contratante/cardapios.php');
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio-novo.php', 'Token de seguranÃ§a invÃ¡lido', 'error');
        }

        try {
            $menuId = $this->menuModel->create([
                'restaurant_id' => $this->restaurant['id'],
                'name' => sanitizeText($_POST['name'] ?? ''),
                'description' => sanitizeText($_POST['description'] ?? ''),
                'type' => sanitizeText($_POST['type'] ?? MENU_TYPE_BOTH),
                'unit_number' => (int)($_POST['unit_number'] ?? 1)
            ]);

            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                'CardÃ¡pio criado! Agora adicione os pratos.',
                'success'
            );

        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio-novo.php', $e->getMessage(), 'error');
        }
    }

    /**
     * Exibe formulÃ¡rio de ediÃ§Ã£o + gerenciamento de pratos
     */
    public function showEditForm()
    {
        $menuId = (int)($_GET['id'] ?? 0);

        if (!$this->restaurant) {
            $this->restaurant = $this->restaurantModel->ensureTestRestaurantForUser(getAuthUserId(), 'Teste');
        }

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
        }

        $menu = $this->menuModel->findById($menuId);
        $items = $this->menuItemModel->findByMenuId($menuId);
        $restaurant = $this->restaurant;
        $csrf_token = generateCSRFToken();

        require_once VIEWS_PATH . '/dashboard/cardapio-pratos.php';
    }

    /**
     * Processa atualizaÃ§Ã£o do cardÃ¡pio
     */
    public function updateMenu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin-contratante/cardapios.php');
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            jsonResponse(['error' => 'Token invÃ¡lido'], 403);
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            jsonResponse(['error' => 'CardÃ¡pio nÃ£o encontrado'], 404);
        }

        try {
            $this->menuModel->update($menuId, [
                'name' => sanitizeText($_POST['name'] ?? ''),
                'description' => sanitizeText($_POST['description'] ?? ''),
                'type' => sanitizeText($_POST['type'] ?? MENU_TYPE_BOTH),
                'unit_number' => (int)($_POST['unit_number'] ?? 1)
            ]);

            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                'CardÃ¡pio atualizado com sucesso!',
                'success'
            );

        } catch (Exception $e) {
            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Deleta cardÃ¡pio
     */
    public function deleteMenu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
        }

        try {
            $this->menuModel->delete($menuId);
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'CardÃ¡pio excluÃ­do com sucesso!', 'success');
        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', $e->getMessage(), 'error');
        }
    }

    /**
     * Publica/Despublica cardÃ¡pio
     */
    public function togglePublish()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
        }

        try {
            $menu = $this->menuModel->findById($menuId);

            if ($menu['is_published']) {
                $this->menuModel->unpublish($menuId);
                redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'CardÃ¡pio despublicado', 'success');
            } else {
                $this->menuModel->publish($menuId);
                redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'CardÃ¡pio publicado com sucesso!', 'success');
            }

        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', $e->getMessage(), 'error');
        }
    }

    // =============================================
    // PRATOS (Menu Items)
    // =============================================

    /**
     * Adiciona prato ao cardÃ¡pio
     */
    public function addItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
        }

        try {
            $data = [
                'menu_id' => $menuId,
                'name' => sanitizeText($_POST['item_name'] ?? ''),
                'description' => sanitizeText($_POST['item_description'] ?? ''),
                'price' => (float)($_POST['item_price'] ?? 0),
                'category' => sanitizeText($_POST['item_category'] ?? 'outros'),
                'image_url' => null
            ];

            // Upload de imagem se enviada
            if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
                $data['image_url'] = $this->menuItemModel->uploadImage($_FILES['item_image']);
            }

            $this->menuItemModel->create($data);

            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                'Prato adicionado com sucesso!',
                'success'
            );

        } catch (Exception $e) {
            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Atualiza prato
     */
    public function updateItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);
        $itemId = (int)($_POST['item_id'] ?? 0);

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
        }

        try {
            $data = [
                'name' => sanitizeText($_POST['item_name'] ?? ''),
                'description' => sanitizeText($_POST['item_description'] ?? ''),
                'price' => (float)($_POST['item_price'] ?? 0),
                'category' => sanitizeText($_POST['item_category'] ?? 'outros')
            ];

            // Upload de imagem se enviada
            if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
                $data['image_url'] = $this->menuItemModel->uploadImage($_FILES['item_image']);
            }

            $this->menuItemModel->update($itemId, $data);

            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                'Prato atualizado com sucesso!',
                'success'
            );

        } catch (Exception $e) {
            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Deleta prato
     */
    public function deleteItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);
        $itemId = (int)($_POST['item_id'] ?? 0);

        try {
            $this->menuItemModel->delete($itemId);
            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                'Prato removido com sucesso!',
                'success'
            );
        } catch (Exception $e) {
            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Toggle ativo/inativo de prato
     */
    public function toggleItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($csrf_token)) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapios.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);
        $itemId = (int)($_POST['item_id'] ?? 0);

        try {
            $this->menuItemModel->toggleActive($itemId);
            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                'Status do prato atualizado!',
                'success'
            );
        } catch (Exception $e) {
            redirectWithMessage(
                BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . $menuId,
                $e->getMessage(),
                'error'
            );
        }
    }

    private function assertEditorToken($publicId)
    {
        $claims = JwtService::verify($_POST['jwt_token'] ?? '');

        if (!$claims ||
            ($claims['scope'] ?? '') !== 'menu_editor' ||
            strtoupper((string)($claims['menu_public_id'] ?? '')) !== strtoupper((string)$publicId) ||
            (int)($claims['restaurant_id'] ?? 0) !== (int)$this->restaurant['id'] ||
            (int)($claims['user_id'] ?? 0) !== (int)getAuthUserId()
        ) {
            jsonResponse(['error' => 'Token JWT invalido para este tenant/cardapio'], 403);
        }
    }
}
