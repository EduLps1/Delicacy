<?php
/**
 * DELICACY - Lista de Cardapios (Admin)
 * Variaveis: $menus, $restaurant, $csrf_token
 */
$message = getSessionMessage();
$panelTitle = 'Cardápios';
$panelActive = 'cardapios';
require VIEWS_PATH . '/admin-contratante/_panel-start.php';
?>
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Gestão de Cardápios</h1>
        <p class="text-xs text-textSec mt-1">Crie, edite pratos e publique cardápios por canal de venda.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio-novo.php" class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-brandRed text-white text-center">
        Criar novo cardápio
    </a>
</div>

<?php if ($message): ?>
    <div class="rounded-xl border border-borderCard bg-bgCard px-4 py-3 text-xs"><?php echo htmlspecialchars($message['text']); ?></div>
<?php endif; ?>

<?php if (empty($menus)): ?>
    <div class="bg-bgCard rounded-2xl border border-borderCard p-12 flex flex-col items-center text-center">
        <div class="w-12 h-12 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center text-xl"><i class="fa-solid fa-kitchen-set"></i></div>
        <h2 class="text-base font-bold mt-4">Nenhum cardápio cadastrado</h2>
        <p class="text-xs text-textSec mt-2">Crie o primeiro cardápio para começar a receber pedidos.</p>
        <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio-novo.php" class="mt-6 bg-brandRed text-white text-xs font-semibold rounded-xl px-5 py-3">Criar cardápio</a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($menus as $menu): ?>
            <article class="bg-bgCard border border-borderCard rounded-2xl overflow-hidden flex flex-col justify-between shadow-sm">
                <div class="p-6 space-y-3">
                    <span class="text-[10px] font-mono font-bold px-2 py-1 rounded-full <?php echo $menu['is_published'] ? 'text-emerald-500 bg-emerald-500/10' : 'text-textSec bg-bgMain border border-borderCard'; ?>">
                        <?php echo $menu['is_published'] ? 'Ativo' : 'Rascunho'; ?>
                    </span>
                    <h2 class="text-base font-bold pt-2"><?php echo htmlspecialchars($menu['name']); ?></h2>
                    <p class="text-xs text-textSec min-h-[36px]"><?php echo htmlspecialchars($menu['description'] ?? 'Sem descrição cadastrada.'); ?></p>
                    <div class="flex flex-wrap gap-3 pt-2 text-[11px] text-textSec">
                        <span><i class="fa-solid fa-utensils mr-1"></i><?php echo (int)($menu['items_count'] ?? 0); ?> pratos</span>
                        <span><i class="fa-solid fa-location-dot mr-1"></i>Unidade <?php echo (int)$menu['unit_number']; ?></span>
                    </div>
                    <?php if ($menu['is_published'] && !empty($menu['slug'])): ?>
                        <a class="block truncate text-[11px] text-brandRed hover:underline" target="_blank" href="<?php echo BASE_URL; ?>/menu.php?slug=<?php echo rawurlencode($menu['slug']); ?>">
                            Ver cardápio publicado
                        </a>
                    <?php endif; ?>
                </div>
                <div class="p-4 bg-bgMain/30 border-t border-borderCard flex flex-wrap items-center gap-3 text-xs">
                    <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio-editar.php?id=<?php echo (int)$menu['id']; ?>" class="font-medium hover:text-brandRed">Editar pratos</a>
                    <form method="POST" onsubmit="return confirm('<?php echo $menu['is_published'] ? 'Deseja despublicar este cardápio?' : 'Deseja publicar este cardápio?'; ?>');">
                        <input type="hidden" name="action" value="toggle_publish">
                        <input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <button type="submit" class="font-medium text-brandRed"><?php echo $menu['is_published'] ? 'Despublicar' : 'Publicar'; ?></button>
                    </form>
                    <form method="POST" class="ml-auto" onsubmit="return confirm('Tem certeza que deseja excluir este cardápio?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <button type="submit" class="text-textSec hover:text-brandRed" aria-label="Excluir cardápio"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require VIEWS_PATH . '/admin-contratante/_panel-end.php'; ?>
