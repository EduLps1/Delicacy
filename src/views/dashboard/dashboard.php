<?php
/**
 * DELICACY - Painel do Contratante
 * Variaveis: $restaurant, $contractorStats, $dailySeries, $csrf_token
 */

$message = getSessionMessage();
$user = getAuthUser() ?: ['name' => 'Contratante'];
$restaurantName = $restaurant['name'] ?? $user['name'] ?? 'Contratante';
$restaurantEmail = $restaurant['email'] ?: ($user['email'] ?? '');
$initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $restaurantName), 0, 2) ?: 'CT');

$stats = $contractorStats ?? [
    'period_days' => 30,
    'revenue' => 0,
    'commission' => 0,
    'received' => 0,
    'orders' => 0,
    'average_ticket' => 0
];
$series = $dailySeries ?? [];
if (empty($series)) {
    for ($i = 29; $i >= 0; $i--) {
        $series[] = [
            'day' => (int)date('j', strtotime("-{$i} days")),
            'orders' => 0,
            'revenue' => 0
        ];
    }
}

$maxRevenue = max(1, max(array_map(function ($row) {
    return (float)($row['revenue'] ?? 0);
}, $series)));
$maxOrders = max(1, max(array_map(function ($row) {
    return (int)($row['orders'] ?? 0);
}, $series)));

$pointCount = count($series);
$points = [];
$areaPoints = [];
foreach ($series as $index => $row) {
    $x = $pointCount > 1 ? ($index / ($pointCount - 1)) * 760 : 0;
    $y = 210 - (((float)$row['revenue'] / $maxRevenue) * 180);
    $points[] = round($x, 2) . ',' . round($y, 2);
    $areaPoints[] = round($x, 2) . ',' . round($y, 2);
}
$areaPolygon = '0,210 ' . implode(' ', $areaPoints) . ' 760,210';

