<?php
/**
 * DELICACY - Editar Cardapio e Gerenciar Pratos
 * Variaveis: $menu, $items, $restaurant, $csrf_token
 */
$message = getSessionMessage();
$panelTitle = 'Editar ' . ($menu['name'] ?? 'Cardápio');
$panelActive = 'cardapios';
require VIEWS_PATH . '/admin-contratante/_panel-start.php';
?>
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight"><?php echo htmlspecialchars($menu['name']); ?></h1>
        <p class="text-xs text-textSec mt-1">Edite os dados do cardápio e gerencie seus pratos.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="text-xs text-textSec hover:text-brandRed">Voltar para cardápios</a>
</div>
<?php if ($message): ?>
    <div class="rounded-xl border border-borderCard bg-bgCard px-4 py-3 text-xs"><?php echo htmlspecialchars($message['text']); ?></div>
<?php endif; ?>
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <form method="POST" class="bg-bgCard rounded-2xl p-6 border border-borderCard space-y-4">
        <h2 class="text-sm font-bold border-b border-borderCard pb-3">Dados do Cardápio</h2>
        <input type="hidden" name="action" value="update_menu">
        <input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <label class="flex flex-col gap-2 text-xs font-semibold">Nome
            <input type="text" name="name" required value="<?php echo htmlspecialchars($menu['name']); ?>" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed">
        </label>
        <label class="flex flex-col gap-2 text-xs font-semibold">Descrição
            <textarea name="description" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 h-20 resize-none outline-none focus:border-brandRed"><?php echo htmlspecialchars($menu['description'] ?? ''); ?></textarea>
        </label>
        <div class="grid grid-cols-2 gap-4">
            <label class="flex flex-col gap-2 text-xs font-semibold">Canal
                <select name="type" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none">
                    <option value="both" <?php echo $menu['type'] === 'both' ? 'selected' : ''; ?>>Online + Presencial</option>
                    <option value="online" <?php echo $menu['type'] === 'online' ? 'selected' : ''; ?>>Delivery</option>
                    <option value="presencial" <?php echo $menu['type'] === 'presencial' ? 'selected' : ''; ?>>Presencial</option>
                </select>
            </label>
            <label class="flex flex-col gap-2 text-xs font-semibold">Unidade
                <input type="number" name="unit_number" min="1" value="<?php echo (int)$menu['unit_number']; ?>" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none">
            </label>
        </div>
        <button type="submit" class="rounded-xl bg-brandRed text-white text-xs font-semibold px-4 py-3">Salvar cardápio</button>
    </form>

    <form method="POST" enctype="multipart/form-data" class="bg-bgCard rounded-2xl p-6 border border-borderCard space-y-4">
        <h2 class="text-sm font-bold border-b border-borderCard pb-3">Adicionar Prato</h2>
        <input type="hidden" name="action" value="add_item">
        <input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="flex flex-col gap-2 text-xs font-semibold">Nome do prato
                <input type="text" name="item_name" required class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed">
            </label>
            <label class="flex flex-col gap-2 text-xs font-semibold">Preço (R$)
                <input type="number" name="item_price" required step="0.01" min="0" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed">
            </label>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="flex flex-col gap-2 text-xs font-semibold">Categoria
                <select name="item_category" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none">
                    <option value="entrada">Entrada</option>
                    <option value="prato_principal" selected>Prato Principal</option>
                    <option value="bebida">Bebida</option>
                    <option value="sobremesa">Sobremesa</option>
                    <option value="acompanhamento">Acompanhamento</option>
                    <option value="outros">Outros</option>
                </select>
            </label>
            <label class="flex flex-col gap-2 text-xs font-semibold">Imagem
                <input type="file" name="item_image" accept="image/*" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-2 outline-none">
            </label>
        </div>
        <label class="flex flex-col gap-2 text-xs font-semibold">Descrição
            <textarea name="item_description" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 h-16 resize-none outline-none"></textarea>
        </label>
        <button type="submit" class="rounded-xl bg-brandRed text-white text-xs font-semibold px-4 py-3">Adicionar prato</button>
    </form>
</div>

<section class="bg-bgCard rounded-2xl p-6 border border-borderCard space-y-4">
    <h2 class="text-sm font-bold">Pratos cadastrados (<?php echo count($items); ?>)</h2>
    <?php if (empty($items)): ?>
        <p class="border border-dashed border-borderCard rounded-xl p-8 text-xs text-textSec text-center">Nenhum prato adicionado ainda.</p>
    <?php else: ?>
        <div class="divide-y divide-borderCard">
            <?php foreach ($items as $item): ?>
                <article class="flex flex-col sm:flex-row sm:items-center gap-4 py-4 <?php echo $item['is_active'] ? '' : 'opacity-60'; ?>">
                    <div class="w-12 h-12 rounded-xl bg-bgMain border border-borderCard overflow-hidden flex items-center justify-center text-textSec shrink-0">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?php echo BASE_URL . htmlspecialchars($item['image_url']); ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fa-solid fa-utensils"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xs font-bold"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="text-[11px] text-textSec truncate"><?php echo htmlspecialchars($item['description'] ?? 'Sem descrição'); ?></p>
                    </div>
                    <span class="text-xs font-bold"><?php echo formatCurrency($item['price']); ?></span>
                    <span class="rounded-full px-2 py-1 text-[10px] <?php echo $item['is_active'] ? 'bg-emerald-500/10 text-emerald-500' : 'bg-brandRed/10 text-brandRed'; ?>"><?php echo $item['is_active'] ? 'Ativo' : 'Inativo'; ?></span>
                    <div class="flex gap-2">
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle_item"><input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>"><input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <button type="submit" class="w-9 h-9 rounded-lg border border-borderCard text-textSec hover:text-brandRed" aria-label="Alterar status"><i class="fa-solid fa-power-off"></i></button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Excluir este prato?');">
                            <input type="hidden" name="action" value="delete_item"><input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>"><input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <button type="submit" class="w-9 h-9 rounded-lg border border-borderCard text-textSec hover:text-brandRed" aria-label="Excluir prato"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require VIEWS_PATH . '/admin-contratante/_panel-end.php'; ?>
