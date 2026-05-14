<?php
/**
 * DELICACY - Cardápio Público (sem login)
 * Variáveis: $menu, $itemsGrouped
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($menu['name']); ?> - <?php echo htmlspecialchars($menu['restaurant_name']); ?></title>
    <meta name="description" content="Cardápio digital de <?php echo htmlspecialchars($menu['restaurant_name']); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; color: #333; }

        .menu-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 2rem 1rem; text-align: center;
        }
        .menu-hero h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .menu-hero p { opacity: 0.9; font-size: 1.1rem; }
        .menu-hero .restaurant-name { font-size: 0.95rem; opacity: 0.8; margin-top: 0.5rem; }

        .menu-container { max-width: 800px; margin: 0 auto; padding: 1rem; }

        .category-section { margin-bottom: 2rem; }
        .category-title {
            font-size: 1.3rem; font-weight: 700; color: #333;
            padding: 0.75rem 0; border-bottom: 2px solid #667eea;
            margin-bottom: 1rem; text-transform: capitalize;
        }

        .menu-item {
            background: white; border-radius: 12px; padding: 1rem;
            margin-bottom: 1rem; display: flex; gap: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .menu-item:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.12); }

        .menu-item-image {
            width: 100px; height: 100px; border-radius: 8px;
            object-fit: cover; flex-shrink: 0;
        }
        .menu-item-placeholder {
            width: 100px; height: 100px; border-radius: 8px;
            background: linear-gradient(135deg, #667eea22, #764ba222);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; flex-shrink: 0;
        }

        .menu-item-info { flex: 1; }
        .menu-item-name { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.25rem; }
        .menu-item-desc { color: #666; font-size: 0.9rem; margin-bottom: 0.5rem; line-height: 1.4; }
        .menu-item-bottom { display: flex; justify-content: space-between; align-items: center; }
        .menu-item-price { font-size: 1.2rem; font-weight: 700; color: #667eea; }

        .btn-add-cart {
            padding: 0.5rem 1rem; background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; border: none; border-radius: 6px; font-size: 0.9rem;
            font-weight: 600; cursor: pointer; transition: transform 0.2s;
        }
        .btn-add-cart:hover { transform: scale(1.05); }

        /* Carrinho flutuante */
        .cart-float {
            position: fixed; bottom: 1rem; right: 1rem; z-index: 1000;
        }
        .cart-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; border: none; border-radius: 50px;
            padding: 1rem 1.5rem; font-size: 1rem; font-weight: 600;
            cursor: pointer; box-shadow: 0 4px 20px rgba(102,126,234,0.4);
            display: flex; align-items: center; gap: 0.5rem;
            transition: transform 0.2s;
        }
        .cart-btn:hover { transform: scale(1.05); }
        .cart-count {
            background: #e74c3c; color: white; border-radius: 50%;
            width: 24px; height: 24px; display: flex; align-items: center;
            justify-content: center; font-size: 0.8rem; font-weight: 700;
        }

        /* Modal do carrinho */
        .cart-modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 2000;
        }
        .cart-modal-overlay.active { display: flex; align-items: flex-end; justify-content: center; }

        .cart-modal {
            background: white; border-radius: 16px 16px 0 0; width: 100%; max-width: 600px;
            max-height: 80vh; overflow-y: auto; padding: 1.5rem;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }

        .cart-modal-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #eee;
        }
        .cart-modal-header h2 { font-size: 1.3rem; margin: 0; }
        .cart-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; }

        .cart-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;
        }
        .cart-item-info h4 { margin: 0 0 0.25rem; font-size: 1rem; }
        .cart-item-info small { color: #666; }
        .cart-item-controls { display: flex; align-items: center; gap: 0.5rem; }
        .cart-item-controls button {
            width: 28px; height: 28px; border-radius: 50%; border: 1px solid #ddd;
            background: white; cursor: pointer; font-size: 1rem; display: flex;
            align-items: center; justify-content: center;
        }
        .cart-item-controls button:hover { background: #f0f0f0; }
        .cart-item-qty { font-weight: 600; min-width: 24px; text-align: center; }

        .cart-total {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1rem 0; margin-top: 0.5rem; border-top: 2px solid #333;
            font-size: 1.2rem; font-weight: 700;
        }

        .cart-actions { margin-top: 1rem; }
        .btn-checkout {
            width: 100%; padding: 1rem; background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white; border: none; border-radius: 8px; font-size: 1.1rem;
            font-weight: 600; cursor: pointer; transition: transform 0.2s;
        }
        .btn-checkout:hover { transform: translateY(-2px); }
        .btn-checkout:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .cart-empty { text-align: center; padding: 2rem; color: #999; }

        /* Formulário de pedido no modal */
        .order-form { margin-top: 1rem; }
        .order-form .form-group { margin-bottom: 1rem; }
        .order-form label { display: block; margin-bottom: 0.3rem; font-weight: 600; font-size: 0.9rem; }
        .order-form input, .order-form textarea {
            width: 100%; padding: 0.65rem; border: 1px solid #ddd; border-radius: 6px;
            font-size: 1rem; box-sizing: border-box;
        }
        .order-form textarea { min-height: 60px; resize: vertical; }

        @media (max-width: 640px) {
            .menu-hero h1 { font-size: 1.5rem; }
            .menu-item { flex-direction: column; }
            .menu-item-image, .menu-item-placeholder { width: 100%; height: 150px; }
        }
    </style>
</head>
<body>
    <div class="menu-hero">
        <h1>📋 <?php echo htmlspecialchars($menu['name']); ?></h1>
        <?php if (!empty($menu['description'])): ?>
            <p><?php echo htmlspecialchars($menu['description']); ?></p>
        <?php endif; ?>
        <div class="restaurant-name">🏪 <?php echo htmlspecialchars($menu['restaurant_name']); ?></div>
    </div>

    <div class="menu-container">
        <?php if (empty($itemsGrouped)): ?>
            <div class="empty-state" style="text-align:center;padding:3rem;">
                <h2>Cardápio vazio</h2>
                <p>Ainda não há pratos neste cardápio.</p>
            </div>
        <?php else: ?>
            <?php
            $categoryNames = [
                'entrada' => '🥗 Entradas',
                'prato_principal' => '🍽️ Pratos Principais',
                'bebida' => '🥤 Bebidas',
                'sobremesa' => '🍰 Sobremesas',
                'acompanhamento' => '🍟 Acompanhamentos',
                'outros' => '📋 Outros'
            ];
            ?>
            <?php foreach ($itemsGrouped as $category => $items): ?>
                <div class="category-section">
                    <h2 class="category-title"><?php echo $categoryNames[$category] ?? '📋 ' . ucfirst($category); ?></h2>
                    <?php foreach ($items as $item): ?>
                        <div class="menu-item" data-item-id="<?php echo $item['id']; ?>">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?php echo BASE_URL . htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image">
                            <?php else: ?>
                                <div class="menu-item-placeholder">🍽️</div>
                            <?php endif; ?>
                            <div class="menu-item-info">
                                <div class="menu-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <?php if (!empty($item['description'])): ?>
                                    <div class="menu-item-desc"><?php echo htmlspecialchars($item['description']); ?></div>
                                <?php endif; ?>
                                <div class="menu-item-bottom">
                                    <span class="menu-item-price"><?php echo formatCurrency($item['price']); ?></span>
                                    <button class="btn-add-cart" 
                                            onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>', <?php echo $item['price']; ?>)">
                                        ➕ Adicionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Botão flutuante do carrinho -->
    <div class="cart-float" id="cartFloat" style="display:none;">
        <button class="cart-btn" onclick="openCart()">
            🛒 Carrinho <span class="cart-count" id="cartCount">0</span>
        </button>
    </div>

    <!-- Modal do carrinho -->
    <div class="cart-modal-overlay" id="cartOverlay">
        <div class="cart-modal">
            <div class="cart-modal-header">
                <h2>🛒 Seu Carrinho</h2>
                <button class="cart-close" onclick="closeCart()">✕</button>
            </div>
            <div id="cartItems"></div>
            <div class="cart-total" id="cartTotalSection" style="display:none;">
                <span>Total:</span>
                <span id="cartTotal">R$ 0,00</span>
            </div>
            <div class="cart-actions" id="cartActions" style="display:none;">
                <div class="order-form" id="orderForm">
                    <div class="form-group">
                        <label for="customer_name">Seu Nome *</label>
                        <input type="text" id="customer_name" placeholder="Seu nome completo" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">WhatsApp *</label>
                        <input type="tel" id="customer_phone" placeholder="(11) 99999-9999" required>
                    </div>
                    <div class="form-group">
                        <label for="delivery_address">Endereço de Entrega</label>
                        <textarea id="delivery_address" placeholder="Rua, número, bairro..."></textarea>
                    </div>
                </div>
                <button class="btn-checkout" id="btnCheckout" onclick="submitOrder()">
                    ✅ Finalizar Pedido
                </button>
            </div>
        </div>
    </div>

    <!-- Form oculto para enviar pedido -->
    <form id="orderSubmitForm" method="POST" action="<?php echo BASE_URL; ?>/pedido.php" style="display:none;">
        <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
        <input type="hidden" name="cart_data" id="cartDataInput">
        <input type="hidden" name="customer_name" id="customerNameInput">
        <input type="hidden" name="customer_phone" id="customerPhoneInput">
        <input type="hidden" name="delivery_address" id="deliveryAddressInput">
    </form>

    <script src="<?php echo BASE_URL; ?>/js/cart.js"></script>
</body>
</html>
