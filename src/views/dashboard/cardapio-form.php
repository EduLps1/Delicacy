<?php
/**
 * DELICACY - FormulÃ¡rio Criar CardÃ¡pio
 * VariÃ¡veis: $restaurant, $menu (null=novo), $csrf_token
 */
$message = getSessionMessage();
$isEdit = !empty($menu);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Editar' : 'Novo'; ?> CardÃ¡pio - Delicacy</title>
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
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/" class="nav-link"> Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="nav-link active"> CardÃ¡pio</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php" class="nav-link"> Pedidos</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1><?php echo $isEdit ? 'Editar' : 'Novo'; ?> CardÃ¡pio</h1>
                    <p><a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php">â† Voltar para lista</a></p>
                </div>

                <?php if ($message): ?>
                    <div class="message-container">
                        <div class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                            <span><?php echo htmlspecialchars($message['text']); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin-contratante/cardapio-novo.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <div class="form-group">
                            <label for="name">Nome do CardÃ¡pio *</label>
                            <input type="text" id="name" name="name" required
                                   placeholder="Ex: CardÃ¡pio Delivery, Menu AlmoÃ§o..."
                                   value="<?php echo htmlspecialchars($menu['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">DescriÃ§Ã£o</label>
                            <textarea id="description" name="description" 
                                      placeholder="Descreva seu cardÃ¡pio..."><?php echo htmlspecialchars($menu['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="type">Tipo de Atendimento</label>
                                <select id="type" name="type">
                                    <option value="both" <?php echo ($menu['type'] ?? '') === 'both' ? 'selected' : ''; ?>>Online + Presencial</option>
                                    <option value="online" <?php echo ($menu['type'] ?? '') === 'online' ? 'selected' : ''; ?>>Apenas Online (Delivery)</option>
                                    <option value="presencial" <?php echo ($menu['type'] ?? '') === 'presencial' ? 'selected' : ''; ?>>Apenas Presencial</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="unit_number">NÃºmero da Unidade</label>
                                <input type="number" id="unit_number" name="unit_number" min="1"
                                       value="<?php echo (int)($menu['unit_number'] ?? 1); ?>">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $isEdit ? 'Salvar AlteraÃ§Ãµes' : 'Criar CardÃ¡pio'; ?>
                            </button>
                            <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Delicacy. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
