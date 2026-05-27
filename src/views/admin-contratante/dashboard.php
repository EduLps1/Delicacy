<?php
/**
 * DELICACY - Painel do Contratante
 * Variaveis: $restaurant, $contractorStats, $dailySeries, $adminPreview
 */
$message = getSessionMessage();
$adminPreview = !empty($adminPreview);
$user = getAuthUser() ?: ['name' => 'Contratante', 'email' => ''];
$displayName = $restaurant['name'] ?? $user['name'] ?? 'Contratante';
$displayEmail = $restaurant['email'] ?? $user['email'] ?? '';
$cleanInitials = preg_replace('/[^A-Za-z0-9]/', '', $displayName);
$initials = strtoupper(substr($cleanInitials, 0, 2) ?: 'CT');
$restaurantId = (int)($restaurant['id'] ?? 0);

$stats = $contractorStats ?? [
    'period_days' => 30,
    'revenue' => 0.0,
    'commission' => 0.0,
    'received' => 0.0,
    'orders' => 0,
    'average_ticket' => 0.0
];
$series = $dailySeries ?? [];
$selectedPeriod = (int)($stats['period_days'] ?? 30);
$periodLabels = [
    7 => 'Últimos 7 dias',
    30 => 'Últimos 30 dias',
    90 => 'Últimos 90 dias',
    365 => 'Últimos 12 meses'
];
if (!isset($periodLabels[$selectedPeriod])) {
    $selectedPeriod = 30;
}

$allowedSections = ['dashboard', 'clientes', 'fidelidade', 'metricas'];
$activeSection = $_GET['section'] ?? 'dashboard';
if (!in_array($activeSection, $allowedSections, true)) {
    $activeSection = 'dashboard';
}

$dashboardUrl = $adminPreview
    ? BASE_URL . '/admin-delicacy/restaurant-dashboard.php?id=' . $restaurantId
    : BASE_URL . '/admin-contratante/';
$periodUrl = function ($period) use ($adminPreview, $restaurantId, $activeSection) {
    $base = $adminPreview
        ? BASE_URL . '/admin-delicacy/restaurant-dashboard.php?id=' . $restaurantId
        : BASE_URL . '/admin-contratante/';
    $separator = strpos($base, '?') === false ? '?' : '&';
    return $base . $separator . http_build_query(['period' => (int)$period, 'section' => $activeSection]);
};
$sectionUrl = function ($section) use ($dashboardUrl) {
    $separator = strpos($dashboardUrl, '?') === false ? '?' : '&';
    return $dashboardUrl . $separator . 'section=' . rawurlencode($section);
};