$periodLabels = [
    7 => 'Ãšltimos 7 dias',
    30 => 'Ãšltimos 30 dias',
    90 => 'Ãšltimos 90 dias',
    365 => 'Ãšltimos 12 meses'
];
$selectedPeriod = (int)($stats['period_days'] ?? 30);
if (!isset($periodLabels[$selectedPeriod])) {
    $selectedPeriod = 30;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Contratante - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <style>
        :root {
            --contract-bg: #fbf7f5;
            --contract-surface: #fff;
            --contract-border: #dfe7ef;
            --contract-red: #ef3d35;
            --contract-red-soft: #ffe8e7;
            --contract-green: #16b976;
            --contract-green-soft: #def8eb;
            --contract-blue: #5f4bff;
            --contract-blue-soft: #eeedff;
            --contract-orange: #f59e0b;
            --contract-orange-soft: #fff3dc;
            --contract-ink: #07080a;
            --contract-muted: #684f48;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            grid-template-rows: 49px 1fr;
            background: var(--contract-bg);
            color: var(--contract-ink);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
        }

        .contract-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.55rem 0 1rem;
            background: #fff;
            border-bottom: 1px solid var(--contract-border);
        }

        .contract-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.62rem;
            color: var(--contract-ink);
            font-size: 0.92rem;
            font-weight: 900;
            text-decoration: none;
        }

        .brand-icon,
        .user-avatar,
        .metric-icon,
        .nav-icon {
            display: grid;
            place-items: center;
        }

        .brand-icon {
            width: 27px;
            height: 27px;
            border-radius: 8px;
            background: var(--contract-red);
            color: #fff;
        }

        .brand-icon svg,
        .metric-icon svg,
        .nav-icon svg {
            width: 17px;
            height: 17px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .user-menu {
            position: relative;
        }

        .user-trigger {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            min-height: 37px;
            padding: 0.25rem 0.32rem 0.25rem 0.75rem;
            border: 1px solid var(--contract-border);
            border-radius: 999px;
            background: #fff;
            color: var(--contract-ink);
            font-size: 0.84rem;
            font-weight: 800;
            cursor: pointer;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--contract-red);
            color: #fff;
            font-size: 0.67rem;
            font-weight: 900;
        }

        .user-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 0.35rem);
            width: 210px;
            display: none;
            overflow: hidden;
            border: 1px solid var(--contract-border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
            z-index: 10;
        }

        .user-menu:hover .user-dropdown,
        .user-menu:focus-within .user-dropdown {
            display: block;
        }

        .dropdown-head {
            padding: 0.8rem 0.9rem;
            border-bottom: 1px solid #edf1f5;
        }

        .dropdown-head strong {
            display: block;
            color: var(--contract-ink);
            font-size: 0.83rem;
        }

        .dropdown-head span {
            display: block;
            margin-top: 0.25rem;
            color: var(--contract-muted);
            font-size: 0.72rem;
        }

        .dropdown-link {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.78rem 0.9rem;
            color: var(--contract-red);
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
        }

        .contract-layout {
            min-height: 0;
            display: grid;
            grid-template-columns: 195px 1fr;
        }

        .contract-sidebar {
            padding: 0.8rem 0.72rem;
            background: #fff;
            border-right: 1px solid var(--contract-border);
        }

        .contract-nav {
            display: grid;
            gap: 0.45rem;
        }

        .contract-nav a {
            min-height: 31px;
            display: flex;
            align-items: center;
            gap: 0.62rem;
            padding: 0 0.7rem;
            border-radius: 8px;
            color: #0c0d0f;
            font-size: 0.82rem;
            font-weight: 800;
            text-decoration: none;
        }

        .contract-nav a:hover {
            color: #0c0d0f;
            background: #f7eeee;
        }

        .contract-nav a.active {
            background: var(--contract-red);
            color: #fff;
        }

        .contract-main {
            min-width: 0;
            padding: 1.9rem 1.7rem 2.2rem;
            overflow: auto;
        }

        .page-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.8rem;
        }

        .page-title h1 {
            margin: 0;
            color: var(--contract-ink);
            font-size: 1.58rem;
            line-height: 1.1;
            font-weight: 900;
        }

        .page-title p {
            margin: 0.42rem 0 0;
            color: var(--contract-muted);
            font-size: 0.82rem;
        }

        .period-control {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #5d4640;
            font-size: 0.78rem;
        }

        .period-control select {
            width: 166px;
            height: 36px;
            padding: 0 0.8rem;
            border: 1px solid var(--contract-border);
            border-radius: 8px;
            background: #fff;
            color: var(--contract-ink);
            font-size: 0.84rem;
        }

        .contract-message {
            margin: 0 0 1rem;
            padding: 0.85rem 1rem;
            border: 1px solid var(--contract-border);
            border-radius: 8px;
            background: #fff;
            color: var(--contract-muted);
            font-size: 0.84rem;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.8rem;
            margin-bottom: 1.7rem;
        }

        .metric-card,
        .chart-card {
            border: 1px solid var(--contract-border);
            border-radius: 12px;
            background: var(--contract-surface);
            box-shadow: 0 2px 2px rgba(15, 23, 42, 0.06);
        }

        .metric-card {
            position: relative;
            min-height: 122px;
            padding: 1.5rem 1.25rem 1rem;
        }

        .metric-card p {
            margin: 0;
        }

        .metric-label {
            color: #5a3d36;
            font-size: 0.78rem;
        }

        .metric-value {
            margin-top: 1rem !important;
            color: #000;
            font-size: 1.45rem;
            line-height: 1;
            font-weight: 900;
        }

        .metric-sub {
            margin-top: 0.42rem !important;
            color: #5b4540;
            font-size: 0.68rem;
        }

        .metric-icon {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            width: 31px;
            height: 31px;
            border-radius: 9px;
        }

        .metric-icon.green {
            background: var(--contract-green-soft);
            color: var(--contract-green);
        }

        .metric-icon.red {
            background: var(--contract-red-soft);
            color: var(--contract-red);
        }

        .metric-icon.blue {
            background: var(--contract-blue-soft);
            color: var(--contract-blue);
        }

        .metric-icon.orange {
            background: var(--contract-orange-soft);
            color: var(--contract-orange);
        }

        .chart-grid {
            display: grid;
            grid-template-columns: minmax(0, 2.1fr) minmax(340px, 1fr);
            gap: 0.85rem;
        }

        .chart-card {
            min-height: 346px;
            padding: 1.35rem 1.25rem 1.1rem;
        }

        .chart-card h2 {
            margin: 0;
            color: #000;
            font-size: 0.95rem;
            font-weight: 900;
        }

        .chart-card p {
            margin: 0.35rem 0 1.1rem;
            color: var(--contract-muted);
            font-size: 0.78rem;
        }

        .area-chart {
            position: relative;
            height: 235px;
            background:
                linear-gradient(to bottom, rgba(223, 231, 239, 0.7) 1px, transparent 1px) 0 0 / 100% 25%;
        }

        .area-axis {
            position: absolute;
            inset: 0 auto 0 0;
            width: 58px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #684f48;
            font-size: 0.68rem;
            padding-bottom: 1.25rem;
        }

        .area-svg {
            position: absolute;
            inset: 0 0 0 58px;
        }

        .area-svg svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .bar-chart {
            height: 235px;
            display: grid;
            grid-template-columns: 30px 1fr;
            gap: 0.6rem;
            background:
                linear-gradient(to bottom, rgba(223, 231, 239, 0.7) 1px, transparent 1px) 0 0 / 100% 25%;
        }

        .bar-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #684f48;
            font-size: 0.68rem;
            text-align: right;
            padding-bottom: 1.45rem;
        }

        .bar-plot {
            display: grid;
            grid-template-columns: repeat(30, 1fr);
            align-items: end;
            gap: 0.22rem;
            padding-bottom: 1.45rem;
        }

        .bar-wrap {
            position: relative;
            height: 100%;
            display: flex;
            align-items: end;
            justify-content: center;
        }

        .bar {
            width: 100%;
            min-height: 0;
            border-radius: 4px;
            background: var(--contract-red);
        }

        .bar-label {
            position: absolute;
            bottom: -1.25rem;
            left: 50%;
            transform: translateX(-50%);
            color: #684f48;
            font-size: 0.62rem;
        }

        .legend {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.8rem;
            color: #684f48;
            font-size: 0.68rem;
        }

        .legend::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 2px;
            background: var(--contract-red);
        }

        @media (max-width: 1250px) {
            .metric-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .chart-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            body {
                grid-template-rows: auto 1fr;
            }

            .contract-topbar {
                padding: 0.75rem;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .contract-layout {
                grid-template-columns: 1fr;
            }

            .contract-sidebar {
                border-right: 0;
                border-bottom: 1px solid var(--contract-border);
            }

            .contract-nav {
                grid-template-columns: repeat(2, 1fr);
            }

            .contract-main {
                padding: 1.2rem 0.9rem;
            }

            .page-row,
            .period-control {
                align-items: stretch;
                flex-direction: column;
            }

            .period-control select,
            .metric-grid {
                width: 100%;
            }

            .metric-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="contract-topbar">
        <a class="contract-brand" href="<?php echo BASE_URL; ?>/admin-contratante/">
            <span class="brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 20h16" /><path d="M6 20V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v14" /><path d="M9 9h1" /><path d="M14 9h1" /><path d="M9 13h1" /><path d="M14 13h1" /></svg>
            </span>
            PAINEL DO CONTRATANTE
        </a>

        <div class="user-menu">
            <button class="user-trigger" type="button">
                <span><?php echo htmlspecialchars($restaurantName); ?></span>
                <span class="user-avatar"><?php echo htmlspecialchars($initials); ?></span>
            </button>
            <div class="user-dropdown">
                <div class="dropdown-head">
                    <strong><?php echo htmlspecialchars($restaurantName); ?></strong>
                    <span><?php echo htmlspecialchars($restaurantEmail ?: 'sem email cadastrado'); ?></span>
                </div>
                <a class="dropdown-link" href="<?php echo BASE_URL; ?>/logout.php">
                    <span aria-hidden="true">â†ª</span>
                    Sair
                </a>
            </div>
        </div>
    </header>

    <div class="contract-layout">
        <aside class="contract-sidebar" aria-label="Menu do contratante">
            <nav class="contract-nav">
                <a class="active" href="<?php echo BASE_URL; ?>/admin-contratante/">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="6" height="6" rx="1" /><rect x="14" y="4" width="6" height="6" rx="1" /><rect x="4" y="14" width="6" height="6" rx="1" /><rect x="14" y="14" width="6" height="6" rx="1" /></svg></span>
                    Geral
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M5 5.5A2.5 2.5 0 0 1 7.5 3H19v16H7.5A2.5 2.5 0 0 0 5 21.5z" /><path d="M5 5.5A2.5 2.5 0 0 0 2.5 3H2v16h.5A2.5 2.5 0 0 1 5 21.5" /></svg></span>
                    CardÃ¡pios
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M6 3h12l1 4H5z" /><path d="M5 7h14v13H5z" /><path d="M9 11h6" /></svg></span>
                    Pedidos
                </a>
                <a href="#">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" /><circle cx="9.5" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /></svg></span>
                    Clientes
                </a>
                <a href="#">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M12 3v18" /><path d="M5 10h14" /><path d="M7 5l10 14" /><path d="M17 5L7 19" /></svg></span>
                    Fidelidade
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-contratante/editar-restaurante.php">
                    <span class="nav-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1H4a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6V4a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 .5 1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.2.36.4.69.6 1H20a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-.5 1z" /></svg></span>
                    ConfiguraÃ§Ãµes
                </a>
            </nav>
        </aside>

        <main class="contract-main">
            <div class="page-row">
                <section class="page-title">
                    <h1>VisÃ£o Geral</h1>
                    <p>Acompanhe o desempenho do seu estabelecimento no perÃ­odo selecionado.</p>
                </section>

                <form class="period-control" method="GET" action="<?php echo BASE_URL; ?>/admin-contratante/">
                    <label for="period">PerÃ­odo</label>
                    <select id="period" name="period" onchange="this.form.submit()">
                        <?php foreach ($periodLabels as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo $selectedPeriod === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if ($message): ?>
                <div class="contract-message message-<?php echo htmlspecialchars($message['type']); ?>">
                    <?php echo htmlspecialchars($message['text']); ?>
                </div>
            <?php endif; ?>

            <section class="metric-grid" aria-label="Indicadores do contratante">
                <article class="metric-card">
                    <p class="metric-label">Faturamento total</p>
                    <p class="metric-value"><?php echo formatCurrency($stats['revenue']); ?></p>
                    <p class="metric-sub">â†— <?php echo htmlspecialchars($periodLabels[$selectedPeriod]); ?></p>
                    <span class="metric-icon green"><svg viewBox="0 0 24 24"><path d="M12 3v18" /><path d="M16 7.5c0-1.4-1.8-2.5-4-2.5S8 6.1 8 7.5s1.8 2.5 4 2.5 4 1.1 4 2.5-1.8 2.5-4 2.5-4-1.1-4-2.5" /></svg></span>
                </article>

                <article class="metric-card">
                    <p class="metric-label">ComissÃ£o cobrada</p>
                    <p class="metric-value"><?php echo formatCurrency($stats['commission']); ?></p>
                    <p class="metric-sub">â†— <?php echo htmlspecialchars($restaurant['active_commission_rate'] ?? 0); ?>% sobre o faturamento</p>
                    <span class="metric-icon red"><svg viewBox="0 0 24 24"><path d="M19 5L5 19" /><circle cx="7" cy="7" r="2" /><circle cx="17" cy="17" r="2" /></svg></span>
                </article>

                <article class="metric-card">
                    <p class="metric-label">Valor recebido</p>
                    <p class="metric-value"><?php echo formatCurrency($stats['received']); ?></p>
                    <p class="metric-sub">â†— Faturamento - comissÃ£o</p>
                    <span class="metric-icon red"><svg viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="16" rx="2" /><path d="M9 9h6" /><path d="M9 13h3" /><path d="M15 13l2 2-2 2" /></svg></span>
                </article>

                <article class="metric-card">
                    <p class="metric-label">Quantidade de pedidos</p>
                    <p class="metric-value"><?php echo number_format((int)$stats['orders'], 0, ',', '.'); ?></p>
                    <p class="metric-sub">â†— <?php echo htmlspecialchars($periodLabels[$selectedPeriod]); ?></p>
                    <span class="metric-icon blue"><svg viewBox="0 0 24 24"><path d="M6 3h12l1 4H5z" /><path d="M5 7h14v13H5z" /></svg></span>
                </article>

                <article class="metric-card">
                    <p class="metric-label">Ticket mÃ©dio</p>
                    <p class="metric-value"><?php echo formatCurrency($stats['average_ticket']); ?></p>
                    <p class="metric-sub">â†— Faturamento Ã· pedidos</p>
                    <span class="metric-icon orange"><svg viewBox="0 0 24 24"><path d="M7 4h10v16H7z" /><path d="M12 8v8" /><path d="M15 10.5c0-1.2-1.2-2-3-2s-3 .8-3 2 1.2 2 3 2 3 .8 3 2-1.2 2-3 2-3-.8-3-2" /></svg></span>
                </article>
            </section>

            <section class="chart-grid" aria-label="GrÃ¡ficos do contratante">
                <article class="chart-card">
                    <h2>Faturamento</h2>
                    <p><?php echo htmlspecialchars($periodLabels[$selectedPeriod]); ?></p>
                    <div class="area-chart">
                        <div class="area-axis">
                            <span>R$2k</span>
                            <span>R$2k</span>
                            <span>R$1k</span>
                            <span>R$1k</span>
                            <span>R$0k</span>
                        </div>
                        <div class="area-svg">
                            <svg viewBox="0 0 760 235" role="img" aria-label="Faturamento diÃ¡rio">
                                <defs>
                                    <linearGradient id="contractRevenueFill" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#ef3d35" stop-opacity="0.27" />
                                        <stop offset="100%" stop-color="#ef3d35" stop-opacity="0.02" />
                                    </linearGradient>
                                </defs>
                                <polygon points="<?php echo htmlspecialchars($areaPolygon); ?>" fill="url(#contractRevenueFill)" />
                                <polyline points="<?php echo htmlspecialchars(implode(' ', $points)); ?>" fill="none" stroke="#ef3d35" stroke-width="2.4" />
                                <?php foreach ($series as $index => $row): ?>
                                    <?php if ($index % max(1, (int)floor(count($series) / 15)) === 0): ?>
                                        <text x="<?php echo ($index / max(1, count($series) - 1)) * 760; ?>" y="232" fill="#684f48" font-size="10" text-anchor="middle"><?php echo (int)$row['day']; ?></text>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </svg>
                        </div>
                    </div>
                </article>

                <article class="chart-card">
                    <h2>Pedidos</h2>
                    <p>Volume por intervalo</p>
                    <div class="bar-chart">
                        <div class="bar-axis">
                            <span>32</span>
                            <span>24</span>
                            <span>16</span>
                            <span>8</span>
                            <span>0</span>
                        </div>
                        <div class="bar-plot">
                            <?php foreach (array_slice($series, -30) as $index => $row): ?>
                                <?php $height = ((int)$row['orders'] / $maxOrders) * 100; ?>
                                <div class="bar-wrap">
                                    <span class="bar" style="height: <?php echo $height; ?>%;"></span>
                                    <?php if ($index % 2 === 0): ?>
                                        <span class="bar-label"><?php echo (int)$row['day']; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="legend">Pedidos</div>
                </article>
            </section>
        </main>
    </div>
</body>
</html>
