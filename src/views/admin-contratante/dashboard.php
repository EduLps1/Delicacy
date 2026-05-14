<?php
/**
 * DELICACY - Painel do Contratante
 * Variaveis: $restaurant, $contractorStats, $dailySeries
 */

$message = getSessionMessage();
$user = getAuthUser() ?: ['name' => 'Contratante', 'email' => ''];
$displayName = $restaurant['name'] ?? $user['name'] ?? 'Contratante';
$displayEmail = $restaurant['email'] ?? $user['email'] ?? '';
$cleanInitials = preg_replace('/[^A-Za-z0-9]/', '', $displayName);
$initials = strtoupper(substr($cleanInitials, 0, 2) ?: 'CT');

$stats = $contractorStats ?? [
    'period_days' => 30,
    'revenue' => 0.0,
    'commission' => 0.0,
    'received' => 0.0,
    'orders' => 0,
    'average_ticket' => 0.0
];

$series = $dailySeries ?? [];
if (empty($series)) {
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $series[] = [
            'day' => (int)date('j', strtotime($date)),
            'orders' => 0,
            'revenue' => 0.0
        ];
    }
}

$periodLabels = [
    7 => 'Últimos 7 dias',
    30 => 'Últimos 30 dias',
    90 => 'Últimos 90 dias',
    365 => 'Últimos 12 meses'
];
$selectedPeriod = (int)($stats['period_days'] ?? 30);
if (!isset($periodLabels[$selectedPeriod])) {
    $selectedPeriod = 30;
}

$maxRevenue = max(1, max(array_map(function ($row) {
    return (float)($row['revenue'] ?? 0);
}, $series)));
$maxOrders = max(1, max(array_map(function ($row) {
    return (int)($row['orders'] ?? 0);
}, $series)));

$pointCount = count($series);
$linePoints = [];
foreach ($series as $index => $row) {
    $x = $pointCount > 1 ? ($index / ($pointCount - 1)) * 760 : 0;
    $y = 210 - (((float)$row['revenue'] / $maxRevenue) * 178);
    $linePoints[] = round($x, 2) . ',' . round($y, 2);
}
$areaPoints = '0,210 ' . implode(' ', $linePoints) . ' 760,210';