$chartLabels = [];
$chartRevenue = [];
$chartOrders = [];
foreach ($series as $row) {
    $chartLabels[] = isset($row['date']) ? date('d/m', strtotime($row['date'])) : (string)($row['day'] ?? '');
    $chartRevenue[] = (float)($row['revenue'] ?? 0);
    $chartOrders[] = (int)($row['orders'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delicacy - Painel do Restaurante</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        bgMain: 'var(--bg-main)',
                        bgCard: 'var(--bg-card)',
                        bgSidebar: 'var(--bg-sidebar)',
                        bgHeader: 'var(--bg-header)',
                        borderCard: 'var(--border-card)',
                        textMain: 'var(--text-main)',
                        textSec: 'var(--text-sec)',
                        brandAccent: 'var(--brand-accent)',
                        brandRed: '#EF4444'
                    }
                }
            }
        };
    </script>
    <style>
        :root.dark {
            --bg-main: #000000;
            --bg-card: #0b0b0f;
            --bg-sidebar: #0b0b0f;
            --bg-header: #0b0b0f;
            --border-card: #18181c;
            --text-main: #ffffff;
            --text-sec: #71717a;
            --brand-accent: #ef4444;
        }
        :root.light {
            --bg-main: #ffffff;
            --bg-card: #ffffff;
            --bg-sidebar: #f8f9fa;
            --bg-header: #f1f3f5;
            --border-card: #e4e4e7;
            --text-main: #000000;
            --text-sec: #71717a;
            --brand-accent: #000000;
        }
        .page { display: none; }
        .page.active { display: block; animation: enter .25s ease both; }
        @keyframes enter { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
        :root.dark .nav-item-active { background-color: #000 !important; color: #ef4444 !important; border-color: #ef4444 !important; }
        :root.dark .nav-item-hover:hover { color: #ef4444 !important; }
        :root.light .nav-item-active { background-color: #000 !important; color: #fff !important; border-color: #000 !important; }
        :root.light .nav-item-hover:hover { background-color: rgba(0, 0, 0, .04); color: #000; }
        :root.dark .icon-box { background-color: #18181b !important; }
        :root.light .icon-box { background-color: #f4f4f5 !important; }
        .sidebar-collapsed #sidebar { width: 80px; }
        .sidebar-collapsed #sidebar .sidebar-text { display: none; }
        .sidebar-collapsed #sidebar .nav-link { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar-collapsed #sidebar .nav-link i { margin-right: 0; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg-main); }
        ::-webkit-scrollbar-thumb { background: var(--border-card); border-radius: 10px; }
    </style>
</head>
<body class="bg-bgMain text-textMain font-sans antialiased overflow-hidden flex h-screen transition-colors duration-200">
    <aside id="sidebar" class="w-64 bg-bgSidebar text-textSec flex flex-col hidden md:flex transition-all duration-200 border-r border-borderCard relative z-30">
        <button type="button" onclick="toggleSidebar()" class="absolute top-1/2 -right-3.5 -translate-y-1/2 w-7 h-16 border border-borderCard border-l-0 rounded-r-full bg-bgSidebar text-textMain flex items-center justify-center text-sm font-mono shadow-md z-40 hover:bg-brandRed hover:text-white transition-colors" aria-label="Recolher menu">
            <span id="toggleArrow">&lsaquo;</span>
        </button>
        <div class="h-16 flex items-center px-6 gap-3 border-b border-borderCard shrink-0">
            <div class="w-8 h-8 rounded-xl bg-brandRed flex items-center justify-center shrink-0">
                <i class="fa-solid fa-utensils text-white text-sm"></i>
            </div>
            <div class="sidebar-text min-w-0">
                <span class="text-sm font-bold tracking-tight text-textMain block leading-none truncate"><?php echo htmlspecialchars($displayName); ?></span>
                <span class="text-[10px] text-textSec font-mono uppercase tracking-wider">Restaurante Admin</span>
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="<?php echo htmlspecialchars($dashboardUrl); ?>" class="nav-link <?php echo $activeSection === 'dashboard' ? 'nav-item-active' : 'nav-item-hover'; ?> w-full group flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border <?php echo $activeSection === 'dashboard' ? 'border-borderCard' : 'border-transparent'; ?>">
                <i class="fa-solid fa-chart-pie w-5 mr-3 shrink-0"></i><span class="sidebar-text">Dashboard</span>
            </a>
            <?php if (!$adminPreview): ?>
                <a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php" class="nav-item-hover nav-link w-full group flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border border-transparent">
                    <i class="fa-solid fa-bell-concierge w-5 mr-3 opacity-60 shrink-0"></i><span class="sidebar-text">Pedidos</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="nav-item-hover nav-link w-full group flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border border-transparent">
                    <i class="fa-solid fa-kitchen-set w-5 mr-3 opacity-60 shrink-0"></i><span class="sidebar-text">Cardápios</span>
                </a>
                <a href="<?php echo htmlspecialchars($sectionUrl('clientes')); ?>" class="nav-link <?php echo $activeSection === 'clientes' ? 'nav-item-active' : 'nav-item-hover'; ?> w-full group flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border <?php echo $activeSection === 'clientes' ? 'border-borderCard' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-user-group w-5 mr-3 opacity-60 shrink-0"></i><span class="sidebar-text">Clientes</span>
                </a>
                <a href="<?php echo htmlspecialchars($sectionUrl('fidelidade')); ?>" class="nav-link <?php echo $activeSection === 'fidelidade' ? 'nav-item-active' : 'nav-item-hover'; ?> w-full group flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border <?php echo $activeSection === 'fidelidade' ? 'border-borderCard' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-award w-5 mr-3 opacity-60 shrink-0"></i><span class="sidebar-text">Fidelidade</span>
                </a>
                <a href="<?php echo htmlspecialchars($sectionUrl('metricas')); ?>" class="nav-link <?php echo $activeSection === 'metricas' ? 'nav-item-active' : 'nav-item-hover'; ?> w-full group flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border <?php echo $activeSection === 'metricas' ? 'border-borderCard' : 'border-transparent'; ?>">
                    <i class="fa-solid fa-chart-line w-5 mr-3 opacity-60 shrink-0"></i><span class="sidebar-text">Métricas</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-contratante/editar-restaurante.php" class="nav-item-hover nav-link w-full group flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border border-transparent">
                    <i class="fa-solid fa-sliders w-5 mr-3 opacity-60 shrink-0"></i><span class="sidebar-text">Configurações</span>
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php" class="nav-item-hover nav-link w-full group flex items-center px-4 py-2.5 rounded-xl font-medium transition-all border border-transparent">
                    <i class="fa-solid fa-arrow-left w-5 mr-3 shrink-0"></i><span class="sidebar-text">Restaurantes</span>
                </a>
            <?php endif; ?>
        </nav>
        <div class="p-4 border-t border-borderCard space-y-3 shrink-0">
            <button id="themeToggleBtn" type="button" class="flex items-center justify-between px-2 w-full text-left text-xs hover:text-textMain transition-colors">
                <span id="themeToggleText" class="sidebar-text flex items-center"><i class="fa-solid fa-moon mr-2"></i> Dark Mode</span>
                <i id="themeToggleIcon" class="fa-solid fa-toggle-on text-brandRed text-lg cursor-pointer"></i>
            </button>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-bgHeader flex items-center justify-between px-5 lg:px-10 shrink-0 border-b border-borderCard transition-colors duration-200">
            <div class="hidden sm:flex items-center bg-bgCard rounded-xl px-4 py-2 w-80 lg:w-96 border border-borderCard focus-within:border-brandRed/50 transition-all">
                <i class="fa-solid fa-magnifying-glass text-textSec text-xs"></i>
                <input type="text" placeholder="Pesquisar pedidos, pratos, clientes..." class="bg-transparent border-none outline-none ml-3 w-full text-xs text-textMain">
            </div>
            <div class="flex items-center ml-auto gap-4 lg:gap-6">
                <div id="storeStatus" class="hidden sm:inline-flex items-center gap-2 h-8 px-3 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 text-xs font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Loja Aberta
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-brandRed flex items-center justify-center text-white font-bold text-xs"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="hidden lg:block">
                        <p class="text-xs font-medium leading-none"><?php echo htmlspecialchars($displayName); ?></p>
                        <p class="text-[10px] text-textSec mt-1 uppercase tracking-widest"><?php echo $adminPreview ? 'Visualização Delicacy' : 'Admin Contratante'; ?></p>
                    </div>
                    <a href="<?php echo $adminPreview ? BASE_URL . '/admin-delicacy/restaurants.php' : BASE_URL . '/logout.php'; ?>" class="text-xs text-textSec hover:text-brandRed transition-colors">
                        <?php echo $adminPreview ? 'Voltar' : 'Sair'; ?>
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 lg:p-10 space-y-8">
            <?php if ($adminPreview): ?>
                <div class="rounded-xl border border-borderCard bg-bgCard px-4 py-3 text-xs text-textSec">
                    Visualização administrativa somente leitura dos dados de <?php echo htmlspecialchars($displayName); ?>.
                </div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="rounded-xl border border-borderCard bg-bgCard px-4 py-3 text-xs text-textMain">
                    <?php echo htmlspecialchars($message['text']); ?>
                </div>
            <?php endif; ?>

            <section id="dashboard" class="page <?php echo $activeSection === 'dashboard' ? 'active' : ''; ?> space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-textMain">Bom dia, <?php echo htmlspecialchars($displayName); ?></h1>
                        <p class="text-xs text-textSec mt-1">Acompanhe sua operação no período selecionado: pedidos, faturamento e recebimentos.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select onchange="window.location.href=this.value" class="bg-bgCard border border-borderCard rounded-xl px-3 py-2.5 text-xs outline-none">
                            <?php foreach ($periodLabels as $period => $label): ?>
                                <option value="<?php echo htmlspecialchars($periodUrl($period)); ?>" <?php echo $selectedPeriod === $period ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$adminPreview): ?>
                            <button id="toggleStoreBtn" type="button" onclick="toggleStore()" class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-brandRed text-white transition-all">Fechar loja</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <article class="bg-bgCard rounded-2xl p-6 border border-borderCard shadow-sm group">
                        <div class="flex justify-between items-start mb-2"><span class="text-xs font-medium text-textSec">Pedidos</span><div class="icon-box w-7 h-7 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-receipt"></i></div></div>
                        <h3 class="text-2xl font-bold"><?php echo number_format((int)$stats['orders'], 0, ',', '.'); ?></h3>
                        <p class="text-[11px] text-textSec mt-2"><?php echo htmlspecialchars($periodLabels[$selectedPeriod]); ?></p>
                    </article>
                    <article class="bg-bgCard rounded-2xl p-6 border border-borderCard shadow-sm group">
                        <div class="flex justify-between items-start mb-2"><span class="text-xs font-medium text-textSec">Faturamento</span><div class="icon-box w-7 h-7 rounded-lg flex items-center justify-center text-xs text-emerald-500"><i class="fa-solid fa-money-bill-wave"></i></div></div>
                        <h3 class="text-2xl font-bold"><?php echo formatCurrency($stats['revenue']); ?></h3>
                        <p class="text-[11px] text-textSec mt-2">Receita bruta no período</p>
                    </article>
                    <article class="bg-bgCard rounded-2xl p-6 border border-borderCard shadow-sm group">
                        <div class="flex justify-between items-start mb-2"><span class="text-xs font-medium text-textSec">Ticket médio</span><div class="icon-box w-7 h-7 rounded-lg flex items-center justify-center text-xs text-blue-500"><i class="fa-solid fa-calculator"></i></div></div>
                        <h3 class="text-2xl font-bold"><?php echo formatCurrency($stats['average_ticket']); ?></h3>
                        <p class="text-[11px] text-textSec mt-2">Valor médio por pedido</p>
                    </article>
                    <article class="bg-bgCard rounded-2xl p-6 border border-borderCard shadow-sm group">
                        <div class="flex justify-between items-start mb-2"><span class="text-xs font-medium text-textSec">Valor recebido</span><div class="icon-box w-7 h-7 rounded-lg flex items-center justify-center text-xs text-brandRed"><i class="fa-solid fa-wallet"></i></div></div>
                        <h3 class="text-2xl font-bold"><?php echo formatCurrency($stats['received']); ?></h3>
                        <p class="text-[11px] text-textSec mt-2">Após comissão de <?php echo htmlspecialchars($restaurant['active_commission_rate'] ?? 0); ?>%</p>
                    </article>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <article class="bg-bgCard rounded-2xl p-6 border border-borderCard lg:col-span-2 flex flex-col">
                        <h2 class="text-sm font-bold mb-4">Movimento do período</h2>
                        <div class="relative flex-1 min-h-[280px]"><canvas id="dayMovementChart"></canvas></div>
                    </article>
                    <article class="bg-bgCard rounded-2xl p-6 border border-borderCard flex flex-col">
                        <h2 class="text-sm font-bold mb-5">Resumo financeiro</h2>
                        <div class="space-y-5 text-xs flex-1">
                            <div class="flex justify-between pb-3 border-b border-borderCard"><span class="text-textSec">Receita bruta</span><span class="font-bold"><?php echo formatCurrency($stats['revenue']); ?></span></div>
                            <div class="flex justify-between pb-3 border-b border-borderCard"><span class="text-textSec">Comissão Delicacy</span><span class="font-bold text-brandRed"><?php echo formatCurrency($stats['commission']); ?></span></div>
                            <div class="flex justify-between pb-3 border-b border-borderCard"><span class="text-textSec">Repasse estimado</span><span class="font-bold text-emerald-500"><?php echo formatCurrency($stats['received']); ?></span></div>
                        </div>
                        <?php if (!$adminPreview): ?>
                            <a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php" class="mt-6 rounded-xl border border-borderCard px-4 py-3 text-center text-xs font-semibold hover:border-brandRed hover:text-brandRed transition-colors">Ver pedidos</a>
                        <?php endif; ?>
                    </article>
                </div>
            </section>

            <section id="clientes" class="page <?php echo $activeSection === 'clientes' ? 'active' : ''; ?> space-y-8">
                <div><h1 class="text-2xl font-bold tracking-tight">Base de Clientes</h1><p class="text-xs text-textSec mt-1">Histórico de compras, recorrência e engajamento em fidelidade.</p></div>
                <div class="bg-bgCard rounded-2xl border border-borderCard p-10 text-center">
                    <i class="fa-solid fa-user-group text-2xl text-brandRed mb-4"></i>
                    <h2 class="text-sm font-bold">Módulo de clientes em preparação</h2>
                    <p class="text-xs text-textSec mt-2">A estrutura já está reservada no painel para receber a base identificada e relatórios de recorrência.</p>
                </div>
            </section>

            <section id="fidelidade" class="page <?php echo $activeSection === 'fidelidade' ? 'active' : ''; ?> space-y-8">
                <div><h1 class="text-2xl font-bold tracking-tight">Programa de Fidelidade</h1><p class="text-xs text-textSec mt-1">Configure regras de benefícios e retenção de clientes.</p></div>
                <div class="bg-bgCard rounded-2xl border border-borderCard p-10 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-2xl bg-brandRed/10 text-brandRed flex items-center justify-center mb-4 text-xl"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                    <h2 class="text-lg font-bold">Personalização de Programa e Recompensas</h2>
                    <p class="text-xs text-textSec mt-2 max-w-sm">Módulo avançado de fidelidade sob demanda. Esta funcionalidade será integrada nas próximas sprints.</p>
                    <span class="mt-4 px-3 py-1 text-[10px] font-bold tracking-widest uppercase rounded-full bg-brandRed/10 text-brandRed border border-brandRed/20">Coming Soon</span>
                </div>
            </section>

            <section id="metricas" class="page <?php echo $activeSection === 'metricas' ? 'active' : ''; ?> space-y-8">
                <div><h1 class="text-2xl font-bold tracking-tight">Métricas Analíticas</h1><p class="text-xs text-textSec mt-1">Visão comparativa de faturamento e volume de pedidos.</p></div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="bg-bgCard rounded-2xl p-6 border border-borderCard"><span class="text-xs text-textSec block mb-1">Receita bruta</span><h3 class="text-xl font-bold"><?php echo formatCurrency($stats['revenue']); ?></h3></div>
                    <div class="bg-bgCard rounded-2xl p-6 border border-borderCard"><span class="text-xs text-textSec block mb-1">Pedidos consolidados</span><h3 class="text-xl font-bold"><?php echo number_format((int)$stats['orders'], 0, ',', '.'); ?></h3></div>
                    <div class="bg-bgCard rounded-2xl p-6 border border-borderCard"><span class="text-xs text-textSec block mb-1">Ticket médio</span><h3 class="text-xl font-bold"><?php echo formatCurrency($stats['average_ticket']); ?></h3></div>
                </div>
                <article class="bg-bgCard rounded-2xl p-6 border border-borderCard">
                    <h2 class="text-sm font-bold mb-4">Evolução de faturamento</h2>
                    <div class="relative min-h-[300px]"><canvas id="monthlyRevenueChart"></canvas></div>
                </article>
            </section>
        </main>
    </div>

    <script>
        const htmlElement = document.documentElement;
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleText = document.getElementById('themeToggleText');
        const themeToggleIcon = document.getElementById('themeToggleIcon');
        let storeOpen = true;
        let dayMovementChart = null;
        let monthlyRevenueChart = null;
        const chartLabels = <?php echo json_encode($chartLabels, JSON_UNESCAPED_UNICODE); ?>;
        const chartRevenue = <?php echo json_encode($chartRevenue); ?>;
        const chartOrders = <?php echo json_encode($chartOrders); ?>;

        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            document.getElementById('toggleArrow').innerHTML =
                document.body.classList.contains('sidebar-collapsed') ? '&rsaquo;' : '&lsaquo;';
        }

        function toggleStore() {
            const status = document.getElementById('storeStatus');
            const button = document.getElementById('toggleStoreBtn');
            if (!status || !button) return;
            storeOpen = !storeOpen;
            if (storeOpen) {
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Loja Aberta';
                status.className = 'hidden sm:inline-flex items-center gap-2 h-8 px-3 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 text-xs font-bold';
                button.textContent = 'Fechar loja';
                button.className = 'px-4 py-2.5 rounded-xl text-xs font-semibold bg-brandRed text-white transition-all';
            } else {
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-brandRed"></span>Loja Fechada';
                status.className = 'hidden sm:inline-flex items-center gap-2 h-8 px-3 rounded-full bg-brandRed/10 text-brandRed border border-brandRed/20 text-xs font-bold';
                button.textContent = 'Abrir loja';
                button.className = 'px-4 py-2.5 rounded-xl text-xs font-semibold bg-emerald-500 text-white transition-all';
            }
        }

        function chartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#71717A', maxTicksLimit: 12 } },
                    y: { beginAtZero: true, grid: { color: 'rgba(113, 113, 122, 0.12)' }, ticks: { color: '#71717A' } }
                }
            };
        }
        const dayCanvas = document.getElementById('dayMovementChart');
        if (dayCanvas) {
            dayMovementChart = new Chart(dayCanvas, {
                type: 'line',
                data: { labels: chartLabels, datasets: [{ data: chartOrders, borderColor: '#EF4444', backgroundColor: 'rgba(239, 68, 68, 0.06)', fill: true, tension: .3, borderWidth: 2 }] },
                options: chartOptions()
            });
        }
        const revenueCanvas = document.getElementById('monthlyRevenueChart');
        if (revenueCanvas) {
            monthlyRevenueChart = new Chart(revenueCanvas, {
                type: 'line',
                data: { labels: chartLabels, datasets: [{ data: chartRevenue, borderColor: '#EF4444', backgroundColor: 'rgba(239, 68, 68, 0.06)', fill: true, tension: .3, borderWidth: 2 }] },
                options: chartOptions()
            });
        }
        themeToggleBtn.addEventListener('click', () => {
            const useLight = htmlElement.classList.contains('dark');
            htmlElement.classList.toggle('dark', !useLight);
            htmlElement.classList.toggle('light', useLight);
            themeToggleText.innerHTML = useLight
                ? '<i class="fa-solid fa-sun mr-2"></i> Light Mode'
                : '<i class="fa-solid fa-moon mr-2"></i> Dark Mode';
            themeToggleIcon.classList.toggle('fa-toggle-on', !useLight);
            themeToggleIcon.classList.toggle('fa-toggle-off', useLight);
            if (dayMovementChart) dayMovementChart.update();
            if (monthlyRevenueChart) monthlyRevenueChart.update();
        });
    </script>
</body>
</html>
