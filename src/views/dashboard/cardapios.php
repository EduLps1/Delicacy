<?php
/**
 * DELICACY - Lista de Cardapios (Admin Contratante)
 */
$message = getSessionMessage();
$hasRestaurant = !empty($restaurant['id']);
$createMenuUrl = BASE_URL . '/admin_contratante/cardapios/novo';
$createMenuLabel = 'Novo Cardapio';
$panelTitle = 'Cardapios';
$panelActive = 'cardapios';
require VIEWS_PATH . '/admin-contratante/_panel-start.php';
?>

<div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Gestao de Cardapios</h1>
        <p class="text-xs text-textSec mt-1">Crie, personalize, publique e teste seus cardapios com QR Code publico.</p>
        <?php if ($hasRestaurant): ?>
            <p class="text-[11px] text-textSec mt-2">
                Cardapios criados: <strong class="text-textMain"><?php echo (int)($menusCount ?? 0); ?></strong> de <strong class="text-textMain"><?php echo (int)$menuLimit; ?></strong> permitidos pelo plano.
            </p>
        <?php endif; ?>
    </div>

    <?php if (!$hasRestaurant || !$canCreateDraft): ?>
        <button type="button" class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-bgCard text-textSec border border-borderCard cursor-not-allowed">
            Novo Cardapio
        </button>
    <?php else: ?>
        <a href="<?php echo htmlspecialchars($createMenuUrl); ?>" class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-brandRed text-white text-center">
            <?php echo htmlspecialchars($createMenuLabel); ?>
        </a>
    <?php endif; ?>
</div>

<?php if ($message): ?>
    <div class="rounded-xl border <?php echo ($message['type'] ?? '') === 'error' ? 'border-red-300 bg-red-50 text-red-700' : 'border-borderCard bg-bgCard'; ?> px-4 py-3 text-xs">
        <?php echo htmlspecialchars($message['text']); ?>
    </div>
<?php endif; ?>

<?php if (!$hasRestaurant): ?>
    <div class="bg-bgCard rounded-2xl border border-borderCard p-12 flex flex-col items-center text-center">
        <div class="w-12 h-12 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center text-xl"><i class="fa-solid fa-store"></i></div>
        <h2 class="text-base font-bold mt-4">Ambiente de teste em preparacao</h2>
        <p class="text-xs text-textSec mt-2">Sua conta de teste deve receber um restaurante tecnico automaticamente. Recarregue a pagina ou faca login novamente.</p>
        <a href="<?php echo BASE_URL; ?>/admin-contratante/cardapios.php" class="mt-6 bg-brandRed text-white text-xs font-semibold rounded-xl px-5 py-3">Recarregar ambiente</a>
    </div>
