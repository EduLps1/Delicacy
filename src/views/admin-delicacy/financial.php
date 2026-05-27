<?php
/**
 * DELICACY - Operacoes financeiras administrativas
 * Variaveis: $financial, $csrf_token
 */

$user = getAuthUser() ?: ['name' => 'Admin Delicacy'];
$userName = $user['name'] ?? 'Admin Delicacy';
$userEmail = $user['email'] ?? 'admin@delicacy.com.br';
$initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $userName), 0, 2) ?: 'AD');
$distribution = $financial['distribution'];
$distributionTotal = array_sum($distribution);
$plans = $financial['plans'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financeiro - Admin Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin-delicacy.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="platform-admin ad-shell-page" data-theme="dark" data-default-theme="dark">
    <aside class="ad-sidebar">
        <a class="ad-brand" href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php">
            <span class="ad-logo-box"><img src="<?php echo BASE_URL; ?>/images/auth/delicacy-symbol-cropped.png" alt=""></span>
            <span class="ad-brand-name">Delicacy</span>
        </a>
        <nav class="ad-nav" aria-label="Administracao Delicacy">
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M12 3v9h9"/><path d="M21 12a9 9 0 1 1-9-9"/></svg></span><span>Overview</span></a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M4 10l2-6h12l2 6"/><path d="M9 20v-6h6v6"/></svg></span><span>Restaurantes</span></a>
            <a class="active" aria-current="page" href="<?php echo BASE_URL; ?>/admin-delicacy/financial.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-8"/><path d="M22 20H2"/></svg></span><span>Financeiro</span></a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/users.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2"/><path d="M16 4a4 4 0 0 1 0 8"/><path d="M21 21v-2a6 6 0 0 0-4-5.65"/></svg></span><span>Usu&aacute;rios</span></a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/settings.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3A1.7 1.7 0 0 0 14 21v.2a2 2 0 0 1-4 0V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1A2 2 0 0 1 4.2 17l.1-.1A1.7 1.7 0 0 0 3 14h-.1a2 2 0 0 1 0-4H3a1.7 1.7 0 0 0 1.3-2.9L4.2 7A2 2 0 0 1 7 4.2l.1.1A1.7 1.7 0 0 0 10 3V3a2 2 0 0 1 4 0v.2a1.7 1.7 0 0 0 2.9 1.1l.1-.1A2 2 0 0 1 19.8 7l-.1.1A1.7 1.7 0 0 0 21 10h.1a2 2 0 0 1 0 4H21a1.7 1.7 0 0 0-1.6 1z"/></svg></span><span>Configura&ccedil;&otilde;es</span></a>
        </nav>
        <div class="ad-theme">
            <span class="ad-theme-label"><span data-admin-theme-glyph aria-hidden="true">&#9789;</span><span data-admin-theme-label>Dark Mode</span></span>
            <button class="ad-theme-toggle" type="button" data-admin-theme-toggle aria-label="Ativar modo claro" aria-pressed="true"></button>
        </div>
    </aside>

    <header class="ad-topbar">
        <label class="ad-search">
            <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg></span>
            <input type="search" placeholder="Buscar transacao ou metrica..." aria-label="Buscar transacao ou metrica">
        </label>
        <div class="ad-account">
            <span class="ad-bell ad-icon"><svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg></span>
            <span class="ad-divider"></span>
            <details class="ad-user-menu">
                <summary class="ad-profile ad-profile-trigger"><span class="ad-avatar"><?php echo htmlspecialchars($initials); ?></span><span><strong><?php echo htmlspecialchars($userName); ?></strong><small>ADMIN DELICACY</small></span></summary>
                <div class="ad-user-dropdown">
                    <div class="ad-dropdown-head"><strong><?php echo htmlspecialchars($userName); ?></strong><span><?php echo htmlspecialchars($userEmail); ?></span></div>
                    <a class="ad-logout" href="<?php echo BASE_URL; ?>/logout.php"><span aria-hidden="true">&#10132;</span> Sair</a>
                </div>
            </details>
        </div>
    </header>

    <main class="ad-main ad-financial">
        <header class="ad-head">
            <div class="ad-title">
                <h1>Opera&ccedil;&otilde;es Financeiras</h1>
                <p>Monetiza&ccedil;&atilde;o e performance financeira da plataforma</p>
            </div>
        </header>

        <section class="ad-metrics ad-financial-metrics" aria-label="Indicadores financeiros">
            <article class="ad-card"><p class="ad-card-label">GMV Global</p><span class="ad-icon-box">R$</span><p class="ad-card-value"><?php echo formatCurrency($financial['gmv']); ?></p></article>
            <article class="ad-card"><p class="ad-card-label">Receita L&iacute;quida</p><span class="ad-icon-box ad-positive">$</span><p class="ad-card-value"><?php echo formatCurrency($financial['net_revenue']); ?></p></article>
            <article class="ad-card"><p class="ad-card-label">Forecast (m&ecirc;s)</p><span class="ad-icon-box ad-forecast-icon">&#8599;</span><p class="ad-card-value"><?php echo formatCurrency($financial['forecast']); ?></p></article>
            <article class="ad-card"><p class="ad-card-label">MRR Atual</p><span class="ad-icon-box ad-cycle-icon">&#8635;</span><p class="ad-card-value"><?php echo formatCurrency($financial['mrr']); ?></p></article>
        </section>

        <section class="ad-financial-charts">
            <article class="ad-finance-panel ad-finance-forecast">
                <h2>Forecast Financeiro</h2>
                <p>Receita realizada vs. crescimento previsto por intelig&ecirc;ncia de dados</p>
                <div class="ad-canvas-wrap"><canvas id="forecastChart"></canvas></div>
            </article>
            <article class="ad-finance-panel">
                <h2>Distribui&ccedil;&atilde;o da Receita</h2>
                <p>Divis&atilde;o por modelo de monetiza&ccedil;&atilde;o</p>
                <?php if ($distributionTotal > 0): ?>
                    <div class="ad-canvas-wrap ad-doughnut-wrap"><canvas id="distributionChart"></canvas></div>
                <?php else: ?>
                    <div class="ad-finance-empty">Nenhuma receita liquidada no per&iacute;odo.</div>
                <?php endif; ?>
            </article>
        </section>

        <section class="ad-financial-bottom">
            <article class="ad-finance-panel">
                <div class="ad-panel-title">
                    <div><h2>Sa&uacute;de Financeira</h2><p>M&eacute;tricas de liquida&ccedil;&atilde;o e estabilidade de fluxo</p></div>
                    <span class="ad-health-badge"><i></i> Monitorada</span>
                </div>
                <div class="ad-finance-kpis">
                    <div><span>Comiss&otilde;es liquidadas</span><strong><?php echo number_format($financial['settlement_rate'], 2, ',', '.'); ?>%</strong></div>
                    <div><span>Faturas pendentes</span><strong class="warning"><?php echo number_format($financial['pending_invoices'], 0, ',', '.'); ?> faturas</strong></div>
                    <div><span>Valor a processar</span><strong><?php echo formatCurrency($financial['pending_value']); ?></strong></div>
                </div>
            </article>
            <article class="ad-finance-panel">
                <h2>Performance de Monetiza&ccedil;&atilde;o</h2>
                <p>Volume capturado por camadas de planos corporativos</p>
                <div class="ad-plan-list">
                    <div><span>Plano Basic</span><strong><?php echo formatCurrency($plans['basic']); ?></strong></div>
                    <div><span>Plano Premium</span><strong><?php echo formatCurrency($plans['premium']); ?></strong></div>
                    <div><span>Plano Custom</span><strong><?php echo formatCurrency($plans['custom']); ?></strong></div>
                    <div class="total"><span>Comiss&atilde;o Capturada<small>Soma das monetiza&ccedil;&otilde;es liquidadas</small></span><strong><?php echo formatCurrency($financial['net_revenue']); ?></strong></div>
                </div>
            </article>
        </section>
    </main>
    <script src="<?php echo BASE_URL; ?>/js/admin-delicacy.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') {
                return;
            }

            var page = document.querySelector('.platform-admin');
            var axisColor = '#71717a';
            var labels = <?php echo json_encode($financial['labels']); ?>;
            var realized = <?php echo json_encode($financial['revenue_series']); ?>;
            var forecast = <?php echo json_encode($financial['forecast_series']); ?>;
            var distribution = <?php echo json_encode(array_values($distribution)); ?>;
            var charts = [];

            charts.push(new Chart(document.getElementById('forecastChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Realizado', data: realized, borderColor: '#f33c43', backgroundColor: 'rgba(243,60,67,.08)', fill: true, tension: .32, borderWidth: 2 },
                        { label: 'Previsto (Forecast)', data: forecast, borderColor: axisColor, borderDash: [5, 5], tension: .32, borderWidth: 1.5 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: axisColor, boxWidth: 12, font: { size: 11 } } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: axisColor } },
                        y: { grid: { color: 'rgba(113,113,122,.14)' }, ticks: { color: axisColor, callback: function (value) { return 'R$ ' + value; } } }
                    }
                }
            }));

            if (document.getElementById('distributionChart')) {
                charts.push(new Chart(document.getElementById('distributionChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Plano', 'Comissao', 'Hibrido'],
                        datasets: [{ data: distribution, backgroundColor: ['#f33c43', '#2b2d35', '#71717a'], borderWidth: 0 }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '74%',
                        plugins: { legend: { position: 'bottom', labels: { color: axisColor, boxWidth: 12, font: { size: 11 } } } }
                    }
                }));
            }

            if (page) {
                new MutationObserver(function () {
                    charts.forEach(function (chart) { chart.update(); });
                }).observe(page, { attributes: true, attributeFilter: ['data-theme'] });
            }
        }());
    </script>
</body>
</html>
