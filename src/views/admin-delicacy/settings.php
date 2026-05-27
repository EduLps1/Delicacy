<?php
/**
 * DELICACY - Configuracoes administrativas globais
 * Variaveis: $csrf_token
 */

$user = getAuthUser() ?: ['name' => 'Admin Delicacy'];
$userName = $user['name'] ?? 'Admin Delicacy';
$userEmail = $user['email'] ?? 'admin@delicacy.com.br';
$initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $userName), 0, 2) ?: 'AD');

$healthRows = [
    ['API Core', 'Operational', '32ms', 'success'],
    ['Auth Service', 'Operational', '18ms', 'success'],
    ['Orders Engine', 'Operational', '41ms', 'success'],
    ['QR Ordering', 'Operational', '27ms', 'success'],
    ['Kitchen Sync', 'Operational', '54ms', 'success'],
    ['Payment Webhooks', 'Delayed', '182ms', 'warning'],
    ['WhatsApp Notifications', 'Degraded', 'Retry queue detected', 'danger'],
    ['Database Cluster', 'Healthy', 'Replication stable', 'success'],
    ['Redis Cache', 'Operational', '2ms', 'success'],
    ['Queue Workers', 'Operational', '12 workers active', 'success'],
    ['Storage Service', 'Operational', '0 failed uploads', 'success'],
];

$controls = [
    ['QR Ordering', 'ON', 'success'],
    ['Delivery Flow', 'ON', 'success'],
    ['Kitchen Auto Sync', 'ON', 'success'],
    ['WhatsApp Integration', 'OFF', 'muted'],
    ['Real-Time Notifications', 'ON', 'success'],
    ['Maintenance Mode', 'OFF', 'muted'],
    ['Payment Acceptance', 'ON', 'success'],
];

$queueMetrics = [
    ['Pending Jobs', '182', ''],
    ['Failed Jobs', '3', 'danger'],
    ['Avg Processing Time', '421ms', ''],
    ['Retry Queue', '12', 'warning'],
    ['Dead Letter Queue', '0', ''],
    ['Orders/min', '142', ''],
    ['Events processed today', '284k', ''],
];

$securityMetrics = [
    ['MFA Coverage', '82%', ''],
    ['Active Sessions', '284', ''],
    ['Suspicious Access', '0', 'success'],
    ['Token Expiration', '24h', ''],
    ['Admin Sessions', '6', 'warning'],
];