<?php elseif (empty($menus)): ?>
    <div class="bg-bgCard rounded-2xl border border-borderCard p-12 flex flex-col items-center text-center">
        <div class="w-12 h-12 rounded-xl bg-brandRed/10 text-brandRed flex items-center justify-center text-xl"><i class="fa-solid fa-kitchen-set"></i></div>
        <h2 class="text-base font-bold mt-4">Nenhum cardapio cadastrado</h2>
        <p class="text-xs text-textSec mt-2">Crie seu primeiro rascunho para abrir o Editor Visual.</p>
        <a href="<?php echo htmlspecialchars($createMenuUrl); ?>" class="mt-6 bg-brandRed text-white text-xs font-semibold rounded-xl px-5 py-3">Novo Cardapio</a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($menus as $menu): ?>
            <?php
                $publicId = $menu['public_id'] ?? '';
                $isPublished = (int)($menu['is_published'] ?? 0) === 1;
                $publicSlug = $menu['public_url'] ?: ($menu['slug'] ?? '');
                $publicUrl = BASE_URL . '/' . rawurlencode($publicSlug);
                $editorUrl = $publicId ? BASE_URL . '/admin_contratante/cardapios/' . rawurlencode($publicId) . '/editar' : BASE_URL . '/admin-contratante/cardapio-editar.php?id=' . (int)$menu['id'];
                $cover = $menu['cover_image_url']
                    ? BASE_URL . $menu['cover_image_url']
                    : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&w=900&q=80';
            ?>
            <article class="bg-bgCard border border-borderCard rounded-2xl overflow-hidden flex flex-col shadow-sm">
                <form method="POST" enctype="multipart/form-data" class="flex flex-col flex-1">
                    <input type="hidden" name="action" value="quick_update">
                    <input type="hidden" name="public_id" value="<?php echo htmlspecialchars($publicId); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="relative h-40 bg-bgMain">
                        <img src="<?php echo htmlspecialchars($cover); ?>" alt="" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/45 via-transparent to-black/40"></div>
                        <span class="absolute top-3 left-3 text-[10px] font-mono font-black px-3 py-1.5 rounded-full shadow-lg <?php echo $isPublished ? 'text-white bg-emerald-500 border border-emerald-300' : 'text-white bg-amber-500 border border-amber-300'; ?>">
                            <?php echo $isPublished ? 'Publicado' : 'Rascunho'; ?>
                        </span>
                        <span class="absolute top-3 right-3 text-[10px] font-mono font-black px-3 py-1.5 rounded-full bg-black/75 text-white border border-white/25 shadow-lg">
                            ID <?php echo htmlspecialchars($publicId ?: (string)$menu['id']); ?>
                        </span>
                        <label class="absolute left-1/2 bottom-3 -translate-x-1/2 h-10 px-4 rounded-full bg-white text-black shadow-lg border border-black/10 inline-flex items-center gap-2 text-[11px] font-bold cursor-pointer hover:bg-brandRed hover:text-white transition-colors">
                            <i class="fa-solid fa-camera"></i>
                            Enviar capa
                            <input type="file" name="cover_image" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="this.form.submit()">
                        </label>
                    </div>

                    <div class="p-5 space-y-3">
                        <label class="block">
                            <span class="text-[10px] uppercase tracking-widest text-textSec">Nome do cardapio</span>
                            <input name="name" value="<?php echo htmlspecialchars($menu['name']); ?>" class="mt-1 w-full rounded-xl bg-bgMain border border-borderCard px-3 py-2 text-sm outline-none focus:border-brandRed">
                        </label>
                        <div class="flex items-center justify-between text-[11px] text-textSec">
                            <span><i class="fa-solid fa-utensils mr-1"></i><?php echo (int)($menu['items_count'] ?? 0); ?> itens legados</span>
                            <?php if ($isPublished): ?><a target="_blank" href="<?php echo htmlspecialchars($publicUrl); ?>" class="text-brandRed font-semibold hover:underline">Testar link publico</a><?php endif; ?>
                        </div>
                        <button type="submit" class="w-full rounded-xl border border-borderCard py-2 text-xs font-semibold hover:border-brandRed hover:text-brandRed">Atualizar dados</button>
                    </div>
                </form>

                <div class="px-5 pb-5 mt-auto grid grid-cols-4 gap-2 text-[10px]">
                    <a href="<?php echo htmlspecialchars($editorUrl); ?>" class="rounded-xl bg-brandRed text-white py-2 text-center font-semibold grid place-items-center gap-1" title="Editar no Editor Visual">
                        <i class="fa-solid fa-pen-to-square text-sm"></i><span>Editar</span>
                    </a>
                    <button type="button" class="rounded-xl border border-borderCard py-2 hover:text-brandRed grid place-items-center gap-1" title="QR Code" onclick="openQrModal('<?php echo htmlspecialchars($publicUrl, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($menu['name'], ENT_QUOTES); ?>')">
                        <i class="fa-solid fa-qrcode text-sm"></i><span>QR Code</span>
                    </button>
                    <form method="POST" id="publishForm-<?php echo (int)$menu['id']; ?>">
                        <input type="hidden" name="action" value="toggle_publish">
                        <input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <button type="button" class="w-full rounded-xl border border-borderCard py-2 hover:text-brandRed grid place-items-center gap-1" title="<?php echo $isPublished ? 'Despublicar' : 'Publicar'; ?>" onclick="openPublishModal('publishForm-<?php echo (int)$menu['id']; ?>', '<?php echo $isPublished ? 'ocultar' : 'publicar'; ?>', '<?php echo htmlspecialchars($menu['name'], ENT_QUOTES); ?>')">
                            <i class="fa-solid <?php echo $isPublished ? 'fa-eye-slash' : 'fa-upload'; ?> text-sm"></i><span><?php echo $isPublished ? 'Ocultar' : 'Publicar'; ?></span>
                        </button>
                    </form>
                    <button type="button" class="rounded-xl border border-borderCard py-2 hover:text-brandRed grid place-items-center gap-1" title="Excluir" onclick="openDeleteModal('<?php echo htmlspecialchars($publicId, ENT_QUOTES); ?>', '<?php echo $isPublished ? '1' : '0'; ?>', '<?php echo htmlspecialchars($menu['name'], ENT_QUOTES); ?>')">
                        <i class="fa-solid fa-trash text-sm"></i><span>Excluir</span>
                    </button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div id="qrModal" class="hidden fixed inset-0 z-50 bg-black/70 p-4 items-center justify-center">
    <div class="w-full max-w-md rounded-2xl bg-bgCard border border-borderCard p-6 text-center">
        <button type="button" class="float-right text-textSec hover:text-brandRed" onclick="closeModal('qrModal')"><i class="fa-solid fa-xmark"></i></button>
        <h3 id="qrTitle" class="text-lg font-bold">QR Code</h3>
        <div id="qrBox" class="mt-5 mx-auto w-56 h-56 rounded-2xl bg-white p-4 grid place-items-center"></div>
        <input id="qrLink" readonly class="mt-5 w-full rounded-xl bg-bgMain border border-borderCard px-3 py-2 text-xs text-center">
        <p class="mt-3 text-[11px] text-textSec">Use este link para testes publicos e impressao de QR Code.</p>
    </div>
