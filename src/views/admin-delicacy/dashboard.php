<?php
/**
 * DELICACY - Admin Delicacy Dashboard
 * Variaveis vindas do controller: $stats, $csrf_token
 */

$message = getSessionMessage();
$user = getAuthUser() ?: ['name' => 'Admin Delicacy'];

$activeRestaurants = (int)($stats['total_restaurants'] ?? 0);
$inactiveRestaurants = (int)($stats['total_inactive'] ?? 0);
$totalRestaurants = $activeRestaurants + $inactiveRestaurants;
$totalMenus = (int)($stats['total_menus'] ?? 0);
$estimatedRevenue = (float)($stats['estimated_revenue'] ?? 0);

$monthlyRevenue = array_fill(0, 6, 0);
$monthlyActive = array_fill(0, 5, 0);
$monthlyInactive = array_fill(0, 5, 0);
$monthsRevenue = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
$monthsRestaurants = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai'];
$maxRevenue = max(1, max($monthlyRevenue));
$maxRestaurants = max(1, max($monthlyActive), max($monthlyInactive));

function adminNumber($value)
{
    return number_format((int)$value, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <style>
        :root {
            --admin-bg: #fbf7f5;
            --admin-surface: #ffffff;
            --admin-border: #dfe7ef;
            --admin-red: #ef3d35;
            --admin-red-soft: #ffe8e7;
            --admin-green: #0dbf74;
            --admin-green-soft: #def8eb;
            --admin-orange: #ff9f1c;
            --admin-orange-soft: #fff5df;
            --admin-purple: #5f4bff;
            --admin-purple-soft: #eeedff;
            --admin-ink: #08090b;
            --admin-muted: #684f48;
            --admin-brown: #765f59;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            grid-template-rows: 54px 1fr;
            background: var(--admin-bg);
            color: var(--admin-ink);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
        }

        .admin-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.35rem 0 0.85rem;
            background: #fff;
            border-bottom: 1px solid var(--admin-border);
        }

        .admin-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--admin-ink);
            font-size: 0.92rem;
            font-weight: 900;
            letter-spacing: 0.02em;
            text-decoration: none;
        }

        .brand-shield,
        .top-avatar {
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: var(--admin-red);
            color: #fff;
        }

        .brand-shield {
            width: 28px;
            height: 28px;
        }

        .brand-shield svg,
        .admin-icon svg,
        .nav-icon svg {
            width: 17px;
            height: 17px;
            stroke: currentColor;
            stroke-width: 2.1;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .top-user {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            min-height: 36px;
            padding: 0.25rem 0.35rem 0.25rem 0.75rem;
            border: 1px solid var(--admin-border);
            border-radius: 999px;
            background: #fff;
            color: var(--admin-ink);
            font-size: 0.86rem;
            font-weight: 800;
        }

        .top-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-size: 0.68rem;
            font-weight: 900;
        }

        .admin-layout {
            min-height: 0;
            display: grid;
            grid-template-columns: 193px 1fr;
        }

        .admin-sidebar {
            padding: 0.8rem 0.45rem;
            background: #fff;
            border-right: 1px solid var(--admin-border);
        }

        .admin-nav {
            display: grid;
            gap: 0.45rem;
        }

        .admin-nav-link {
            min-height: 32px;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0 0.75rem;
            border-radius: 8px;
            color: #101010;
            font-size: 0.83rem;
            font-weight: 800;
            text-decoration: none;
        }

        .admin-nav-link:hover {
            color: #101010;
            background: #f7eeee;
        }

        .admin-nav-link.active {
            background: var(--admin-red);
            color: #fff;
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            display: grid;
            place-items: center;
            color: currentColor;
        }

        .admin-main {
            min-width: 0;
            padding: 0 0 2rem;
            overflow: auto;
        }

        .admin-content {
            padding: 0.9rem 1.65rem 0;
        }

        .page-heading {
            margin-bottom: 1.65rem;
        }

        .page-heading h1 {
            margin: 0;
            color: var(--admin-ink);
            font-size: 1.65rem;
            line-height: 1.1;
            font-weight: 900;
        }

        .page-heading p {
            margin: 0.35rem 0 0;
            color: var(--admin-muted);
            font-size: 0.82rem;
        }

        .admin-message {
            margin: 0 0 1rem;
            padding: 0.85rem 1rem;
            border-radius: 10px;
            background: #fff;
            border: 1px solid var(--admin-border);
            color: var(--admin-muted);
            font-size: 0.86rem;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
            margin-bottom: 0.85rem;
        }

        .metric-card,
        .revenue-card,
        .chart-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: 14px;
            box-shadow: 0 2px 2px rgba(11, 28, 43, 0.06);
        }

        .metric-card {
            position: relative;
            min-height: 108px;
            padding: 1.55rem 1.35rem 1rem;
        }

        .metric-card.wide {
            grid-column: span 1;
        }

        .metric-label {
            margin: 0 0 0.95rem;
            color: #4f342d;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .metric-value {
            margin: 0;
            color: #000;
            font-size: 1.75rem;
            line-height: 1;
            font-weight: 900;
        }

        .admin-icon {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            width: 31px;
            height: 31px;
            display: grid;
            place-items: center;
            border-radius: 10px;
        }

        .admin-icon.green {
            background: var(--admin-green-soft);
            color: var(--admin-green);
        }

        .admin-icon.red {
            background: var(--admin-red-soft);
            color: var(--admin-red);
        }

        .admin-icon.orange {
            background: var(--admin-orange-soft);
            color: var(--admin-orange);
        }

        .admin-icon.purple {
            background: var(--admin-purple-soft);
            color: var(--admin-purple);
        }

        .revenue-card {
            position: relative;
            min-height: 132px;
            margin: 0.85rem 0 1.6rem;
            padding: 1.7rem 1.35rem;
        }

        .revenue-card .metric-label {
            margin-bottom: 1.1rem;
        }

        .revenue-value {
            margin: 0;
            color: #000;
            font-size: 1.7rem;
            font-weight: 900;
            line-height: 1;
        }

        .revenue-note {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.95rem;
            color: #00835a;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: minmax(0, 2.1fr) minmax(320px, 1fr);
            gap: 0.85rem;
        }

        .chart-card {
            min-height: 300px;
            padding: 1.4rem 1.35rem 1.2rem;
        }

        .chart-card h2 {
            margin: 0;
            color: #000;
            font-size: 0.95rem;
            font-weight: 900;
        }

        .chart-card p {
            margin: 0.4rem 0 1rem;
            color: var(--admin-muted);
            font-size: 0.8rem;
        }

        .area-chart {
            position: relative;
            height: 215px;
            margin-top: 0.6rem;
            border-left: 0;
            background:
                linear-gradient(to bottom, rgba(223, 231, 239, 0.65) 1px, transparent 1px) 0 0 / 100% 25%;
        }

        .area-chart svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .axis-labels {
            position: absolute;
            inset: 0 auto 0 0;
            width: 54px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #684f48;
            font-size: 0.68rem;
            pointer-events: none;
        }

        .area-svg-wrap {
            position: absolute;
            inset: 0 0 0 54px;
        }

        .bar-chart {
            height: 215px;
            display: grid;
            grid-template-columns: 35px 1fr;
            gap: 0.65rem;
            margin-top: 0.6rem;
        }

        .bar-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #684f48;
            font-size: 0.68rem;
            text-align: right;
            padding-bottom: 1.4rem;
        }

        .bar-plot {
            position: relative;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            align-items: end;
            gap: 0.65rem;
            padding: 0 0 1.4rem;
            background:
                linear-gradient(to bottom, rgba(223, 231, 239, 0.65) 1px, transparent 1px) 0 0 / 100% 25%;
        }

        .bar-group {
            height: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.22rem;
            align-items: end;
            position: relative;
        }

        .bar {
            min-height: 0;
            border-radius: 4px;
        }

        .bar.active {
            background: var(--admin-red);
        }

        .bar.inactive {
            background: #765f59;
        }

        .bar-label {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1.25rem;
            color: #684f48;
            font-size: 0.68rem;
            text-align: center;
        }

        .empty-hint {
            color: #9b8d88;
            font-size: 0.76rem;
            text-align: center;
        }

        @media (max-width: 1100px) {
            .metrics-grid,
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            body {
                grid-template-rows: auto 1fr;
            }

            .admin-topbar {
                gap: 1rem;
                padding: 0.8rem;
                flex-wrap: wrap;
            }

            .admin-layout {
                grid-template-columns: 1fr;
            }

            .admin-sidebar {
                border-right: 0;
                border-bottom: 1px solid var(--admin-border);
            }

            .admin-nav {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .admin-nav-link {
                justify-content: center;
                padding: 0.55rem;
                font-size: 0.76rem;
            }

            .admin-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="admin-topbar">
        <a class="admin-brand" href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php">
            <span class="brand-shield" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.6-2.8 8-7 10-4.2-2-7-5.4-7-10V6z" /></svg>
            </span>
            ADMIN SERVER DELICACY
        </a>

        <div class="top-user">
            <span>Admin Delicacy</span>
            <span class="top-avatar">AD</span>
        </div>
    </header>

    <div class="admin-layout">
        <aside class="admin-sidebar" aria-label="Menu admin">
            <nav class="admin-nav">
                <a href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php" class="admin-nav-link active">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="4" y="4" width="6" height="6" rx="1" /><rect x="14" y="4" width="6" height="6" rx="1" /><rect x="4" y="14" width="6" height="6" rx="1" /><rect x="14" y="14" width="6" height="6" rx="1" /></svg>
                    </span>
                    Dashboard
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php" class="admin-nav-link">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 10l8-5 8 5" /><path d="M6 10v9h12v-9" /><path d="M9 19v-5h6v5" /></svg>
                    </span>
                    Restaurantes
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-delicacy/users.php" class="admin-nav-link">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" /><circle cx="9.5" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                    </span>
                    Usuários
                </a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-content">
                <section class="page-heading">
                    <h1>Dashboard Admin</h1>
                    <p>Bem-vindo ao painel de administração da Delicacy</p>
                </section>

                <?php if ($message): ?>
                    <div class="admin-message message-<?php echo htmlspecialchars($message['type']); ?>">
                        <?php echo htmlspecialchars($message['text']); ?>
                    </div>
                <?php endif; ?>

                <section class="metrics-grid" aria-label="Resumo da plataforma">
                    <article class="metric-card">
                        <p class="metric-label">Restaurantes ativos</p>
                        <p class="metric-value"><?php echo adminNumber($activeRestaurants); ?></p>
                        <span class="admin-icon green" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 10l8-5 8 5" /><path d="M6 10v9h12v-9" /><path d="M9 19v-5h6v5" /></svg>
                        </span>
                    </article>

                    <article class="metric-card">
                        <p class="metric-label">Restaurantes inativos</p>
                        <p class="metric-value"><?php echo adminNumber($inactiveRestaurants); ?></p>
                        <span class="admin-icon red" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 4l16 16" /><path d="M6 10v9h12v-5" /><path d="M4 10l3.7-2.3" /><path d="M12 5l8 5" /></svg>
                        </span>
                    </article>

                    <article class="metric-card">
                        <p class="metric-label">Total de restaurantes</p>
                        <p class="metric-value"><?php echo adminNumber($totalRestaurants); ?></p>
                        <span class="admin-icon red" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 10l8-5 8 5" /><path d="M6 10v9h12v-9" /><path d="M9 19v-5h6v5" /></svg>
                        </span>
                    </article>

                    <article class="metric-card wide">
                        <p class="metric-label">Cardápios cadastrados</p>
                        <p class="metric-value"><?php echo adminNumber($totalMenus); ?></p>
                        <span class="admin-icon orange" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M5 5.5A2.5 2.5 0 0 1 7.5 3H19v16H7.5A2.5 2.5 0 0 0 5 21.5z" /><path d="M5 5.5A2.5 2.5 0 0 0 2.5 3H2v16h.5A2.5 2.5 0 0 1 5 21.5" /></svg>
                        </span>
                    </article>
                </section>

                <section class="revenue-card" aria-label="Prospeccao de saldo">
                    <p class="metric-label">Prospecção de saldo total</p>
                    <p class="revenue-value"><?php echo formatCurrency($estimatedRevenue); ?></p>
                    <span class="revenue-note">
                        <span aria-hidden="true">↗</span>
                        Conectado à dashboard de métricas — receita estimada da plataforma
                    </span>
                    <span class="admin-icon purple" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="5" y="5" width="14" height="14" rx="2" /><path d="M9 9h6" /><path d="M9 13h3" /><path d="M15 13l2 2-2 2" /></svg>
                    </span>
                </section>

                <section class="charts-grid" aria-label="Graficos da plataforma">
                    <article class="chart-card">
                        <h2>Receita mensal</h2>
                        <p>Prospecção de saldo arrecadado nos últimos 6 meses</p>

                        <div class="area-chart">
                            <div class="axis-labels" aria-hidden="true">
                                <span>R$200k</span>
                                <span>R$150k</span>
                                <span>R$100k</span>
                                <span>R$50k</span>
                                <span>R$0k</span>
                            </div>
                            <div class="area-svg-wrap">
                                <svg viewBox="0 0 600 210" role="img" aria-label="Receita mensal zerada">
                                    <defs>
                                        <linearGradient id="revenueFill" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="0%" stop-color="#ef3d35" stop-opacity="0.24" />
                                            <stop offset="100%" stop-color="#ef3d35" stop-opacity="0.02" />
                                        </linearGradient>
                                    </defs>
                                    <polygon points="0,190 120,190 240,190 360,190 480,190 600,190 600,210 0,210" fill="url(#revenueFill)" />
                                    <polyline points="0,190 120,190 240,190 360,190 480,190 600,190" fill="none" stroke="#ef3d35" stroke-width="2.5" />
                                    <?php foreach ($monthsRevenue as $index => $month): ?>
                                        <text x="<?php echo 18 + ($index * 112); ?>" y="207" fill="#684f48" font-size="11"><?php echo htmlspecialchars($month); ?></text>
                                    <?php endforeach; ?>
                                </svg>
                            </div>
                        </div>
                    </article>

                    <article class="chart-card">
                        <h2>Restaurantes</h2>
                        <p>Ativos vs inativos por mês</p>

                        <div class="bar-chart">
                            <div class="bar-axis" aria-hidden="true">
                                <span>140</span>
                                <span>105</span>
                                <span>70</span>
                                <span>35</span>
                                <span>0</span>
                            </div>
                            <div class="bar-plot" role="img" aria-label="Restaurantes ativos e inativos zerados">
                                <?php foreach ($monthsRestaurants as $index => $month): ?>
                                    <?php
                                        $activeHeight = ((int)$monthlyActive[$index] / $maxRestaurants) * 100;
                                        $inactiveHeight = ((int)$monthlyInactive[$index] / $maxRestaurants) * 100;
                                    ?>
                                    <div class="bar-group">
                                        <div class="bar active" style="height: <?php echo $activeHeight; ?>%;"></div>
                                        <div class="bar inactive" style="height: <?php echo $inactiveHeight; ?>%;"></div>
                                        <span class="bar-label"><?php echo htmlspecialchars($month); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
