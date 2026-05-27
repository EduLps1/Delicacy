<?php
/**
 * DELICACY - Overview administrativa
 * Variaveis: $stats, $csrf_token
 */

$user = getAuthUser() ?: ['name' => 'Admin Delicacy'];
$userName = $user['name'] ?? 'Admin Delicacy';
$userEmail = $user['email'] ?? 'admin@delicacy.com.br';
$initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $userName), 0, 2) ?: 'AD');
$revenue = (float)($stats['estimated_revenue'] ?? 0);
$activeRestaurants = (int)($stats['total_restaurants'] ?? 0);
$totalMenus = (int)($stats['total_menus'] ?? 0);
$newRestaurants = (int)($stats['new_restaurants'] ?? 0);
$activeOrders = (int)($stats['active_orders'] ?? 0);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delicacy | Overview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin-delicacy.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
</head>
<body class="platform-admin ad-shell-page ad-overview-page" data-theme="dark" data-default-theme="dark">
    <aside class="ad-sidebar">
        <a class="ad-brand" href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php">
            <span class="ad-logo-box"><img src="<?php echo BASE_URL; ?>/images/auth/delicacy-symbol-cropped.png" alt=""></span>
            <span class="ad-brand-name">Delicacy</span>
        </a>
        <nav class="ad-nav" aria-label="Administracao Delicacy">
            <a class="active" aria-current="page" href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M12 3v9h9"/><path d="M21 12a9 9 0 1 1-9-9"/></svg></span><span>Overview</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M4 10l2-6h12l2 6"/><path d="M9 20v-6h6v6"/></svg></span><span>Restaurantes</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/financial.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-8"/><path d="M22 20H2"/></svg></span><span>Financeiro</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/users.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2"/><path d="M16 4a4 4 0 0 1 0 8"/><path d="M21 21v-2a6 6 0 0 0-4-5.65"/></svg></span><span>Usu&aacute;rios</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/settings.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3A1.7 1.7 0 0 0 14 21v.2a2 2 0 0 1-4 0V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1A2 2 0 0 1 4.2 17l.1-.1A1.7 1.7 0 0 0 3 14h-.1a2 2 0 0 1 0-4H3a1.7 1.7 0 0 0 1.3-2.9L4.2 7A2 2 0 0 1 7 4.2l.1.1A1.7 1.7 0 0 0 10 3V3a2 2 0 0 1 4 0v.2a1.7 1.7 0 0 0 2.9 1.1l.1-.1A2 2 0 0 1 19.8 7l-.1.1A1.7 1.7 0 0 0 21 10h.1a2 2 0 0 1 0 4H21a1.7 1.7 0 0 0-1.6 1z"/></svg></span><span>Configura&ccedil;&otilde;es</span>
            </a>
        </nav>
        <div class="ad-theme">
            <span class="ad-theme-label"><span data-admin-theme-glyph aria-hidden="true">&#9790;</span><span data-admin-theme-label>Dark Mode</span></span>
            <button class="ad-theme-toggle" type="button" data-admin-theme-toggle aria-label="Ativar modo claro" aria-pressed="true"></button>
        </div>
    </aside>

    <header class="ad-topbar">
        <label class="ad-search">
            <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg></span>
            <input type="search" placeholder="Busca global..." aria-label="Busca global">
            <span class="ad-key">Ctrl</span><span class="ad-key">/</span>
        </label>
        <div class="ad-account">
            <span class="ad-bell ad-icon"><svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg></span>
            <span class="ad-divider"></span>
            <details class="ad-user-menu">
                <summary class="ad-profile ad-profile-trigger">
                    <span class="ad-avatar"><?php echo htmlspecialchars($initials); ?></span>
                    <span><strong><?php echo htmlspecialchars($userName); ?></strong><small>ADMIN DELICACY</small></span>
                </summary>
                <div class="ad-user-dropdown">
                    <div class="ad-dropdown-head"><strong><?php echo htmlspecialchars($userName); ?></strong><span><?php echo htmlspecialchars($userEmail); ?></span></div>
                    <a class="ad-logout" href="<?php echo BASE_URL; ?>/logout.php"><span aria-hidden="true">&#10132;</span> Sair</a>
                </div>
            </details>
        </div>
    </header>

    <main class="ad-main">
        <header class="ad-head">
            <div class="ad-title">
                <h1>Delicacy Overview</h1>
                <div class="ad-tabs"><span class="active">Real Time</span><span>History</span></div>
            </div>
            <button class="ad-action" type="button"><span class="ad-action-icon" aria-hidden="true">&#8679;</span> Export</button>
        </header>

        <section class="ad-metrics" aria-label="Indicadores da plataforma">
            <article class="ad-card">
                <p class="ad-card-label">Receita Delicacy</p>
                <span class="ad-icon-box">R$</span>
                <p class="ad-card-value"><?php echo formatCurrency($revenue); ?></p>
                <p class="ad-card-note"><span class="ad-positive">&#8599; Dados atuais</span> do banco</p>
            </article>
            <article class="ad-card">
                <p class="ad-card-label">Restaurantes Ativos</p>
                <span class="ad-icon-box"><svg viewBox="0 0 24 24"><path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M4 10l2-6h12l2 6"/></svg></span>
                <p class="ad-card-value"><?php echo number_format($activeRestaurants, 0, ',', '.'); ?></p>
                <p class="ad-card-note">Cadastrados e ativos</p>
            </article>
            <article class="ad-card">
                <p class="ad-card-label">Card&aacute;pios Cadastrados</p>
                <span class="ad-icon-box"><svg viewBox="0 0 24 24"><path d="M5 4h10a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3z"/><path d="M8 20V7a3 3 0 0 0-3-3"/></svg></span>
                <p class="ad-card-value"><?php echo number_format($totalMenus, 0, ',', '.'); ?></p>
                <p class="ad-card-note"><span class="ad-positive">&#8599; Total</span> registrado</p>
            </article>
            <article class="ad-card">
                <p class="ad-card-label">Novos Restaurantes</p>
                <span class="ad-icon-box">+</span>
                <p class="ad-card-value"><?php echo number_format($newRestaurants, 0, ',', '.'); ?></p>
                <p class="ad-card-note"><span class="ad-positive">&#8599; Novos</span> nos &uacute;ltimos 30 dias</p>
            </article>
        </section>

        <section class="ad-grid" aria-label="Graficos operacionais">
            <article class="ad-chart">
                <h2>An&aacute;lise de Performance</h2>
                <p>Volume de pedidos transacionados vs. ticket m&eacute;dio</p>
                <div class="ad-overview-canvas"><canvas id="mainComboChart"></canvas></div>
            </article>
            <article class="ad-chart">
                <header class="ad-chart-head">
                    <div>
                        <h2>Crescimento de Restaurantes</h2>
                        <p>NOVOS X RETEN&Ccedil;&Atilde;O</p>
                    </div>
                    <select aria-label="Periodo do crescimento">
                        <option>Mensal</option>
                        <option>Trimestral</option>
                        <option>Semestral</option>
                        <option>Anual</option>
                    </select>
                </header>
                <div class="ad-overview-canvas"><canvas id="growthRetentionChart"></canvas></div>
            </article>
        </section>

        <section class="ad-health">
            <h2>Sa&uacute;de Operacional</h2>
            <p>Monitoramento de m&eacute;tricas cr&iacute;ticas e estabilidade de infraestrutura</p>
            <div class="ad-health-grid">
                <article class="ad-health-card"><label><i class="success"></i>Uptime</label><strong>--</strong><span class="ad-positive">Sem dados de infraestrutura</span></article>
                <article class="ad-health-card"><label><i class="danger"></i>Falhas</label><strong>0</strong><span>Nenhum bloqueio ativo</span></article>
                <article class="ad-health-card"><label>Pedidos Ativos</label><strong><?php echo number_format($activeOrders, 0, ',', '.'); ?></strong><span class="ad-positive">Pedidos em processamento</span></article>
                <article class="ad-health-card"><label>Tempo M&eacute;dio</label><strong>--</strong><span>Sem dados dispon&iacute;veis</span></article>
                <article class="ad-health-card ad-gateways"><label>Gateways</label><strong>--</strong><span>Sem integra&ccedil;&atilde;o monitorada</span></article>
            </div>
        </section>
    </main>

    <script src="<?php echo BASE_URL; ?>/js/admin-delicacy.js"></script>
    <script>
        (function () {
            if (!window.Chart) {
                return;
            }

            var page = document.querySelector('.ad-overview-page');
            var charts = [];

            function palette() {
                return page.dataset.theme === 'dark'
                    ? { tick: '#71717a', grid: 'rgba(113, 113, 122, .15)' }
                    : { tick: '#71717a', grid: 'rgba(23, 23, 23, .10)' };
            }

            function options() {
                var colors = palette();
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: colors.tick } },
                        y: { beginAtZero: true, suggestedMax: 1, grid: { color: colors.grid }, ticks: { color: colors.tick } }
                    }
                };
            }

            ['mainComboChart', 'growthRetentionChart'].forEach(function (id) {
                charts.push(new Chart(document.getElementById(id), {
                    type: 'bar',
                    data: { labels: [], datasets: [] },
                    options: options()
                }));
            });

            page.addEventListener('admin-theme-change', function () {
                charts.forEach(function (chart) {
                    chart.options = options();
                    chart.update();
                });
            });
        }());
    </script>
</body>
</html>
