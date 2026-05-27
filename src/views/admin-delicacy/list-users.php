<?php
/**
 * DELICACY - Central administrativa de usuarios
 * Variaveis: $userStats, $activity, $profiles
 */

$user = getAuthUser() ?: ['name' => 'Admin Delicacy'];
$userName = $user['name'] ?? 'Admin Delicacy';
$userEmail = $user['email'] ?? 'admin@delicacy.com.br';
$initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $userName), 0, 2) ?: 'AD');
$weeklyRegistrations = array_sum($activity['registrations'] ?? []);
$weeklyAccesses = array_sum($activity['accesses'] ?? []);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usu&aacute;rios - Admin Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin-delicacy.css">
</head>
<body class="platform-admin ad-shell-page" data-theme="dark" data-default-theme="dark">
    <aside class="ad-sidebar">
        <a class="ad-brand" href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php">
            <span class="ad-logo-box"><img src="<?php echo BASE_URL; ?>/images/auth/delicacy-symbol-cropped.png" alt=""></span>
            <span class="ad-brand-name">Delicacy</span>
        </a>
        <nav class="ad-nav" aria-label="Administracao Delicacy">
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M12 3v9h9"/><path d="M21 12a9 9 0 1 1-9-9"/></svg></span><span>Overview</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M4 10l2-6h12l2 6"/><path d="M9 20v-6h6v6"/></svg></span><span>Restaurantes</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/financial.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-8"/><path d="M22 20H2"/></svg></span><span>Financeiro</span>
            </a>
            <a class="active" href="<?php echo BASE_URL; ?>/admin-delicacy/users.php" aria-current="page">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2"/><path d="M16 4a4 4 0 0 1 0 8"/><path d="M21 21v-2a6 6 0 0 0-4-5.65"/></svg></span><span>Usu&aacute;rios</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/settings.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3A1.7 1.7 0 0 0 14 21v.2a2 2 0 0 1-4 0V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1A2 2 0 0 1 4.2 17l.1-.1A1.7 1.7 0 0 0 3 14h-.1a2 2 0 0 1 0-4H3a1.7 1.7 0 0 0 1.3-2.9L4.2 7A2 2 0 0 1 7 4.2l.1.1A1.7 1.7 0 0 0 10 3V3a2 2 0 0 1 4 0v.2a1.7 1.7 0 0 0 2.9 1.1l.1-.1A2 2 0 0 1 19.8 7l-.1.1A1.7 1.7 0 0 0 21 10h.1a2 2 0 0 1 0 4H21a1.7 1.7 0 0 0-1.6 1z"/></svg></span><span>Configura&ccedil;&otilde;es</span>
            </a>
        </nav>
        <div class="ad-theme">
            <span class="ad-theme-label"><span aria-hidden="true">&#9790;</span><span data-admin-theme-label>Dark Mode</span></span>
            <button class="ad-theme-toggle" type="button" data-admin-theme-toggle aria-label="Ativar modo claro" aria-pressed="true"></button>
        </div>
    </aside>

    <header class="ad-topbar">
        <label class="ad-search">
            <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg></span>
            <input type="search" placeholder="Buscar usuarios ou permissoes..." aria-label="Buscar usuarios ou permissoes">
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

    <main class="ad-main ad-users">
        <header class="ad-head">
            <div class="ad-title">
                <h1>Central de Usu&aacute;rios</h1>
                <p>Gest&atilde;o de usu&aacute;rios, tenants e permiss&otilde;es</p>
            </div>
        </header>

        <section class="ad-metrics ad-users-metrics" aria-label="Indicadores de usuarios">
            <article class="ad-card">
                <p class="ad-card-label">Usu&aacute;rios</p>
                <span class="ad-icon-box"><svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg></span>
                <p class="ad-card-value"><?php echo number_format((int)$userStats['users'], 0, ',', '.'); ?></p>
            </article>
            <article class="ad-card">
                <p class="ad-card-label">Restaurantes</p>
                <span class="ad-icon-box danger"><svg viewBox="0 0 24 24"><path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M4 10l2-6h12l2 6"/></svg></span>
                <p class="ad-card-value"><?php echo number_format((int)$userStats['restaurants'], 0, ',', '.'); ?></p>
            </article>
            <article class="ad-card">
                <p class="ad-card-label">Staff Ativos</p>
                <span class="ad-icon-box success"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-5"/></svg></span>
                <p class="ad-card-value"><?php echo number_format((int)$userStats['active_staff'], 0, ',', '.'); ?></p>
            </article>
            <article class="ad-card">
                <p class="ad-card-label">Convites</p>
                <span class="ad-icon-box info"><svg viewBox="0 0 24 24"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg></span>
                <p class="ad-card-value">--</p>
                <p class="ad-card-note">M&oacute;dulo n&atilde;o configurado</p>
            </article>
        </section>

        <section class="ad-users-panel ad-user-activity">
            <header>
                <h2>Atividade dos Usu&aacute;rios</h2>
                <p>Cadastros e &uacute;ltimos acessos registrados nos &uacute;ltimos 7 dias</p>
            </header>
            <div class="ad-chart-canvas">
                <canvas id="userActivityChart"></canvas>
            </div>
        </section>

        <div class="ad-users-grid">
            <section class="ad-users-panel ad-user-health">
                <header>
                    <h2>Sa&uacute;de Operacional</h2>
                    <p>Status dos dados de autentica&ccedil;&atilde;o dispon&iacute;veis</p>
                </header>
                <div class="ad-user-health-list">
                    <article><span>Acessos Recentes</span><strong><?php echo number_format($weeklyAccesses, 0, ',', '.'); ?></strong></article>
                    <article><span>Novos Usu&aacute;rios</span><strong class="success"><?php echo number_format($weeklyRegistrations, 0, ',', '.'); ?></strong></article>
                    <article><span>Sess&otilde;es Ativas</span><strong>-- <small>n&atilde;o monitorado</small></strong></article>
                    <article><span>Erro de Autentica&ccedil;&atilde;o</span><strong>-- <small>n&atilde;o monitorado</small></strong></article>
                </div>
            </section>

            <section class="ad-users-panel ad-profile-chart">
                <header>
                    <h2>Distribui&ccedil;&atilde;o de Perfis</h2>
                    <p>Divis&atilde;o estrutural dos pap&eacute;is cadastrados</p>
                </header>
                <div class="ad-profile-canvas">
                    <canvas id="profileDistributionChart"></canvas>
                </div>
            </section>
        </div>

        <section class="ad-insights ad-user-insights">
            <h2>Performance Operacional por Perfil</h2>
            <span>Coming Soon</span>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/admin-delicacy.js"></script>
    <script>
        (function () {
            if (!window.Chart) {
                return;
            }

            var page = document.querySelector('.platform-admin');
            var activityData = <?php echo json_encode($activity, JSON_UNESCAPED_SLASHES); ?>;
            var profileData = <?php echo json_encode(array_values($profiles), JSON_UNESCAPED_SLASHES); ?>;

            function chartTheme() {
                var dark = page.dataset.theme === 'dark';
                return {
                    muted: dark ? '#71717a' : '#667085',
                    grid: dark ? 'rgba(113, 113, 122, .14)' : 'rgba(102, 112, 133, .14)',
                    profile: dark ? ['#27272a', '#ef4444', '#71717a', '#d4d4d8'] : ['#111827', '#ef4444', '#71717a', '#d1d5db']
                };
            }

            var palette = chartTheme();
            var userActivityChart = new Chart(document.getElementById('userActivityChart'), {
                type: 'line',
                data: {
                    labels: activityData.labels,
                    datasets: [{
                        label: 'Novos usuarios',
                        data: activityData.registrations,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, .06)',
                        fill: true,
                        tension: .3,
                        borderWidth: 2,
                        pointRadius: 2
                    }, {
                        label: 'Ultimos acessos',
                        data: activityData.accesses,
                        borderColor: '#a1a1aa',
                        backgroundColor: 'transparent',
                        tension: .3,
                        borderWidth: 2,
                        pointRadius: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: palette.muted, boxWidth: 14, font: { size: 11 } } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: palette.muted } },
                        y: { beginAtZero: true, grid: { color: palette.grid }, ticks: { color: palette.muted, precision: 0 } }
                    }
                }
            });

            var profileDistributionChart = new Chart(document.getElementById('profileDistributionChart'), {
                type: 'pie',
                data: {
                    labels: ['Admin Delicacy', 'Contratantes', 'Atendentes', 'Clientes'],
                    datasets: [{ data: profileData, backgroundColor: palette.profile, borderWidth: 0 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { color: palette.muted, boxWidth: 12, font: { size: 11 } } } }
                }
            });

            page.addEventListener('admin-theme-change', function () {
                palette = chartTheme();
                userActivityChart.options.plugins.legend.labels.color = palette.muted;
                userActivityChart.options.scales.x.ticks.color = palette.muted;
                userActivityChart.options.scales.y.ticks.color = palette.muted;
                userActivityChart.options.scales.y.grid.color = palette.grid;
                profileDistributionChart.data.datasets[0].backgroundColor = palette.profile;
                profileDistributionChart.options.plugins.legend.labels.color = palette.muted;
                userActivityChart.update();
                profileDistributionChart.update();
            });
        }());
    </script>
</body>
</html>
