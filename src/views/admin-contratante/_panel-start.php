<?php
$panelTitle = $panelTitle ?? 'Painel do Restaurante';
$panelActive = $panelActive ?? '';
$user = getAuthUser() ?: ['name' => 'Contratante'];
$panelName = $restaurant['name'] ?? $user['name'] ?? 'Contratante';
$cleanInitials = preg_replace('/[^A-Za-z0-9]/', '', $panelName);
$panelInitials = strtoupper(substr($cleanInitials, 0, 2) ?: 'CT');
$navClass = function ($key) use ($panelActive) {
    return $panelActive === $key
        ? 'nav-item-active border-brandRed text-brandRed'
        : 'nav-item-hover border-transparent text-textSec';
};
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($panelTitle); ?> - Delicacy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: {
            bgMain: 'var(--bg-main)', bgCard: 'var(--bg-card)', bgSidebar: 'var(--bg-sidebar)',
            bgHeader: 'var(--bg-header)', borderCard: 'var(--border-card)', textMain: 'var(--text-main)',
            textSec: 'var(--text-sec)', brandRed: '#EF4444'
        }}}};
    </script>
    <style>
        :root.dark { --bg-main:#000; --bg-card:#0b0b0f; --bg-sidebar:#0b0b0f; --bg-header:#0b0b0f; --border-card:#18181c; --text-main:#fff; --text-sec:#71717a; }
        :root.light { --bg-main:#fff; --bg-card:#fff; --bg-sidebar:#f8f9fa; --bg-header:#f1f3f5; --border-card:#e4e4e7; --text-main:#000; --text-sec:#71717a; }
        :root.dark .nav-item-active { background:#000; color:#ef4444; border-color:#ef4444; }
        :root.light .nav-item-active { background:#000; color:#fff; border-color:#000; }
        :root.dark .nav-item-hover:hover { color:#ef4444; }
        :root.light .nav-item-hover:hover { background:rgba(0,0,0,.04); color:#000; }
        .sidebar-collapsed #sidebar { width:80px; }
        .sidebar-collapsed #sidebar .sidebar-text { display:none; }
        .sidebar-collapsed #sidebar .nav-link { justify-content:center; padding-left:0; padding-right:0; }
        .sidebar-collapsed #sidebar .nav-link i { margin-right:0; }
        ::-webkit-scrollbar { width:5px; height:5px; }
        ::-webkit-scrollbar-track { background:var(--bg-main); }
        ::-webkit-scrollbar-thumb { background:var(--border-card); border-radius:10px; }
    </style>
</head>
<body class="bg-bgMain text-textMain font-sans antialiased overflow-hidden flex h-screen transition-colors duration-200">
    <aside id="sidebar" class="w-64 bg-bgSidebar text-textSec flex flex-col hidden md:flex transition-all duration-200 border-r border-borderCard relative z-30">
        <button type="button" onclick="toggleSidebar()" class="absolute top-1/2 -right-3.5 -translate-y-1/2 w-7 h-16 border border-borderCard border-l-0 rounded-r-full bg-bgSidebar text-textMain flex items-center justify-center text-sm font-mono shadow-md z-40 hover:bg-brandRed hover:text-white transition-colors" aria-label="Recolher menu">
            <span id="toggleArrow">&lsaquo;</span>
        </button>
        <div class="h-16 flex items-center px-6 gap-3 border-b border-borderCard shrink-0">
            <div class="w-8 h-8 rounded-xl bg-brandRed flex items-center justify-center shrink-0"><i class="fa-solid fa-utensils text-white text-sm"></i></div>
            <div class="sidebar-text min-w-0">
                <span class="text-sm font-bold tracking-tight text-textMain block leading-none truncate"><?php echo htmlspecialchars($panelName); ?></span>
                <span class="text-[10px] text-textSec font-mono uppercase tracking-wider">Restaurante Admin</span>
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="<?php echo BASE_URL; ?>/admin-contratante/" class="nav-link <?php echo $navClass('dashboard'); ?> w-full flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border">
                <i class="fa-solid fa-chart-pie w-5 mr-3 shrink-0"></i><span class="sidebar-text">Dashboard</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php" class="nav-link <?php echo $navClass('pedidos'); ?> w-full flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border">
                <i class="fa-solid fa-bell-concierge w-5 mr-3 shrink-0"></i><span class="sidebar-text">Pedidos</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="nav-link <?php echo $navClass('cardapios'); ?> w-full flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border">
                <i class="fa-solid fa-kitchen-set w-5 mr-3 shrink-0"></i><span class="sidebar-text">Cardápios</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-contratante/?section=clientes" class="nav-link nav-item-hover border-transparent text-textSec w-full flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border">
                <i class="fa-solid fa-user-group w-5 mr-3 shrink-0"></i><span class="sidebar-text">Clientes</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-contratante/?section=fidelidade" class="nav-link nav-item-hover border-transparent text-textSec w-full flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border">
                <i class="fa-solid fa-award w-5 mr-3 shrink-0"></i><span class="sidebar-text">Fidelidade</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-contratante/?section=metricas" class="nav-link nav-item-hover border-transparent text-textSec w-full flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border">
                <i class="fa-solid fa-chart-line w-5 mr-3 shrink-0"></i><span class="sidebar-text">Métricas</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-contratante/editar-restaurante.php" class="nav-link <?php echo $navClass('config'); ?> w-full flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border">
                <i class="fa-solid fa-sliders w-5 mr-3 shrink-0"></i><span class="sidebar-text">Configurações</span>
            </a>
        </nav>
        <div class="p-4 border-t border-borderCard shrink-0">
            <button id="themeToggleBtn" type="button" class="flex items-center justify-between px-2 w-full text-left text-xs hover:text-textMain transition-colors">
                <span id="themeToggleText" class="sidebar-text flex items-center"><i class="fa-solid fa-moon mr-2"></i> Dark Mode</span>
                <i id="themeToggleIcon" class="fa-solid fa-toggle-on text-brandRed text-lg"></i>
            </button>
        </div>
    </aside>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-bgHeader flex items-center justify-between px-5 lg:px-10 shrink-0 border-b border-borderCard">
            <div class="hidden sm:flex items-center bg-bgCard rounded-xl px-4 py-2 w-80 lg:w-96 border border-borderCard">
                <i class="fa-solid fa-magnifying-glass text-textSec text-xs"></i>
                <input type="text" placeholder="Pesquisar pedidos, pratos, clientes..." class="bg-transparent border-none outline-none ml-3 w-full text-xs text-textMain">
            </div>
            <div class="flex items-center ml-auto gap-4">
                <div class="hidden sm:inline-flex items-center gap-2 h-8 px-3 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 text-xs font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Loja Aberta
                </div>
                <div class="w-8 h-8 rounded-full bg-brandRed flex items-center justify-center text-white font-bold text-xs"><?php echo htmlspecialchars($panelInitials); ?></div>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="text-xs text-textSec hover:text-brandRed">Sair</a>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6 lg:p-10 space-y-8">
