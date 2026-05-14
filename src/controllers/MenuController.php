<?php
/**
 * DELICACY - Menu Controller
 * 
 * Gerencia CRUD de cardÃ¡pios e pratos.
 * Acesso restrito a admin_restaurant.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/Menu.php';
require_once __DIR__ . '/../models/MenuItem.php';

class MenuController
{
    private $restaurantModel;
    private $menuModel;
    private $menuItemModel;
    private $restaurant;

    public function __construct()
    {
        requireRole(ROLE_ADMIN_RESTAURANT);
        $this->restaurantModel = new Restaurant();
        $this->menuModel = new Menu();
        $this->menuItemModel = new MenuItem();

        // Carrega restaurante do usuÃ¡rio logado
        $userId = getAuthUserId();
        $this->restaurant = $this->restaurantModel->findByUserId($userId);
    }

    /**
     * Lista cardÃ¡pios do restaurante
     */
    public function listMenus()
    {
        if (!$this->restaurant) {
            redirectWithMessage(BASE_URL . '/admin-contratante/', 'Cadastre um restaurante primeiro', 'warning');
        }

        $menus = $this->menuModel->findByRestaurantId($this->restaurant['id']);
        $restaurant = $this->restaurant;
        $csrf_token = generateCSRFToken();

        require_once VIEWS_PATH . '/dashboard/cardapios.php';
    }

    /**
     * Exibe formulÃ¡rio de novo cardÃ¡pio
     */
    public function showCreateForm()
    {
        if (!$this->restaurant) {
            redirectWithMessage(BASE_URL . '/admin-contratante/', 'Cadastre um restaurante primeiro', 'warning');
        }

        $restaurant = $this->restaurant;
        $menu = null; // Novo cardÃ¡pio
        $csrf_token = generateCSRFToken();

        require_once VIEWS_PATH . '/dashboard/cardapio-form.php';
    }

    /**
     * Processa criaÃ§Ã£o de cardÃ¡pio
     */
    public function createMenu()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin-contratante/cardapio.php');
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
            redirectWithMessage(BASE_URL . '/admin-contratante/', 'Cadastre um restaurante primeiro', 'warning');
        }

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
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
            header('Location: ' . BASE_URL . '/admin-contratante/cardapio.php');
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
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
        }

        try {
            $this->menuModel->delete($menuId);
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'CardÃ¡pio excluÃ­do com sucesso!', 'success');
        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', $e->getMessage(), 'error');
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
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
        }

        try {
            $menu = $this->menuModel->findById($menuId);

            if ($menu['is_published']) {
                $this->menuModel->unpublish($menuId);
                redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'CardÃ¡pio despublicado', 'success');
            } else {
                $this->menuModel->publish($menuId);
                redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'CardÃ¡pio publicado com sucesso!', 'success');
            }

        } catch (Exception $e) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', $e->getMessage(), 'error');
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
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
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
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'Token invÃ¡lido', 'error');
        }

        $menuId = (int)($_POST['menu_id'] ?? 0);
        $itemId = (int)($_POST['item_id'] ?? 0);

        if (!$menuId || !$this->menuModel->belongsToRestaurant($menuId, $this->restaurant['id'])) {
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'CardÃ¡pio nÃ£o encontrado', 'error');
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
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'Token invÃ¡lido', 'error');
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
            redirectWithMessage(BASE_URL . '/admin-contratante/cardapio.php', 'Token invÃ¡lido', 'error');
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
}
