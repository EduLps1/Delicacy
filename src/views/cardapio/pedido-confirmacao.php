<?php
/**
 * DELICACY - Confirmação de Pedido
 * Variáveis: $order, $orderItems
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Delicacy</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }

        .confirmation-card {
            background: white; border-radius: 16px; padding: 2rem; max-width: 500px; width: 100%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center;
        }
        .success-icon { font-size: 4rem; margin-bottom: 1rem; }
        .confirmation-card h1 { font-size: 1.5rem; color: #27ae60; margin-bottom: 0.5rem; }
        .confirmation-card .order-number { font-size: 2rem; font-weight: 700; color: #333; margin: 1rem 0; }
        .confirmation-card .order-number span { color: #667eea; }

        .order-details { text-align: left; margin: 1.5rem 0; padding: 1rem; background: #f9f9f9; border-radius: 8px; }
        .order-details h3 { margin-bottom: 0.75rem; font-size: 1rem; color: #333; }
        .order-detail-item { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #eee; font-size: 0.95rem; }
        .order-detail-item:last-child { border-bottom: none; }
        .order-detail-item .qty { color: #666; }

        .order-total { display: flex; justify-content: space-between; padding: 1rem 0; font-size: 1.2rem; font-weight: 700; border-top: 2px solid #333; margin-top: 0.5rem; }

        .order-info { text-align: left; margin: 1rem 0; font-size: 0.95rem; color: #666; }
        .order-info p { margin-bottom: 0.5rem; }
        .order-info strong { color: #333; }

        .status-badge { display: inline-block; padding: 0.5rem 1rem; background: #fef3c7; color: #d97706; border-radius: 50px; font-weight: 600; margin: 1rem 0; }

        .actions { margin-top: 1.5rem; }
        .actions a {
            display: inline-block; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: transform 0.2s;
        }
        .actions a:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <div class="success-icon">✅</div>
        <h1>Pedido Confirmado!</h1>
        <div class="order-number">Pedido <span>#<?php echo $order['id']; ?></span></div>

        <div class="status-badge">⏳ <?php echo ucfirst($order['status']); ?></div>

        <?php if (!empty($order['customer_name'])): ?>
            <div class="order-info">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <?php if (!empty($order['customer_phone'])): ?>
                    <p><strong>WhatsApp:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($order['delivery_address'])): ?>
                    <p><strong>Entrega:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="order-details">
            <h3>🛒 Itens do Pedido</h3>
            <?php foreach ($orderItems as $item): ?>
                <div class="order-detail-item">
                    <span>
                        <?php echo htmlspecialchars($item['name']); ?>
                        <span class="qty">x<?php echo $item['quantity']; ?></span>
                    </span>
                    <span><?php echo formatCurrency($item['price_at_moment'] * $item['quantity']); ?></span>
                </div>
            <?php endforeach; ?>
            <div class="order-total">
                <span>Total</span>
                <span><?php echo formatCurrency($order['total_value']); ?></span>
            </div>
        </div>

        <p style="color:#666; font-size: 0.9rem;">
            📋 Anote o número do seu pedido.<br>
            O restaurante receberá seu pedido em instantes.
        </p>

        <div class="actions">
            <a href="<?php echo BASE_URL; ?>">🏠 Voltar ao Início</a>
        </div>
    </div>

    <script>
        // Limpa carrinho após confirmação
        localStorage.removeItem('delicacy_cart');
    </script>
</body>
</html>
