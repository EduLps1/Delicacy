<?php
$panelTitle = $panelTitle ?? 'Painel do Restaurante';
$panelActive = $panelActive ?? '';
$user = getAuthUser() ?: ['name' => 'Contratante', 'email' => ''];
$panelName = $restaurant['name'] ?? $user['name'] ?? 'Contratante';
$userName = $user['name'] ?? $panelName;
$userEmail = $user['email'] ?? '';
$cleanInitials = preg_replace('/[^A-Za-z0-9]/', '', $userName);
$panelInitials = strtoupper(substr($cleanInitials, 0, 2) ?: 'CT');
$navClass = function ($key) use ($panelActive) {
    return $panelActive === $key ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($panelTitle); ?> - Delicacy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin-delicacy.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin-contratante.css">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: {
            bgMain: 'var(--ad-bg)', bgCard: 'var(--ad-surface)', bgSidebar: 'var(--ad-sidebar)',
            bgHeader: 'var(--ad-topbar)', borderCard: 'var(--ad-line)', textMain: 'var(--ad-text)',
            textSec: 'var(--ad-muted)', brandRed: '#F33C43'
        }}}};
    </script>
</head>
<body class="platform-admin contractor-admin" data-theme="light" data-default-theme="light" data-theme-key="delicacy-contractor-theme">
    <aside class="ad-sidebar">
        <button type="button" class="contractor-collapse-toggle" aria-label="Recolher menu" onclick="document.body.classList.toggle('sidebar-collapsed'); this.querySelector('span').innerHTML = document.body.classList.contains('sidebar-collapsed') ? '&rsaquo;' : '&lsaquo;';">
            <span>&lsaquo;</span>
        </button>
        <a class="ad-brand" href="<?php echo BASE_URL; ?>/admin-contratante/">
            <span class="ad-logo-box"><i class="fa-solid fa-utensils" style="color:#fff;display:grid;place-items:center;height:100%;"></i></span>
            <span>
                <span class="ad-brand-name"><?php echo htmlspecialchars($panelName); ?></span>
                <span class="ad-brand-role">RESTAURANTE ADMIN</span>
            </span>
        </a>

        <nav class="ad-nav" aria-label="Admin Contratante">
            <a class="<?php echo $navClass('dashboard'); ?>" href="<?php echo BASE_URL; ?>/admin-contratante/"><span class="ad-menu-icon"><i class="fa-solid fa-chart-pie"></i></span><span>Dashboard</span></a>
            <a class="<?php echo $navClass('pedidos'); ?>" href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php"><span class="ad-menu-icon"><i class="fa-solid fa-bell-concierge"></i></span><span>Pedidos</span></a>
            <a class="<?php echo $navClass('cardapios'); ?>" href="<?php echo BASE_URL; ?>/admin-contratante/cardapios.php"><span class="ad-menu-icon"><i class="fa-solid fa-kitchen-set"></i></span><span>Cardápios</span></a>
            <a href="<?php echo BASE_URL; ?>/admin-contratante/?section=clientes"><span class="ad-menu-icon"><i class="fa-solid fa-user-group"></i></span><span>Clientes</span></a>
            <a href="<?php echo BASE_URL; ?>/admin-contratante/?section=fidelidade"><span class="ad-menu-icon"><i class="fa-solid fa-award"></i></span><span>Fidelidade</span></a>
            <a href="<?php echo BASE_URL; ?>/admin-contratante/?section=metricas"><span class="ad-menu-icon"><i class="fa-solid fa-chart-line"></i></span><span>Métricas</span></a>
            <a class="<?php echo $navClass('config'); ?>" href="<?php echo BASE_URL; ?>/admin-contratante/editar-restaurante.php"><span class="ad-menu-icon"><i class="fa-solid fa-sliders"></i></span><span>Configurações</span></a>
        </nav>

        <div class="ad-theme">
            <span class="ad-theme-label"><i data-admin-theme-icon class="fa-solid fa-sun mr-2" aria-hidden="true"></i><span data-admin-theme-label>Light Mode</span></span>
            <button class="ad-theme-toggle" type="button" data-admin-theme-toggle aria-label="Ativar modo escuro" aria-pressed="false"></button>
        </div>
    </aside>

    <header class="ad-topbar">
        <label class="ad-search">
            <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg></span>
            <input type="search" placeholder="Pesquisar pedidos, pratos, clientes..." aria-label="Pesquisar">
        </label>
        <div class="ad-account">
            <span class="ad-bell ad-icon"><svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg></span>
            <span class="ad-divider"></span>
            <span class="ad-store-status"><i></i> Loja Aberta</span>
            <details class="ad-user-menu">
                <summary class="ad-profile ad-profile-trigger">
                    <span class="ad-avatar"><?php echo htmlspecialchars($panelInitials); ?></span>
                    <span><strong><?php echo htmlspecialchars($userName); ?></strong><small>ADMIN CONTRATANTE</small></span>
                </summary>
                <div class="ad-user-dropdown">
                    <div class="ad-dropdown-head"><strong><?php echo htmlspecialchars($userName); ?></strong><span><?php echo htmlspecialchars($userEmail); ?></span></div>
                    <a class="ad-logout" href="<?php echo BASE_URL; ?>/logout.php"><span aria-hidden="true">&#10132;</span> Sair</a>
                </div>
            </details>
        </div>
    </header>

    <main class="ad-main space-y-8">
