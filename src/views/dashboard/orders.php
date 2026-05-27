<?php
/**
 * DELICACY - Pedidos do Restaurante (Admin)
 * Variaveis: $orders, $restaurant, $stats, $totalOrders, $totalPages, $page, $filters, $csrf_token
 */
$message = getSessionMessage();
$currentStatus = $_GET['status'] ?? '';
$currentDateFrom = $_GET['date_from'] ?? '';
$currentDateTo = $_GET['date_to'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$statusLabels = [
    'pending' => ['label' => 'Novo', 'color' => 'text-brandRed bg-brandRed/10'],
    'confirmed' => ['label' => 'Confirmado', 'color' => 'text-blue-500 bg-blue-500/10'],
    'preparing' => ['label' => 'Em preparo', 'color' => 'text-amber-500 bg-amber-500/10'],
    'ready' => ['label' => 'Pronto', 'color' => 'text-emerald-500 bg-emerald-500/10'],
    'delivered' => ['label' => 'Entregue', 'color' => 'text-emerald-500 bg-emerald-500/10'],
    'cancelled' => ['label' => 'Cancelado', 'color' => 'text-textSec bg-bgMain'],
    'paid' => ['label' => 'Pago', 'color' => 'text-emerald-500 bg-emerald-500/10']
];
$columns = [
    'new' => ['title' => 'Novos pedidos', 'color' => 'text-brandRed', 'statuses' => ['pending', 'confirmed']],
    'preparing' => ['title' => 'Em preparo', 'color' => 'text-amber-500', 'statuses' => ['preparing']],
    'done' => ['title' => 'Finalizados', 'color' => 'text-emerald-500', 'statuses' => ['ready', 'delivered', 'paid']],
    'cancelled' => ['title' => 'Cancelados', 'color' => 'text-textSec', 'statuses' => ['cancelled']]
];
$ordersByColumn = array_fill_keys(array_keys($columns), []);
foreach ($orders as $order) {
    foreach ($columns as $columnKey => $column) {
        if (in_array($order['status'], $column['statuses'], true)) {
            $ordersByColumn[$columnKey][] = $order;
            break;
        }
    }
}
$panelTitle = 'Pedidos';
$panelActive = 'pedidos';
require VIEWS_PATH . '/admin-contratante/_panel-start.php';
?>
<div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Painel de Pedidos</h1>
        <p class="text-xs text-textSec mt-1">Gerencie a esteira operacional da cozinha e atualize pedidos em tempo real.</p>
    </div>
    <form method="GET" class="flex flex-wrap items-end gap-2 text-xs">
        <label class="flex flex-col gap-1 text-textSec">Status
            <select name="status" class="bg-bgCard text-textMain border border-borderCard rounded-lg px-3 py-2 outline-none">
                <option value="">Todos</option>
                <?php foreach ($statusLabels as $key => $status): ?>
                    <option value="<?php echo $key; ?>" <?php echo $currentStatus === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($status['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="flex flex-col gap-1 text-textSec">De
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($currentDateFrom); ?>" class="bg-bgCard text-textMain border border-borderCard rounded-lg px-3 py-2 outline-none">
        </label>
        <label class="flex flex-col gap-1 text-textSec">Até
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($currentDateTo); ?>" class="bg-bgCard text-textMain border border-borderCard rounded-lg px-3 py-2 outline-none">
        </label>
        <button type="submit" class="rounded-lg bg-brandRed text-white px-4 py-2.5 font-semibold">Filtrar</button>
    </form>
</div>

<?php if ($message): ?>
    <div class="rounded-xl border border-borderCard bg-bgCard px-4 py-3 text-xs"><?php echo htmlspecialchars($message['text']); ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <article class="bg-bgCard rounded-2xl p-5 border border-borderCard"><span class="text-xs text-textSec">Pendentes</span><p class="mt-2 text-2xl font-bold text-brandRed"><?php echo (int)$stats['pending']; ?></p></article>
    <article class="bg-bgCard rounded-2xl p-5 border border-borderCard"><span class="text-xs text-textSec">Pedidos hoje</span><p class="mt-2 text-2xl font-bold"><?php echo (int)$stats['today_orders']; ?></p></article>
    <article class="bg-bgCard rounded-2xl p-5 border border-borderCard"><span class="text-xs text-textSec">Receita hoje</span><p class="mt-2 text-2xl font-bold"><?php echo formatCurrency($stats['today_revenue']); ?></p></article>
    <article class="bg-bgCard rounded-2xl p-5 border border-borderCard"><span class="text-xs text-textSec">Total de pedidos</span><p class="mt-2 text-2xl font-bold"><?php echo (int)$stats['total_orders']; ?></p></article>
</div>

<?php if (empty($orders)): ?>
    <div class="bg-bgCard rounded-2xl border border-borderCard px-6 py-16 text-center">
        <i class="fa-solid fa-bell-concierge text-2xl text-brandRed"></i>
        <h2 class="text-sm font-bold mt-4">Nenhum pedido encontrado</h2>
        <p class="text-xs text-textSec mt-2">Novos pedidos aparecerão aqui quando forem enviados pelos clientes.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <?php foreach ($columns as $columnKey => $column): ?>
            <section class="bg-bgCard/40 border border-borderCard rounded-2xl p-4 min-h-[360px] flex flex-col gap-3">
                <h2 class="text-xs font-bold <?php echo $column['color']; ?> uppercase tracking-widest border-b border-borderCard pb-3">
                    <?php echo htmlspecialchars($column['title']); ?> (<?php echo count($ordersByColumn[$columnKey]); ?>)
                </h2>
                <?php foreach ($ordersByColumn[$columnKey] as $order): ?>
                    <?php $status = $statusLabels[$order['status']] ?? ['label' => $order['status'], 'color' => 'text-textSec bg-bgMain']; ?>
                    <article class="bg-bgCard border border-borderCard p-4 rounded-xl space-y-3 shadow-sm">
                        <div class="flex justify-between items-center text-xs">
                            <strong>#<?php echo (int)$order['id']; ?></strong>
                            <span class="px-2 py-0.5 rounded-full <?php echo $status['color']; ?>"><?php echo htmlspecialchars($status['label']); ?></span>
                        </div>
                        <p class="text-xs font-medium"><?php echo htmlspecialchars($order['customer_name'] ?? 'Cliente'); ?></p>
                        <p class="text-[11px] text-textSec"><?php echo htmlspecialchars($order['menu_name'] ?? 'Cardápio'); ?> · <?php echo formatCurrency($order['total_value']); ?></p>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <select name="new_status" onchange="this.form.submit()" class="w-full bg-bgMain text-textSec text-[11px] border border-borderCard rounded-lg px-2 py-2 outline-none">
                                <option value="">Atualizar status...</option>
                                <option value="confirmed">Confirmar</option>
                                <option value="preparing">Em preparo</option>
                                <option value="ready">Pronto</option>
                                <option value="delivered">Entregue</option>
                                <option value="cancelled">Cancelar</option>
                            </select>
                        </form>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
    <nav class="flex justify-center gap-2 text-xs" aria-label="Paginação">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php $params = $_GET; $params['page'] = $i; ?>
            <a class="w-9 h-9 rounded-lg border border-borderCard inline-flex items-center justify-center <?php echo $i === $page ? 'bg-brandRed text-white' : 'bg-bgCard'; ?>" href="?<?php echo htmlspecialchars(http_build_query($params)); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
<?php require VIEWS_PATH . '/admin-contratante/_panel-end.php'; ?>
