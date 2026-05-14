<?php
/**
 * DELICACY - Pedidos do Restaurante (Admin)
 * VariÃ¡veis: $orders, $restaurant, $stats, $totalOrders, $totalPages, $page, $filters, $csrf_token
 */
$message = getSessionMessage();
$currentStatus = $_GET['status'] ?? '';
$currentDateFrom = $_GET['date_from'] ?? '';
$currentDateTo = $_GET['date_to'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));

$statusLabels = [
    'pending' => ['label' => 'Pendente', 'class' => 'badge-warning', 'icon' => 'â³'],
    'confirmed' => ['label' => 'Confirmado', 'class' => 'badge-primary', 'icon' => 'âœ…'],
    'preparing' => ['label' => 'Preparando', 'class' => 'badge-primary', 'icon' => 'ðŸ‘¨â€ðŸ³'],
    'ready' => ['label' => 'Pronto', 'class' => 'badge-success', 'icon' => 'ðŸ””'],
    'delivered' => ['label' => 'Entregue', 'class' => 'badge-success', 'icon' => 'ðŸ“¦'],
    'cancelled' => ['label' => 'Cancelado', 'class' => 'badge-danger', 'icon' => 'âŒ'],
    'paid' => ['label' => 'Pago', 'class' => 'badge-success', 'icon' => 'ðŸ’°'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Delicacy</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="header-logo">
                <a href="<?php echo BASE_URL; ?>" class="logo"><h1>ðŸ½ï¸ Delicacy</h1></a>
            </div>
            <nav class="header-nav">
                <span class="user-info">OlÃ¡, <strong><?php echo htmlspecialchars(getAuthUser()['name']); ?></strong></span>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn-logout">Sair</a>
            </nav>
        </div>
    </header>

    <div class="layout-with-sidebar">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <h3 class="nav-title">Restaurante</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/" class="nav-link">ðŸ“Š Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="nav-link">ðŸ“‹ CardÃ¡pio</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin-contratante/pedidos.php" class="nav-link active">ðŸ“¦ Pedidos</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>ðŸ“¦ Pedidos</h1>
                    <p>Gerencie os pedidos do seu restaurante</p>
                </div>

                <?php if ($message): ?>
                    <div class="message-container">
                        <div class="message message-<?php echo htmlspecialchars($message['type']); ?>">
                            <span><?php echo htmlspecialchars($message['text']); ?></span>
                            <button onclick="this.parentElement.parentElement.style.display='none';">Ã—</button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">â³</div>
                        <div class="stat-value"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label">Pendentes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">ðŸ“Š</div>
                        <div class="stat-value"><?php echo $stats['today_orders']; ?></div>
                        <div class="stat-label">Pedidos Hoje</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">ðŸ’°</div>
                        <div class="stat-value"><?php echo formatCurrency($stats['today_revenue']); ?></div>
                        <div class="stat-label">Receita Hoje</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">ðŸ“ˆ</div>
                        <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                        <div class="stat-label">Total Pedidos</div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card">
                    <form method="GET" class="filters-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="">Todos</option>
                                    <?php foreach ($statusLabels as $key => $info): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $currentStatus === $key ? 'selected' : ''; ?>>
                                            <?php echo $info['icon'] . ' ' . $info['label']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date_from">De</label>
                                <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($currentDateFrom); ?>">
                            </div>
                            <div class="form-group">
                                <label for="date_to">AtÃ©</label>
                                <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($currentDateTo); ?>">
                            </div>
                            <div class="form-group" style="display:flex;align-items:flex-end;">
                                <button type="submit" class="btn btn-primary">ðŸ” Filtrar</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabela de pedidos -->
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">ðŸ“¦</div>
                        <h2>Nenhum pedido encontrado</h2>
                        <p>Quando seus clientes fizerem pedidos, eles aparecerÃ£o aqui.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>CardÃ¡pio</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>AÃ§Ãµes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <?php $status = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'class' => 'badge-primary', 'icon' => 'ðŸ“‹']; ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['customer_name'] ?? 'AnÃ´nimo'); ?>
                                        <?php if (!empty($order['customer_phone'])): ?>
                                            <br><small>ðŸ“± <?php echo htmlspecialchars($order['customer_phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
                                    <td><strong><?php echo formatCurrency($order['total_value']); ?></strong></td>
                                    <td>
                                        <span class="badge <?php echo $status['class']; ?>">
                                            <?php echo $status['icon'] . ' ' . $status['label']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDateTime($order['created_at']); ?></td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                            <select name="new_status" onchange="this.form.submit()">
                                                <option value="">Alterar...</option>
                                                <option value="confirmed">âœ… Confirmar</option>
                                                <option value="preparing">ðŸ‘¨â€ðŸ³ Preparando</option>
                                                <option value="ready">ðŸ”” Pronto</option>
                                                <option value="delivered">ðŸ“¦ Entregue</option>
                                                <option value="cancelled">âŒ Cancelar</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- PaginaÃ§Ã£o -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php 
                                $queryParams = $_GET;
                                $queryParams['page'] = $i;
                                $url = '?' . http_build_query($queryParams);
                                ?>
                                <a href="<?php echo $url; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Delicacy. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