function contractorPeriodUrl($period) {
    return BASE_URL . '/admin-contratante/?period=' . (int)$period;
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
            --page-bg: #fbf7f5;
            --surface: #fff;
            --border: #dde5ee;
            --ink: #050608;
            --muted: #6a4f49;
            --red: #ef3d3a;
            --red-soft: #ffe8e8;
            --green: #10a66c;
            --green-soft: #e5f8ee;
            --blue: #5947ff;
            --blue-soft: #eeedff;
            --orange: #e98a08;
            --orange-soft: #fff2dc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            grid-template-rows: 53px 1fr;
            background: var(--page-bg);
            color: var(--ink);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
        }

        svg {
            display: block;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--ink);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0;
            text-decoration: none;
        }

        .brand-mark,
        .nav-mark,
        .metric-mark,
        .avatar {
            display: grid;
            place-items: center;
        }

        .brand-mark {
            width: 27px;
            height: 27px;
            border-radius: 8px;
            background: var(--red);
            color: #fff;
        }

        .brand-mark svg,
        .nav-mark svg,
        .metric-mark svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .user-menu,
        .period-menu {
            position: relative;
        }

        .user-menu summary,
        .period-menu summary {
            list-style: none;
            cursor: pointer;
        }

        .user-menu summary::-webkit-details-marker,
        .period-menu summary::-webkit-details-marker {
            display: none;
        }

        .user-trigger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 37px;
            padding: 4px 5px 4px 12px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            font-size: 12px;
            font-weight: 800;
        }

        .avatar {
            width: 27px;
            height: 27px;
            border-radius: 50%;
            background: var(--red);
            color: #fff;
            font-size: 10px;
            font-weight: 900;
        }

        .menu-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 6px);
            z-index: 20;
            min-width: 196px;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.13);
        }

        .menu-head {
            padding: 10px 12px 11px;
            border-bottom: 1px solid #edf1f5;
        }

        .menu-head strong,
        .menu-head span {
            display: block;
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .menu-head strong {
            font-size: 12px;
        }

        .menu-head span {
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }

        .logout-link,
        .period-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-height: 34px;
            padding: 0 12px;
            color: var(--ink);
            font-size: 12px;
            text-decoration: none;
        }

        .logout-link {
            justify-content: flex-start;
            color: var(--red);
            font-weight: 700;
        }

        .app-layout {
            display: grid;
            grid-template-columns: 195px minmax(0, 1fr);
            min-height: 0;
        }

        .sidebar {
            padding: 14px 14px 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
        }

        .nav-list {
            display: grid;
            gap: 4px;
        }

        .nav-list a {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 31px;
            padding: 0 10px;
            border-radius: 8px;
            color: #050608;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .nav-list a:hover {
            background: #fff0ef;
        }

        .nav-list a.active {
            background: var(--red);
            color: #fff;
        }

        .main {
            min-width: 0;
            padding: 28px 27px 40px;
            overflow: auto;
        }

        .title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 29px;
        }

        .title-block h1 {
            margin: 0;
            font-size: 25px;
            line-height: 1.1;
            font-weight: 900;
        }

        .title-block p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 12px;
        }

        .period-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #654b45;
            font-size: 12px;
        }

        .period-trigger {
            width: 166px;
            min-height: 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
            color: var(--ink);
            font-size: 12px;
        }

        .period-panel {
            left: 0;
            right: auto;
            width: 190px;
            padding: 8px 0;
        }

        .message {
            margin: -12px 0 18px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            color: var(--muted);
            font-size: 12px;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 27px;
        }

        .metric-card,
        .chart-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        .metric-card {
            position: relative;
            min-height: 122px;
            padding: 27px 42px 18px 21px;
        }

        .metric-label,
        .metric-value,
        .metric-sub {
            margin: 0;
        }

        .metric-label {
            color: #5c4039;
            font-size: 12px;
        }

        .metric-value {
            margin-top: 18px;
            color: #000;
            font-size: 21px;
            line-height: 1;
            font-weight: 900;
        }

        .metric-sub {
            margin-top: 6px;
            color: var(--muted);
            font-size: 10px;
        }

        .metric-mark {
            position: absolute;
            top: 21px;
            right: 20px;
            width: 30px;
            height: 30px;
            border-radius: 9px;
        }

        .metric-mark.green {
            background: var(--green-soft);
            color: var(--green);
        }

        .metric-mark.red {
            background: var(--red-soft);
            color: var(--red);
        }

        .metric-mark.blue {
            background: var(--blue-soft);
            color: var(--blue);
        }

        .metric-mark.orange {
            background: var(--orange-soft);
            color: var(--orange);
        }

        .chart-grid {
            display: grid;
            grid-template-columns: minmax(0, 2.05fr) minmax(340px, 1fr);
            gap: 14px;
        }

        .chart-card {
            min-height: 347px;
            padding: 20px 20px 18px;
        }

        .chart-card h2 {
            margin: 0;
            font-size: 13px;
            font-weight: 900;
        }

        .chart-card p {
            margin: 7px 0 18px;
            color: var(--muted);
            font-size: 12px;
        }

        .area-chart,
        .bar-chart {
            height: 235px;
            background:
                linear-gradient(to bottom, rgba(221, 229, 238, 0.65) 1px, transparent 1px) 0 0 / 100% 25%;
        }

        .area-chart {
            position: relative;
        }

        .axis {
            color: var(--muted);
            font-size: 10px;
        }

        .area-axis {
            position: absolute;
            inset: 0 auto 0 0;
            width: 58px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-bottom: 25px;
        }

        .area-svg {
            position: absolute;
            inset: 0 0 0 58px;
        }

        .area-svg svg {
            width: 100%;
            height: 100%;
        }

        .bar-chart {
            display: grid;
            grid-template-columns: 31px 1fr;
            gap: 11px;
        }

        .bar-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-bottom: 26px;
            text-align: right;
        }

        .bar-plot {
            display: grid;
            grid-template-columns: repeat(30, minmax(4px, 1fr));
            align-items: end;
            gap: 5px;
            padding-bottom: 26px;
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
            min-height: 2px;
            border-radius: 4px;
            background: var(--red);
            opacity: 0.35;
        }

        .bar-label {
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--muted);
            font-size: 10px;
        }

        .legend {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 9px;
            color: var(--muted);
            font-size: 10px;
        }

        .legend::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 2px;
            background: var(--red);
        }

        @media (max-width: 1220px) {
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

            .topbar {
                align-items: flex-start;
                padding: 10px 12px;
                gap: 10px;
                flex-direction: column;
            }

            .app-layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-right: 0;
                border-bottom: 1px solid var(--border);
            }

            .nav-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .main {
                padding: 22px 14px 32px;
            }

            .title-row,
            .period-wrap {
                flex-direction: column;
                align-items: stretch;
            }

            .period-trigger,
            .metric-grid {
                width: 100%;
            }

            .metric-grid {
                grid-template-columns: 1fr;
            }

            .bar-plot {
                gap: 3px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="<?php echo BASE_URL; ?>/admin-contratante/">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 21h16" /><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16" /><path d="M9 8h1" /><path d="M14 8h1" /><path d="M9 12h1" /><path d="M14 12h1" /></svg>
            </span>
            PAINEL DO CONTRATANTE
        </a>

        <details class="user-menu">
            <summary class="user-trigger">
                <span><?php echo htmlspecialchars($displayName); ?></span>
                <span class="avatar"><?php echo htmlspecialchars($initials); ?></span>
            </summary>
            <div class="menu-panel">
                <div class="menu-head">
                    <strong><?php echo htmlspecialchars($displayName); ?></strong>
                    <span><?php echo htmlspecialchars($displayEmail ?: 'sem email cadastrado'); ?></span>
                </div>
                <a class="logout-link" href="<?php echo BASE_URL; ?>/logout.php">
                    <span aria-hidden="true">&rarr;</span>
                    Sair
                </a>
            </div>
        </details>
    </header>

    <div class="app-layout">
        <aside class="sidebar" aria-label="Menu do contratante">
            <nav class="nav-list">
                <a class="active" href="<?php echo BASE_URL; ?>/admin-contratante/">
                    <span class="nav-mark"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="6" height="6" rx="1" /><rect x="14" y="4" width="6" height="6" rx="1" /><rect x="4" y="14" width="6" height="6" rx="1" /><rect x="14" y="14" width="6" height="6" rx="1" /></svg></span>
                    Geral
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php">
                    <span class="nav-mark"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" /></svg></span>
                    Cardápios
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php">
                    <span class="nav-mark"><svg viewBox="0 0 24 24"><path d="M6 3h12l1 4H5z" /><path d="M5 7h14v13H5z" /><path d="M9 12h6" /></svg></span>
                    Pedidos
                </a>
                <a href="#">
                    <span class="nav-mark"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2" /><circle cx="9.5" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /></svg></span>
                    Clientes
                </a>
                <a href="#">
                    <span class="nav-mark"><svg viewBox="0 0 24 24"><path d="M12 2v20" /><path d="M5 9h14" /><path d="M7 4l10 16" /><path d="M17 4L7 20" /></svg></span>
                    Fidelidade
                </a>
                <a href="<?php echo BASE_URL; ?>/admin-contratante/editar-restaurante.php">
                    <span class="nav-mark"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-.6a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1H4a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6V4a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 .5a1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.2.36.4.69.6 1H20a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-.5 1z" /></svg></span>
                    Configurações
                </a>
            </nav>
        </aside>

        <main class="main">
            <div class="title-row">
                <section class="title-block">
                    <h1>Visão Geral</h1>
                    <p>Acompanhe o desempenho do seu estabelecimento no período selecionado.</p>
                </section>

                <div class="period-wrap">
                    <span>Período</span>
                    <details class="period-menu">
                        <summary class="period-trigger">
                            <span><?php echo htmlspecialchars($periodLabels[$selectedPeriod]); ?></span>
                            <span aria-hidden="true">⌄</span>
                        </summary>
                        <div class="menu-panel period-panel">
                            <?php foreach ($periodLabels as $value => $label): ?>
                                <a class="period-option" href="<?php echo contractorPeriodUrl($value); ?>">
                                    <span><?php echo htmlspecialchars($label); ?></span>
                                    <?php if ($selectedPeriod === $value): ?>
                                        <span aria-hidden="true">✓</span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </details>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                    <?php echo htmlspecialchars($message['text']); ?>
                </div>
            <?php endif; ?>

            <section class="metric-grid" aria-label="Indicadores do contratante">
                <article class="metric-card">
                    <p class="metric-label">Faturamento total</p>
                    <p class="metric-value"><?php echo formatCurrency($stats['revenue']); ?></p>
                    <p class="metric-sub">↗ <?php echo htmlspecialchars($periodLabels[$selectedPeriod]); ?></p>
                    <span class="metric-mark green"><svg viewBox="0 0 24 24"><path d="M12 3v18" /><path d="M16 7.5c0-1.4-1.8-2.5-4-2.5S8 6.1 8 7.5s1.8 2.5 4 2.5s4 1.1 4 2.5s-1.8 2.5-4 2.5s-4-1.1-4-2.5" /></svg></span>
                </article>

                <article class="metric-card">
                    <p class="metric-label">Comissão cobrada</p>
                    <p class="metric-value"><?php echo formatCurrency($stats['commission']); ?></p>
                    <p class="metric-sub">↗ <?php echo htmlspecialchars($restaurant['active_commission_rate'] ?? 0); ?>% sobre o faturamento</p>
                    <span class="metric-mark red"><svg viewBox="0 0 24 24"><path d="M19 5L5 19" /><circle cx="7" cy="7" r="2" /><circle cx="17" cy="17" r="2" /></svg></span>
                </article>

                <article class="metric-card">
                    <p class="metric-label">Valor recebido</p>
                    <p class="metric-value"><?php echo formatCurrency($stats['received']); ?></p>
                    <p class="metric-sub">↗ Faturamento - comissão</p>
                    <span class="metric-mark red"><svg viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="16" rx="2" /><path d="M9 9h6" /><path d="M9 13h3" /><path d="M15 13l2 2-2 2" /></svg></span>
                </article>

                <article class="metric-card">
                    <p class="metric-label">Quantidade de pedidos</p>
                    <p class="metric-value"><?php echo number_format((int)$stats['orders'], 0, ',', '.'); ?></p>
                    <p class="metric-sub">↗ <?php echo htmlspecialchars($periodLabels[$selectedPeriod]); ?></p>
                    <span class="metric-mark blue"><svg viewBox="0 0 24 24"><path d="M6 3h12l1 4H5z" /><path d="M5 7h14v13H5z" /></svg></span>
                </article>

                <article class="metric-card">
                    <p class="metric-label">Ticket médio</p>
                    <p class="metric-value"><?php echo formatCurrency($stats['average_ticket']); ?></p>
                    <p class="metric-sub">↗ Faturamento ÷ pedidos</p>
                    <span class="metric-mark orange"><svg viewBox="0 0 24 24"><path d="M7 4h10v16H7z" /><path d="M12 8v8" /><path d="M15 10.5c0-1.2-1.2-2-3-2s-3 .8-3 2s1.2 2 3 2s3 .8 3 2s-1.2 2-3 2s-3-.8-3-2" /></svg></span>
                </article>
            </section>

            <section class="chart-grid" aria-label="Gráficos do contratante">
                <article class="chart-card">
                    <h2>Faturamento</h2>
                    <p><?php echo htmlspecialchars($periodLabels[$selectedPeriod]); ?></p>
                    <div class="area-chart">
                        <div class="axis area-axis">
                            <span>R$2k</span>
                            <span>R$2k</span>
                            <span>R$1k</span>
                            <span>R$1k</span>
                            <span>R$0k</span>
                        </div>
                        <div class="area-svg">
                            <svg viewBox="0 0 760 235" role="img" aria-label="Faturamento diário">
                                <defs>
                                    <linearGradient id="revenueFill" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#ef3d3a" stop-opacity="0.24" />
                                        <stop offset="100%" stop-color="#ef3d3a" stop-opacity="0.02" />
                                    </linearGradient>
                                </defs>
                                <polygon points="<?php echo htmlspecialchars($areaPoints); ?>" fill="url(#revenueFill)" />
                                <polyline points="<?php echo htmlspecialchars(implode(' ', $linePoints)); ?>" fill="none" stroke="#ef3d3a" stroke-width="2.2" />
                                <?php foreach ($series as $index => $row): ?>
                                    <?php if ($index % max(1, (int)floor(count($series) / 15)) === 0): ?>
                                        <text x="<?php echo ($index / max(1, count($series) - 1)) * 760; ?>" y="232" fill="#6a4f49" font-size="10" text-anchor="middle"><?php echo (int)$row['day']; ?></text>
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
                        <div class="axis bar-axis">
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
