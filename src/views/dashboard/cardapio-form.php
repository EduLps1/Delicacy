<?php
/**
 * DELICACY - Formulario Criar Cardapio
 * Variaveis: $restaurant, $menu (null=novo), $csrf_token
 */
$message = getSessionMessage();
$isEdit = !empty($menu);
$panelTitle = $isEdit ? 'Editar Cardápio' : 'Novo Cardápio';
$panelActive = 'cardapios';
require VIEWS_PATH . '/admin-contratante/_panel-start.php';
?>
<div class="flex items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight"><?php echo $isEdit ? 'Editar' : 'Novo'; ?> Cardápio</h1>
        <p class="text-xs text-textSec mt-1">Defina o canal e as informações iniciais do cardápio.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="text-xs text-textSec hover:text-brandRed">Voltar para cardápios</a>
</div>
<?php if ($message): ?>
    <div class="rounded-xl border border-borderCard bg-bgCard px-4 py-3 text-xs"><?php echo htmlspecialchars($message['text']); ?></div>
<?php endif; ?>
<form method="POST" action="<?php echo BASE_URL; ?>/admin-contratante/cardapio-novo.php" class="bg-bgCard rounded-2xl p-6 border border-borderCard max-w-3xl space-y-5">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <label class="flex flex-col gap-2 text-xs font-semibold">
        Nome do cardápio
        <input type="text" name="name" required placeholder="Ex: Cardápio Almoço" value="<?php echo htmlspecialchars($menu['name'] ?? ''); ?>" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed">
    </label>
    <label class="flex flex-col gap-2 text-xs font-semibold">
        Descrição
        <textarea name="description" placeholder="Descreva o cardápio..." class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed h-24 resize-none"><?php echo htmlspecialchars($menu['description'] ?? ''); ?></textarea>
    </label>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label class="flex flex-col gap-2 text-xs font-semibold">
            Tipo de atendimento
            <select name="type" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed">
                <option value="both" <?php echo ($menu['type'] ?? '') === 'both' ? 'selected' : ''; ?>>Online + Presencial</option>
                <option value="online" <?php echo ($menu['type'] ?? '') === 'online' ? 'selected' : ''; ?>>Apenas Delivery</option>
                <option value="presencial" <?php echo ($menu['type'] ?? '') === 'presencial' ? 'selected' : ''; ?>>Apenas Presencial</option>
            </select>
        </label>
        <label class="flex flex-col gap-2 text-xs font-semibold">
            Número da unidade
            <input type="number" name="unit_number" min="1" value="<?php echo (int)($menu['unit_number'] ?? 1); ?>" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed">
        </label>
    </div>
    <div class="flex justify-end gap-3 pt-2">
        <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapio.php" class="px-4 py-3 rounded-xl text-xs font-semibold border border-borderCard">Cancelar</a>
        <button type="submit" class="px-4 py-3 rounded-xl text-xs font-semibold bg-brandRed text-white">Criar cardápio</button>
    </div>
</form>
<?php require VIEWS_PATH . '/admin-contratante/_panel-end.php'; ?>
