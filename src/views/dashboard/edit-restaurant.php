<?php
/**
 * DELICACY - Edicao de Restaurante
 * Variaveis: $restaurant, $csrf_token
 */
$message = getSessionMessage();
$panelTitle = 'Configurações';
$panelActive = 'config';
require VIEWS_PATH . '/admin-contratante/_panel-start.php';
?>
<div>
    <h1 class="text-2xl font-bold tracking-tight">Configurações Operacionais</h1>
    <p class="text-xs text-textSec mt-1">Dados cadastrais da loja e informações apresentadas ao cliente.</p>
</div>

<?php if ($message): ?>
    <div class="rounded-xl border border-borderCard bg-bgCard px-4 py-3 text-xs"><?php echo htmlspecialchars($message['text']); ?></div>
<?php endif; ?>

<form method="POST" action="<?php echo BASE_URL; ?>/admin-contratante/editar-restaurante.php" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <section class="bg-bgCard rounded-2xl p-6 border border-borderCard space-y-5">
        <h2 class="text-sm font-bold border-b border-borderCard pb-3">Dados do Restaurante</h2>
        <label class="flex flex-col gap-2 text-xs font-semibold">
            Nome comercial
            <input type="text" name="name" required value="<?php echo htmlspecialchars($restaurant['name'] ?? ''); ?>" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed">
        </label>
        <label class="flex flex-col gap-2 text-xs font-semibold">
            Descrição pública da casa
            <textarea name="description" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed h-28 resize-none"><?php echo htmlspecialchars($restaurant['description'] ?? ''); ?></textarea>
        </label>
    </section>
    <section class="bg-bgCard rounded-2xl p-6 border border-borderCard space-y-5">
        <h2 class="text-sm font-bold border-b border-borderCard pb-3">Contato</h2>
        <label class="flex flex-col gap-2 text-xs font-semibold">
            Telefone
            <input type="text" name="phone" value="<?php echo htmlspecialchars($restaurant['phone'] ?? ''); ?>" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed">
        </label>
        <label class="flex flex-col gap-2 text-xs font-semibold">
            E-mail do restaurante
            <input type="email" name="email" value="<?php echo htmlspecialchars($restaurant['email'] ?? ''); ?>" class="bg-bgMain font-normal border border-borderCard rounded-xl px-3 py-3 outline-none focus:border-brandRed">
        </label>
        <div class="pt-4 flex justify-end gap-3">
            <a href="<?php echo BASE_URL; ?>/admin-contratante/" class="px-4 py-3 rounded-xl text-xs font-semibold border border-borderCard hover:border-brandRed">Cancelar</a>
            <button type="submit" class="px-4 py-3 rounded-xl text-xs font-semibold bg-brandRed text-white">Salvar alterações</button>
        </div>
    </section>
</form>
<?php require VIEWS_PATH . '/admin-contratante/_panel-end.php'; ?>