</div>

<div id="publishModal" class="hidden fixed inset-0 z-50 bg-black/70 p-4 items-center justify-center">
    <div class="w-full max-w-md rounded-2xl bg-bgCard border border-borderCard p-6">
        <button type="button" class="float-right text-textSec hover:text-brandRed" onclick="closeModal('publishModal')"><i class="fa-solid fa-xmark"></i></button>
        <h3 id="publishTitle" class="text-lg font-bold">Confirmar acao</h3>
        <p id="publishCopy" class="mt-3 text-xs text-textSec leading-relaxed"></p>
        <div class="mt-6 grid grid-cols-2 gap-3">
            <button type="button" class="rounded-xl border border-borderCard py-3 text-xs font-bold hover:border-brandRed" onclick="closeModal('publishModal')">Cancelar</button>
            <button type="button" id="publishConfirmBtn" class="rounded-xl bg-brandRed text-white py-3 text-xs font-bold">Confirmar</button>
        </div>
    </div>
</div>

<div id="deleteModal" class="hidden fixed inset-0 z-50 bg-black/70 p-4 items-center justify-center">
    <form method="POST" id="deleteMenuForm" class="w-full max-w-md rounded-2xl bg-bgCard border border-borderCard p-6" onsubmit="return validateDeleteForm()">
        <input type="hidden" name="action" value="secure_delete">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <input type="hidden" name="public_id" id="deletePublicId">
        <input type="hidden" id="deletePublished">
        <button type="button" class="float-right text-textSec hover:text-brandRed" onclick="closeModal('deleteModal')"><i class="fa-solid fa-xmark"></i></button>
        <h3 class="text-lg font-bold">Exclusao segura</h3>
        <p id="deleteCopy" class="mt-2 text-xs text-textSec"></p>
        <div id="deleteWarning" class="hidden mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700"></div>
        <label id="passwordWrap" class="hidden block mt-4">
            <span class="text-[10px] uppercase tracking-widest text-textSec">Senha do usuario</span>
            <input name="password" id="deletePassword" type="password" class="mt-1 w-full rounded-xl bg-bgMain border border-borderCard px-3 py-2 text-sm outline-none focus:border-brandRed">
        </label>
        <label class="block mt-4">
            <span class="text-[10px] uppercase tracking-widest text-textSec">Digite CONFIRMAR</span>
            <input name="confirmation" id="deleteConfirmation" autocomplete="off" class="mt-1 w-full rounded-xl bg-bgMain border border-borderCard px-3 py-2 text-sm outline-none focus:border-brandRed">
        </label>
        <button type="submit" class="mt-5 w-full rounded-xl bg-brandRed text-white py-3 text-xs font-bold">Excluir definitivamente</button>
    </form>
