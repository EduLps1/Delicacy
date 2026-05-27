<?php
/**
 * DELICACY - Lista administrativa de restaurantes
 * Variaveis: $restaurants, $restaurantStats, $search, $status, $pages, $page
 */

$user = getAuthUser() ?: ['name' => 'Admin Delicacy'];
$userName = $user['name'] ?? 'Admin Delicacy';
$userEmail = $user['email'] ?? 'admin@delicacy.com.br';
$initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $userName), 0, 2) ?: 'AD');
$restaurantStats = $restaurantStats ?? ['total' => 0, 'active' => 0, 'pending' => 0, 'inactive' => 0];
$message = getSessionMessage();
$activeStatus = $status ?? '';
$filterUrl = function ($targetStatus) use ($search) {
    $query = [];
    if ($targetStatus !== '') {
        $query['status'] = $targetStatus;
    }
    if (($search ?? '') !== '') {
        $query['search'] = $search;
    }
    return BASE_URL . '/admin-delicacy/restaurants.php' . ($query ? '?' . http_build_query($query) : '');
};
$statusCards = [
    ['key' => '', 'label' => 'Total Cadastrados', 'value' => $restaurantStats['total'], 'class' => '', 'icon' => '<path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M4 10l2-6h12l2 6"/>'],
    ['key' => 'active', 'label' => 'Ativos', 'value' => $restaurantStats['active'], 'class' => 'success', 'icon' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/>'],
    ['key' => 'pending', 'label' => 'Pendentes', 'value' => $restaurantStats['pending'], 'class' => 'warning', 'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
    ['key' => 'inactive', 'label' => 'Inativos', 'value' => $restaurantStats['inactive'], 'class' => 'danger', 'icon' => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/>'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurantes - Admin Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin-delicacy.css">
</head>
<body class="platform-admin ad-shell-page ad-restaurants-page" data-theme="dark" data-default-theme="dark">
    <aside class="ad-sidebar">
        <a class="ad-brand" href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php">
            <span class="ad-logo-box"><img src="<?php echo BASE_URL; ?>/images/auth/delicacy-symbol-cropped.png" alt=""></span>
            <span class="ad-brand-name">Delicacy</span>
        </a>
        <nav class="ad-nav" aria-label="Administracao Delicacy">
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/dashboard.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M12 3v9h9"/><path d="M21 12a9 9 0 1 1-9-9"/></svg></span><span>Overview</span></a>
            <a class="active" aria-current="page" href="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M4 10l2-6h12l2 6"/><path d="M9 20v-6h6v6"/></svg></span><span>Restaurantes</span></a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/financial.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-8"/><path d="M22 20H2"/></svg></span><span>Financeiro</span></a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/users.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2"/><path d="M16 4a4 4 0 0 1 0 8"/><path d="M21 21v-2a6 6 0 0 0-4-5.65"/></svg></span><span>Usu&aacute;rios</span></a>
            <a href="<?php echo BASE_URL; ?>/admin-delicacy/settings.php"><span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3A1.7 1.7 0 0 0 14 21v.2a2 2 0 0 1-4 0V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1A2 2 0 0 1 4.2 17l.1-.1A1.7 1.7 0 0 0 3 14h-.1a2 2 0 0 1 0-4H3a1.7 1.7 0 0 0 1.3-2.9L4.2 7A2 2 0 0 1 7 4.2l.1.1A1.7 1.7 0 0 0 10 3V3a2 2 0 0 1 4 0v.2a1.7 1.7 0 0 0 2.9 1.1l.1-.1A2 2 0 0 1 19.8 7l-.1.1A1.7 1.7 0 0 0 21 10h.1a2 2 0 0 1 0 4H21a1.7 1.7 0 0 0-1.6 1z"/></svg></span><span>Configura&ccedil;&otilde;es</span></a>
        </nav>
        <div class="ad-theme">
            <span class="ad-theme-label"><span data-admin-theme-glyph aria-hidden="true">&#9790;</span><span data-admin-theme-label>Dark Mode</span></span>
            <button class="ad-theme-toggle" type="button" data-admin-theme-toggle aria-label="Ativar modo claro" aria-pressed="true"></button>
        </div>
    </aside>

    <header class="ad-topbar">
        <form class="ad-search" method="GET" action="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php">
            <?php if ($activeStatus !== ''): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($activeStatus); ?>"><?php endif; ?>
            <span class="ad-icon"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg></span>
            <input type="search" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="Buscar restaurante..." aria-label="Buscar restaurante">
        </form>
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

    <main class="ad-main ad-restaurants">
        <header class="ad-head">
            <div class="ad-title">
                <h1>Restaurantes</h1>
                <p>Controle de status, plano, cidade, movimenta&ccedil;&atilde;o e sa&uacute;de financeira.</p>
            </div>
        </header>

        <?php if ($message): ?>
            <p class="ad-page-message"><?php echo htmlspecialchars($message['text']); ?></p>
        <?php endif; ?>

        <section class="ad-metrics ad-restaurants-metrics" aria-label="Totais de restaurantes">
            <?php foreach ($statusCards as $card): ?>
                <a class="ad-card <?php echo $activeStatus === $card['key'] ? 'selected' : ''; ?>" href="<?php echo htmlspecialchars($filterUrl($card['key'])); ?>" aria-current="<?php echo $activeStatus === $card['key'] ? 'true' : 'false'; ?>">
                    <p class="ad-card-label"><?php echo htmlspecialchars($card['label']); ?></p>
                    <span class="ad-icon-box <?php echo htmlspecialchars($card['class']); ?>"><svg viewBox="0 0 24 24"><?php echo $card['icon']; ?></svg></span>
                    <p class="ad-card-value"><?php echo number_format((int)$card['value'], 0, ',', '.'); ?></p>
                </a>
            <?php endforeach; ?>
        </section>

        <section class="ad-table-wrap">
            <div class="ad-table-scroll">
                <table class="ad-table">
                    <thead><tr><th>Restaurante</th><th>Cidade</th><th>Plano</th><th>Status</th><th>&Uacute;ltima movimenta&ccedil;&atilde;o</th><th class="ad-table-action"></th></tr></thead>
                    <tbody>
                        <?php if (empty($restaurants)): ?>
                            <tr><td class="ad-empty" colspan="6"><?php echo $activeStatus === 'pending' ? 'Nenhum restaurante pendente. O status estara disponivel quando implementado.' : 'Nenhum restaurante encontrado.'; ?></td></tr>
                        <?php else: ?>
                            <?php foreach ($restaurants as $restaurant): ?>
                                <?php $dashboardUrl = BASE_URL . '/admin-delicacy/restaurant-dashboard.php?id=' . (int)$restaurant['id']; ?>
                                <tr>
                                    <td>
                                        <div class="ad-restaurant">
                                            <span class="ad-store"><svg viewBox="0 0 24 24"><path d="M4 10h16"/><path d="M5 10v10h14V10"/><path d="M4 10l2-6h12l2 6"/><path d="M9 20v-6h6v6"/></svg></span>
                                            <span><a class="ad-restaurant-link" href="<?php echo htmlspecialchars($dashboardUrl); ?>"><?php echo htmlspecialchars($restaurant['name']); ?></a><small><?php echo htmlspecialchars($restaurant['owner_name'] ?? 'Sem responsavel'); ?></small></span>
                                        </div>
                                    </td>
                                    <td>N&atilde;o informado</td>
                                    <td><span class="ad-pill"><?php echo htmlspecialchars(ucfirst($restaurant['plan_type'] ?? 'basic')); ?></span></td>
                                    <td><span class="ad-status <?php echo (int)$restaurant['is_active'] === 1 ? '' : 'inactive'; ?>"><?php echo (int)$restaurant['is_active'] === 1 ? 'Ativo' : 'Inativo'; ?></span></td>
                                    <td class="ad-move"><?php echo htmlspecialchars(date('d/m/Y', strtotime($restaurant['updated_at'] ?? $restaurant['created_at']))); ?><small><?php echo htmlspecialchars(date('H:i', strtotime($restaurant['updated_at'] ?? $restaurant['created_at']))); ?></small></td>
                                    <td><a class="ad-row-open" href="<?php echo htmlspecialchars($dashboardUrl); ?>" aria-label="Abrir dashboard de <?php echo htmlspecialchars($restaurant['name']); ?>"><svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="ad-table-foot">
                <?php if (($pages ?? 0) > ($page ?? 1)): ?>
                    <?php $moreQuery = http_build_query(array_filter(['page' => (int)$page + 1, 'status' => $activeStatus, 'search' => $search ?? ''], function ($value) { return $value !== ''; })); ?>
                    <a class="ad-action" href="<?php echo BASE_URL; ?>/admin-delicacy/restaurants.php?<?php echo htmlspecialchars($moreQuery); ?>">+ Carregar mais restaurantes</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script src="<?php echo BASE_URL; ?>/js/admin-delicacy.js"></script>
</body>
</html>
