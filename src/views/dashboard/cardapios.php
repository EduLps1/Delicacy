<?php
/**
 * DELICACY - Lista de CardÃ¡pios (Admin)
 * VariÃ¡veis: $menus, $restaurant, $csrf_token
 */
$message = getSessionMessage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CardÃ¡pios - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-logo">
                <a href="<?php echo BASE_URL; ?>" class="logo"><h1>ðŸ½ï¸ Delicacy</h1></a>
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
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/" class="nav-link">ðŸ“Š Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="nav-link active">ðŸ“‹ CardÃ¡pio</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php" class="nav-link">ðŸ“¦ Pedidos</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="dashboard">
                <div class="dashboard-header">
                    <div class="header-actions">
                        <div>
                            <h1>ðŸ“‹ CardÃ¡pios</h1>
                            <p>Gerencie os cardÃ¡pios de <strong><?php echo htmlspecialchars($restaurant['name']); ?></strong></p>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio-novo.php" class="action-btn">
                            âž• Novo CardÃ¡pio
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="message-container">
                        <div class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                            <span><?php echo htmlspecialchars($message['text']); ?></span>
                            <button onclick="this.parentElement.parentElement.style.display='none';">Ã—</button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($menus)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">ðŸ“‹</div>
                        <h2>Nenhum cardÃ¡pio ainda</h2>
                        <p>Crie seu primeiro cardÃ¡pio para comeÃ§ar a receber pedidos.</p>
                        <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio-novo.php" class="action-btn">
                            âž• Criar Primeiro CardÃ¡pio
                        </a>
                    </div>
                <?php else: ?>
                    <div class="menus-grid">
                        <?php foreach ($menus as $menu): ?>
                            <div class="menu-card">
                                <div class="menu-card-header">
                                    <h3><?php echo htmlspecialchars($menu['name']); ?></h3>
                                    <span class="badge <?php echo $menu['is_published'] ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo $menu['is_published'] ? 'âœ… Publicado' : 'ðŸ“ Rascunho'; ?>
                                    </span>
                                </div>
                                <div class="menu-card-body">
                                    <p><?php echo htmlspecialchars($menu['description'] ?? 'Sem descriÃ§Ã£o'); ?></p>
                                    <div class="menu-meta">
                                        <span>ðŸ½ï¸ <?php echo (int)($menu['items_count'] ?? 0); ?> pratos</span>
                                        <span>ðŸ“ Unidade <?php echo (int)$menu['unit_number']; ?></span>
                                        <span>ðŸ“… <?php echo formatDate($menu['created_at']); ?></span>
                                    </div>
                                    <?php if ($menu['is_published'] && !empty($menu['slug'])): ?>
                                        <div class="menu-url">
                                            <small>ðŸ”— URL pÃºblica:</small>
                                            <a href="<?php echo BASE_URL; ?>/menu.php?slug=<?php echo htmlspecialchars($menu['slug']); ?>" target="_blank">
                                                <?php echo BASE_URL; ?>/menu.php?slug=<?php echo htmlspecialchars($menu['slug']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="menu-card-actions">
                                    <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio-editar.php?id=<?php echo $menu['id']; ?>" class="btn btn-small btn-primary">
                                        âœï¸ Editar
                                    </a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $menu['is_published'] ? 'Deseja despublicar?' : 'Deseja publicar?'; ?>')">
                                        <input type="hidden" name="action" value="toggle_publish">
                                        <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <button type="submit" class="btn btn-small <?php echo $menu['is_published'] ? 'btn-secondary' : 'btn-success'; ?>">
                                            <?php echo $menu['is_published'] ? 'â¸ï¸ Despublicar' : 'ðŸš€ Publicar'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este cardÃ¡pio?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <button type="submit" class="btn btn-small btn-danger">ðŸ—‘ï¸ Excluir</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Delicacy. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