</div>

<script>
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}

function openQrModal(url, title) {
    var modal = document.getElementById('qrModal');
    document.getElementById('qrTitle').textContent = 'QR Code - ' + title;
    document.getElementById('qrLink').value = url;
    document.getElementById('qrBox').innerHTML = '<img class="w-full h-full" alt="QR Code" src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(url) + '">';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openPublishModal(formId, action, title) {
    var modal = document.getElementById('publishModal');
    var confirmBtn = document.getElementById('publishConfirmBtn');
    var isPublish = action === 'publicar';
    document.getElementById('publishTitle').textContent = isPublish ? 'Publicar cardapio?' : 'Ocultar cardapio?';
    document.getElementById('publishCopy').textContent = isPublish
        ? 'Ao publicar "' + title + '", o link publico e o QR Code ficarao disponiveis para testes e acesso dos clientes.'
        : 'Ao ocultar "' + title + '", o link publico deixa de exibir este cardapio ate que ele seja publicado novamente.';
    confirmBtn.textContent = isPublish ? 'Sim, publicar' : 'Sim, ocultar';
    confirmBtn.onclick = function () {
        document.getElementById(formId).submit();
    };
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openDeleteModal(publicId, published, title) {
    var modal = document.getElementById('deleteModal');
    var needsPassword = published === '1';
    document.getElementById('deletePublicId').value = publicId;
    document.getElementById('deletePublished').value = published;
    document.getElementById('deleteConfirmation').value = '';
    document.getElementById('deletePassword').value = '';
    document.getElementById('deleteWarning').classList.add('hidden');
    document.getElementById('passwordWrap').classList.toggle('hidden', !needsPassword);
    document.getElementById('deleteCopy').textContent = needsPassword
        ? 'O cardapio "' + title + '" esta publicado. Informe sua senha e digite CONFIRMAR exatamente em caixa alta.'
        : 'O rascunho "' + title + '" sera removido. Digite CONFIRMAR exatamente em caixa alta para continuar.';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function showDeleteWarning(message) {
    var warning = document.getElementById('deleteWarning');
    warning.textContent = message;
    warning.classList.remove('hidden');
}

function validateDeleteForm() {
    var published = document.getElementById('deletePublished').value === '1';
    var confirmation = document.getElementById('deleteConfirmation').value;
    var password = document.getElementById('deletePassword').value;

    if (confirmation !== 'CONFIRMAR') {
        showDeleteWarning('Digite CONFIRMAR exatamente em caixa alta para liberar a exclusao.');
        return false;
    }

    if (published && password.trim() === '') {
        showDeleteWarning('Informe sua senha. Se ela estiver errada, o sistema avisara apos validar.');
        return false;
    }

    return true;
}
</script>

<?php require VIEWS_PATH . '/admin-contratante/_panel-end.php'; ?>
