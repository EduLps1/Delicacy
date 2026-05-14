<?php
/**
 * DELICACY - Layout Base
 * 
 * Template HTML base para todas as pÃ¡ginas.
 * Inclui header, navigation, e footer.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/<?php echo htmlspecialchars($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="layout-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="header-logo">
                    <a href="<?php echo BASE_URL; ?>" class="logo">
                        <h1>ðŸ½ï¸ Delicacy</h1>
                    </a>
                </div>

                <nav class="header-nav">
                    <?php if (isAuthenticated()): ?>
                        <?php $user = getAuthUser(); ?>
                        <span class="user-info">
                            OlÃ¡, <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                        </span>
                        <a href="<?php echo BASE_URL; ?>/logout.php" class="btn-logout">
                            Sair
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php" class="btn-login">
                            Entrar
                        </a>
                        <a href="<?php echo BASE_URL; ?>/register.php" class="btn-register">
                            Cadastrar
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <!-- Navigation Sidebar (se autenticado) -->
        <?php if (isAuthenticated()): ?>
            <aside class="sidebar">
                <nav class="sidebar-nav">
                    <?php if (hasRole(ROLE_ADMIN_DELICACY)): ?>
                        <h3 class="nav-title">Admin Delicacy</h3>
                        <ul>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php" 
                                   class="nav-link <?php if (strpos($_SERVER['REQUEST_URI'], 'admin-delicacy/dashboard') !== false) echo 'active'; ?>">
                                    ðŸ“Š Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php"
                                   class="nav-link <?php if (strpos($_SERVER['REQUEST_URI'], 'admin-delicacy/restaurants') !== false) echo 'active'; ?>">
                                    ðŸª Restaurantes
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/admin-delicacy/users.php"
                                   class="nav-link <?php if (strpos($_SERVER['REQUEST_URI'], 'admin-delicacy/users') !== false) echo 'active'; ?>">
                                    ðŸ‘¥ UsuÃ¡rios
                                </a>
                            </li>
                        </ul>
                    <?php elseif (hasRole(ROLE_ADMIN_RESTAURANT)): ?>
                        <h3 class="nav-title">Restaurante</h3>
                        <ul>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/admin-contratante/"
                                   class="nav-link <?php if (strpos($_SERVER['REQUEST_URI'], 'dashboard/dashboard') !== false) echo 'active'; ?>">
                                    ðŸ“Š Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php"
                                   class="nav-link <?php if (strpos($_SERVER['REQUEST_URI'], 'cardapio') !== false) echo 'active'; ?>">
                                    ðŸ“‹ CardÃ¡pio
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php"
                                   class="nav-link <?php if (strpos($_SERVER['REQUEST_URI'], 'pedidos') !== false) echo 'active'; ?>">
                                    ðŸ“¦ Pedidos
                                </a>
                            </li>
                        </ul>
                    <?php endif; ?>
                </nav>
            </aside>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Messages -->
            <?php 
            $message = getSessionMessage();
            if ($message): 
            ?>
                <div class="message-container">
                    <div class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                        <p><?php echo htmlspecialchars($message['text']); ?></p>
                        <button class="message-close" onclick="this.parentElement.style.display='none';">Ã—</button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="page-content">
                <?php require_once $contentPath; ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Delicacy - GestÃ£o de CardÃ¡pios Digitais. Todos os direitos reservados.</p>
    </footer>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>/js/main.js"></script>
    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <script src="<?php echo BASE_URL; ?>/js/<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>