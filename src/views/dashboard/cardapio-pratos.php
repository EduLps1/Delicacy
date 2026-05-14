<?php
/**
 * DELICACY - Editar CardÃ¡pio + Gerenciar Pratos
 * VariÃ¡veis: $menu, $items, $restaurant, $csrf_token
 */
$message = getSessionMessage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar <?php echo htmlspecialchars($menu['name']); ?> - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-logo">
                <a href="<?php echo BASE_URL; ?>" class="logo"><h1>Delicacy</h1></a>
            </div>
            <nav class="header-nav">
                <span class="user-info">OlÃ¡, <strong><?php echo htmlspecialchars(getAuthUser()['name']); ?></strong></span>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn-logout">Sair</a>
            </nav>
        </div>
    </header>

    <div class="layout-with-sidebar">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <h3 class="nav-title">Restaurante</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/" class="nav-link"> Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="nav-link active"> CardÃ¡pio</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php" class="nav-link"> Pedidos</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>âœï¸ <?php echo htmlspecialchars($menu['name']); ?></h1>
                    <p><a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php">â† Voltar para lista</a></p>
                </div>

                <?php if ($message): ?>
                    <div class="message-container">
                        <div class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                            <span><?php echo htmlspecialchars($message['text']); ?></span>
                            <button onclick="this.parentElement.parentElement.style.display='none';">Ã—</button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Editar dados do cardÃ¡pio -->
                <div class="card">
                    <div class="card-header"><h2> Dados do CardÃ¡pio</h2></div>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_menu">
                        <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <div class="form-group">
                            <label for="name">Nome do CardÃ¡pio *</label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo htmlspecialchars($menu['name']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">DescriÃ§Ã£o</label>
                            <textarea id="description" name="description"><?php echo htmlspecialchars($menu['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="type">Tipo</label>
                                <select id="type" name="type">
                                    <option value="both" <?php echo $menu['type'] === 'both' ? 'selected' : ''; ?>>Online + Presencial</option>
                                    <option value="online" <?php echo $menu['type'] === 'online' ? 'selected' : ''; ?>>Delivery</option>
                                    <option value="presencial" <?php echo $menu['type'] === 'presencial' ? 'selected' : ''; ?>>Presencial</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="unit_number">Unidade</label>
                                <input type="number" id="unit_number" name="unit_number" min="1"
                                       value="<?php echo (int)$menu['unit_number']; ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">ðŸ’¾ Salvar CardÃ¡pio</button>
                    </form>
                </div>

                <!-- Adicionar novo prato -->
                <div class="card">
                    <div class="card-header"><h2> Adicionar Prato</h2></div>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_item">
                        <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="item_name">Nome do Prato *</label>
                                <input type="text" id="item_name" name="item_name" required placeholder="Ex: HambÃºrguer Artesanal">
                            </div>
                            <div class="form-group">
                                <label for="item_price">PreÃ§o (R$) *</label>
                                <input type="number" id="item_price" name="item_price" required step="0.01" min="0" placeholder="29.90">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="item_category">Categoria</label>
                                <select id="item_category" name="item_category">
                                    <option value="entrada"> Entrada</option>
                                    <option value="prato_principal" selected> Prato Principal</option>
                                    <option value="bebida"> Bebida</option>
                                    <option value="sobremesa"> Sobremesa</option>
                                    <option value="acompanhamento"> Acompanhamento</option>
                                    <option value="outros"> Outros</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="item_image">Imagem</label>
                                <input type="file" id="item_image" name="item_image" accept="image/*">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="item_description">DescriÃ§Ã£o</label>
                            <textarea id="item_description" name="item_description" placeholder="Ingredientes, informaÃ§Ãµes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success">âž• Adicionar Prato</button>
                    </form>
                </div>

                <!-- Lista de pratos -->
                <div class="card">
                    <div class="card-header">
                        <h2>ðŸ½ï¸ Pratos (<?php echo count($items); ?>)</h2>
                    </div>

                    <?php if (empty($items)): ?>
                        <div class="empty-state-small">
                            <p>Nenhum prato adicionado ainda. Use o formulÃ¡rio acima para adicionar.</p>
                        </div>
                    <?php else: ?>
                        <div class="items-list">
                            <?php foreach ($items as $item): ?>
                                <div class="item-row <?php echo $item['is_active'] ? '' : 'item-inactive'; ?>">
                                    <div class="item-info">
                                        <?php if (!empty($item['image_url'])): ?>
                                            <img src="<?php echo BASE_URL . htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-thumb">
                                        <?php else: ?>
                                            <div class="item-thumb item-thumb-placeholder">ðŸ½ï¸</div>
                                        <?php endif; ?>
                                        <div class="item-details">
                                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                            <p class="item-desc"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                                            <div class="item-meta">
                                                <span class="badge badge-primary"><?php echo htmlspecialchars($item['category']); ?></span>
                                                <span class="item-price"><?php echo formatCurrency($item['price']); ?></span>
                                                <?php if (!$item['is_active']): ?>
                                                    <span class="badge badge-danger">Inativo</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_item">
                                            <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                            <button type="submit" class="btn btn-small <?php echo $item['is_active'] ? 'btn-secondary' : 'btn-success'; ?>" title="<?php echo $item['is_active'] ? 'Desativar' : 'Ativar'; ?>">
                                                <?php echo $item['is_active'] ? 'â¸ï¸' : 'â–¶ï¸'; ?>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir este prato?')">
                                            <input type="hidden" name="action" value="delete_item">
                                            <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                            <button type="submit" class="btn btn-small btn-danger">ðŸ—‘ï¸</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Delicacy. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