$governanceMetrics = [
    ['Multi-tenant Isolation', 'ACTIVE', 'success'],
    ['Audit Logs', 'ENABLED', 'success'],
    ['RBAC Enforcement', 'ACTIVE', 'success'],
    ['Rate Limiting', 'ACTIVE', 'success'],
    ['Abuse Detection', 'MONITORING', 'warning'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Admin Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin-delicacy.css">
</head>
<body class="platform-admin ad-shell-page" data-theme="dark" data-default-theme="dark">
    <aside class="ad-sidebar">
        <a class="ad-brand" href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php">
            <span class="ad-logo-box"><img src="<?php echo BASE_URL; ?>/images/auth/delicacy-symbol-cropped.png" alt=""></span>
            <span class="ad-brand-name">Delicacy</span>
        </a>
        <nav class="ad-nav" aria-label="Administração Delicacy">
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M12 3v9h9"/><path d="M21 12a9 9 0 1 1-9-9"/></svg></span><span>Overview</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M4 10l2-6h12l2 6"/><path d="M9 20v-6h6v6"/></svg></span><span>Restaurantes</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/financial.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-8"/><path d="M22 20H2"/></svg></span><span>Financeiro</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/users.php">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2"/><path d="M16 4a4 4 0 0 1 0 8"/><path d="M21 21v-2a6 6 0 0 0-4-5.65"/></svg></span><span>Usuários</span>
            </a>
            <a class="active" href="<?php echo BASE_URL; ?>/admin-delicacy/settings.php" aria-current="page">
                <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3A1.7 1.7 0 0 0 14 21v.2a2 2 0 0 1-4 0V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1A2 2 0 0 1 4.2 17l.1-.1A1.7 1.7 0 0 0 3 14h-.1a2 2 0 0 1 0-4H3a1.7 1.7 0 0 0 1.3-2.9L4.2 7A2 2 0 0 1 7 4.2l.1.1A1.7 1.7 0 0 0 10 3V3a2 2 0 0 1 4 0v.2a1.7 1.7 0 0 0 2.9 1.1l.1-.1A2 2 0 0 1 19.8 7l-.1.1A1.7 1.7 0 0 0 21 10h.1a2 2 0 0 1 0 4H21a1.7 1.7 0 0 0-1.6 1z"/></svg></span><span>Configurações</span>
            </a>
        </nav>
        <div class="ad-theme">
            <span class="ad-theme-label"><span data-admin-theme-glyph aria-hidden="true">☾</span><span data-admin-theme-label>Dark Mode</span></span>
            <button class="ad-theme-toggle" type="button" data-admin-theme-toggle aria-label="Ativar modo claro" aria-pressed="true"></button>
        </div>
    </aside>

    <header class="ad-topbar">
        <label class="ad-search">
            <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg></span>
            <input type="search" placeholder="Buscar diretivas globais..." aria-label="Buscar diretivas globais">
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

    <main class="ad-main ad-settings">
        <header class="ad-head">
            <div class="ad-title">
                <h1>Configurações</h1>
                <p>Configurações globais, operacionais e sistêmicas</p>
            </div>
        </header>

        <section class="ad-metrics ad-settings-metrics" aria-label="Status geral da plataforma">
            <article class="ad-card">
                <p class="ad-card-label">Status Geral</p>
                <span class="ad-icon-box ad-setting-icon success"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg></span>
                <p class="ad-card-value success">Operacional</p>
            </article>
            <article class="ad-card">
                <p class="ad-card-label">Serviços Ativos</p>
                <span class="ad-icon-box ad-setting-icon"><svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="14" rx="2"/><path d="M8 9h.01M8 13h.01M12 9h4M12 13h4"/></svg></span>
                <p class="ad-card-value">11 / 11</p>
            </article>
            <article class="ad-card">
                <p class="ad-card-label">Queue Health</p>
                <span class="ad-icon-box ad-setting-icon warning"><svg viewBox="0 0 24 24"><path d="M12 4 21 20H3z"/><path d="M12 10v4M12 17h.01"/></svg></span>
                <p class="ad-card-value warning">Atenção</p>
            </article>
            <article class="ad-card">
                <p class="ad-card-label">Alertas Ativos</p>
                <span class="ad-icon-box ad-setting-icon danger"><svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M14 21a2 2 0 0 1-4 0"/></svg></span>
                <p class="ad-card-value danger">2</p>
            </article>
        </section>

        <div class="ad-settings-stack">
            <section class="ad-console">
                <h2 class="ad-console-head">Platform Health</h2>
                <div class="ad-console-body ad-table-scroll">
                    <table class="ad-monitor-table">
                        <thead>
                            <tr><th>Serviço / Componente</th><th>Status</th><th>Métrica / Resposta</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($healthRows as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row[0]); ?></td>
                                    <td class="<?php echo htmlspecialchars($row[3]); ?>"><span class="ad-dot">●</span><?php echo htmlspecialchars($row[1]); ?></td>
                                    <td class="<?php echo htmlspecialchars($row[3] === 'success' ? '' : $row[3]); ?>"><?php echo htmlspecialchars($row[2]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="ad-console-grid">
                <section class="ad-console">
                    <h2 class="ad-console-head">Operational Controls</h2>
                    <div class="ad-console-body ad-kv-list">
                        <?php foreach ($controls as $control): ?>
                            <div><span><?php echo htmlspecialchars($control[0]); ?></span><strong class="ad-chip <?php echo htmlspecialchars($control[2]); ?>"><?php echo htmlspecialchars($control[1]); ?></strong></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="ad-button-grid">
                        <button class="ad-control-btn danger" type="button">Pause New Orders</button>
                        <button class="ad-control-btn warning" type="button">Disable QR Ordering</button>
                        <button class="ad-control-btn" type="button">Enter Maintenance</button>
                        <button class="ad-control-btn danger" type="button">Restart Notification Bus</button>
                    </div>
                </section>

                <section class="ad-console">
                    <h2 class="ad-console-head">Queue &amp; Event Monitoring</h2>
                    <div class="ad-console-body ad-kv-list">
                        <?php foreach ($queueMetrics as $metric): ?>
                            <div><span><?php echo htmlspecialchars($metric[0]); ?></span><strong class="<?php echo htmlspecialchars($metric[2]); ?>"><?php echo htmlspecialchars($metric[1]); ?></strong></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="ad-console-footer success"><span>Queue Status:</span><strong>OPERATIONAL</strong></div>
                </section>

                <section class="ad-console">
                    <h2 class="ad-console-head">Security &amp; Access</h2>
                    <div class="ad-console-body ad-kv-list">
                        <?php foreach ($securityMetrics as $metric): ?>
                            <div><span><?php echo htmlspecialchars($metric[0]); ?></span><strong class="<?php echo htmlspecialchars($metric[2]); ?>"><?php echo htmlspecialchars($metric[1]); ?></strong></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="ad-action-list">
                        <button class="ad-control-btn danger" type="button">Force Session Reset</button>
                        <button class="ad-control-btn danger" type="button">Revoke All Tokens</button>
                        <button class="ad-control-btn" type="button">Restrict Admin Access</button>
                    </div>
                </section>

                <section class="ad-console">
                    <h2 class="ad-console-head">Platform Governance</h2>
                    <div class="ad-console-body ad-kv-list">
                        <?php foreach ($governanceMetrics as $metric): ?>
                            <div><span><?php echo htmlspecialchars($metric[0]); ?></span><strong class="<?php echo htmlspecialchars($metric[2]); ?>"><?php echo htmlspecialchars($metric[1]); ?></strong></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="ad-action-list">
                        <button class="ad-control-btn" type="button">Rotate API Keys</button>
                        <button class="ad-control-btn danger" type="button">Enable Emergency Lockdown</button>
                        <button class="ad-control-btn" type="button">Export Audit Snapshot</button>
                    </div>
                </section>
            </div>

            <section class="ad-console ad-emergency">
                <h2 class="ad-console-head"><span class="ad-shield ad-icon"><svg viewBox="0 0 24 24"><path d="M12 3 19 6v5c0 5-3 8-7 10-4-2-7-5-7-10V6z"/><path d="M12 8v6M12 17h.01"/></svg></span> Emergency Response Layer</h2>
                <div class="ad-emergency-body">
                    <button class="ad-emergency-btn" type="button">Global Maintenance Mode</button>
                    <div>
                        <strong>Immediately pauses:</strong>
                        <ul><li>new orders</li><li>QR sessions</li><li>webhook processing</li><li>real-time notifications</li></ul>
                    </div>
                    <div class="preserves">
                        <strong>Preserves:</strong>
                        <ul><li>active kitchen orders</li><li>audit logs</li><li>payment reconciliation</li></ul>
                    </div>
                </div>
                <div class="ad-recovery">Recovery Strategy: <strong>Automated Failover Enabled</strong></div>
            </section>

            <section class="ad-insights">
                <h2>Live Operational Insights</h2>
                <span>Coming Soon</span>
            </section>
        </div>
    </main>
    <script src="<?php echo BASE_URL; ?>/js/admin-delicacy.js"></script>
</body>
</html>
