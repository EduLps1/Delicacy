<?php
/**
 * DELICACY - Editor Visual de Cardápio
 *
 * Variáveis: $menu, $restaurant, $editorData, $csrf_token, $publicUrl
 */
$editorJson = json_encode($editorData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Visual - <?php echo htmlspecialchars($menu['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --wine:#561d2a; --wine-dark:#2b0d15; --gold:#c79a54; --ink:#171114; --muted:#74696a; --line:#ece7e1; --paper:#fff; --soft:#f7f4ef; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; background:#fff; color:var(--ink); font-family:Inter,system-ui,sans-serif; }
        button,input,textarea,select { font:inherit; }
        button { cursor:pointer; }
        .editor-shell { min-height:100vh; display:grid; grid-template-rows:auto auto 1fr auto; background:#fff; padding-bottom:86px; }
        .topbar { display:grid; grid-template-columns:1fr auto 1fr; gap:18px; align-items:center; padding:14px 28px; border-bottom:1px solid var(--line); background:#fff; position:sticky; top:0; z-index:20; }
        .establishment-bar { display:grid; grid-template-columns:1fr auto 1fr; gap:18px; align-items:center; padding:18px 28px; border-bottom:1px solid var(--line); background:#fff; position:sticky; top:65px; z-index:19; }
        .brand-fields,.address-fields,.utility { display:flex; align-items:center; gap:12px; }
        .address-fields { justify-content:flex-end; }
        .address-fields .field input { min-width:260px; }
        .logo-preview { width:56px; height:56px; border-radius:50%; background:var(--wine); color:#fff; display:grid; place-items:center; overflow:hidden; font-weight:800; flex:0 0 56px; }
        .logo-preview img { width:100%; height:100%; object-fit:cover; }
        .field { display:grid; gap:5px; }
        .field span { font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:.12em; font-weight:800; }
        .field input,.field textarea,.field select { border:1px solid var(--line); border-radius:14px; padding:11px 12px; outline:0; min-width:210px; background:#fff; }
        .field input:focus,.field textarea:focus,.field select:focus { border-color:var(--gold); }
        .upload-pill { min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px; border:1px solid var(--line); border-radius:14px; padding:0 14px; background:#fff; color:var(--wine); font-size:12px; font-weight:800; cursor:pointer; }
        .upload-pill input { display:none; }
        .status-badge { padding:10px 16px; border-radius:999px; color:#047857; background:#ecfdf5; border:1px solid #bbf7d0; font-size:12px; font-weight:800; }
        .pin-button,.icon-button { width:42px; height:42px; border-radius:14px; border:1px solid var(--line); background:#fff; color:var(--wine); }
        .utility { justify-content:flex-end; }
        .size-control { justify-content:center; }
        .size-control input { min-width:220px; accent-color:var(--wine); }
        .publish-button,.primary-button { border:0; border-radius:999px; background:var(--wine); color:#fff; padding:13px 18px; font-size:12px; font-weight:800; }
        .banner { margin:20px 0 24px; width:100%; min-height:68px; border-radius:26px; background:var(--wine-dark); color:#fff; padding:14px 16px; display:flex; align-items:center; justify-content:center; gap:12px; transition:.25s ease; }
        .banner.is-transparent { background:transparent !important; padding-left:0; padding-right:0; }
        .banner.is-transparent .category-pill { background:var(--wine-dark); border-color:var(--wine-dark); box-shadow:0 14px 26px rgba(43,13,21,.12); }
        .banner.is-transparent .icon-button { color:var(--wine); box-shadow:0 10px 22px rgba(43,13,21,.08); }
        .category-strip { display:flex; gap:10px; align-items:center; justify-content:center; flex:1; flex-wrap:wrap; }
        .category-pill { border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.08); color:#fff; border-radius:999px; padding:11px 18px; font-size:12px; font-weight:900; letter-spacing:.08em; }
        .banner-actions { display:flex; gap:8px; }
        .workspace { width:min(1180px, calc(100% - 56px)); margin:22px auto 24px; display:block; }
        .preview { min-height:620px; border-radius:0; background:transparent; padding:26px 0; box-shadow:none; transition:.2s ease; position:relative; isolation:isolate; }
        .preview::before { content:""; position:fixed; inset:0; z-index:0; background-image:var(--menu-bg-image, none); background-size:cover; background-position:center; background-repeat:no-repeat; pointer-events:none; }
        .preview > * { position:relative; z-index:1; }
        .preview-header { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; margin-bottom:0; }
        .preview-title h1 { margin:0; color:var(--wine-dark); font-size:34px; letter-spacing:-.04em; }
        .preview-title p { margin:6px 0 0; color:var(--muted); font-size:13px; }
        .category-section { padding:12px 0 18px; }
        .category-page-title { margin:10px 0 22px; text-align:center; color:var(--wine-dark); font-size:30px; letter-spacing:-.04em; }
        .category-description-row { margin:18px 0 12px; display:flex; align-items:center; justify-content:center; gap:8px; }
        .category-description-row h3 { margin:0; color:var(--muted); font-size:14px; font-weight:800; letter-spacing:.04em; }
        .category-pill.active { outline:2px solid rgba(255,255,255,.65); outline-offset:3px; }
        .title-row { margin:26px 0 10px; display:flex; align-items:center; gap:8px; }
        .title-row h2 { margin:0; font-size:22px; color:var(--wine-dark); }
        .subtitle-row { margin:18px 0 12px; display:flex; align-items:center; gap:8px; }
        .subtitle-row h3 { margin:0; color:var(--muted); font-size:14px; text-transform:uppercase; letter-spacing:.08em; }
        .product-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .product-card { position:relative; min-height:134px; display:grid; grid-template-columns:110px 1fr; gap:14px; padding:12px; border:1px solid transparent; border-radius:22px; background:#fff; box-shadow:0 18px 38px rgba(43,13,21,.08); }
        .product-card.is-selected,.category-pill.is-selected,.title-row.is-selected,.subtitle-row.is-selected { outline:2px solid rgba(86,29,42,.22); outline-offset:4px; }
        .product-card img,.product-empty { width:110px; height:110px; border-radius:16px; object-fit:cover; background:linear-gradient(135deg,#f3e7dc,#fff); }
        .product-card h4 { margin:0; font-size:15px; }
        .product-card p { margin:7px 0; color:var(--muted); font-size:12px; line-height:1.5; }
        .price { color:var(--wine); font-size:12px; font-weight:900; }
        .edit-product { position:absolute; inset:0; margin:auto; width:44px; height:44px; border:0; border-radius:50%; background:rgba(43,13,21,.88); color:#fff; opacity:0; transition:.2s; }
        .product-card:hover .edit-product { opacity:1; }
        .delete-product { position:absolute; right:10px; top:10px; width:32px; height:32px; border:0; border-radius:50%; background:#fff; color:#ef4444; box-shadow:0 10px 20px rgba(43,13,21,.12); opacity:0; transition:.2s; }
        .product-card:hover .delete-product { opacity:1; }
        .add-product-card { min-height:134px; border:1px dashed rgba(86,29,42,.28); border-radius:22px; background:rgba(255,255,255,.72); color:var(--wine); display:grid; place-items:center; gap:8px; font-weight:900; box-shadow:0 18px 38px rgba(43,13,21,.04); }
        .add-product-card i { width:42px; height:42px; border-radius:16px; display:grid; place-items:center; background:rgba(86,29,42,.08); }
        .side-panel { position:sticky; top:134px; display:grid; gap:14px; }
        .side-card { border:1px solid var(--line); border-radius:24px; padding:18px; background:#fff; box-shadow:0 20px 48px rgba(43,13,21,.06); }
        .side-card h3 { margin:0 0 12px; font-size:14px; }
        .mini-list { display:grid; gap:8px; }
        .mini-list button { border:1px solid var(--line); border-radius:14px; background:#fff; padding:10px; text-align:left; font-size:12px; }
        .bottom-nav { position:fixed; left:0; right:0; bottom:0; z-index:30; height:68px; display:grid; grid-template-columns:repeat(3,1fr); gap:0; padding:7px 0 5px; border-top:1px solid var(--line); background:rgba(255,255,255,.96); box-shadow:0 -8px 30px rgba(43,13,21,.08); backdrop-filter:blur(14px); }
        .bottom-nav button { border:0; border-radius:0; padding:5px 8px; background:transparent; color:var(--editor-accent, var(--wine)); font-size:11px; font-weight:700; display:grid; place-items:center; gap:2px; }
        .bottom-nav button i { font-size:25px; line-height:1; }
        .bottom-nav button.active { color:var(--editor-accent, var(--wine)); }
        .route-panel { display:none; width:min(1180px, calc(100% - 56px)); margin:22px auto 24px; }
        .route-panel.active { display:block; }
        .route-card { min-height:620px; border:0; border-radius:0; background:#fff; padding:26px 0; box-shadow:none; display:grid; align-content:start; justify-items:center; text-align:center; }
        .route-card h2 { margin:0; color:var(--wine-dark); font-size:32px; letter-spacing:-.04em; }
        .route-card p { max-width:560px; margin:10px auto 0; color:var(--muted); font-size:14px; line-height:1.6; }
        .promo-card-grid { width:min(900px,100%); margin-top:28px; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
        .promo-open-card { width:100%; min-height:150px; border:1px solid var(--line); border-radius:28px; background:#fff; color:var(--wine); display:grid; place-items:center; gap:8px; box-shadow:0 22px 48px rgba(43,13,21,.08); font-weight:900; }
        .promo-open-card i { width:48px; height:48px; border-radius:18px; display:grid; place-items:center; background:rgba(86,29,42,.08); font-size:20px; }
        .promo-summary-card { min-height:150px; border:1px solid var(--line); border-radius:28px; background:#fff; overflow:hidden; text-align:left; box-shadow:0 22px 48px rgba(43,13,21,.08); }
        .promo-summary-card img,.promo-summary-image { width:100%; height:110px; object-fit:cover; background:linear-gradient(135deg,#f3e7dc,#fff); display:block; }
        .promo-summary-body { padding:14px; }
        .promo-summary-body strong { display:block; color:var(--wine-dark); }
        .promo-summary-body p { margin:6px 0 0; color:var(--muted); font-size:12px; }
        .promo-image-drop { height:280px; min-height:0; border:1px dashed rgba(86,29,42,.28); border-radius:24px; background:#f7f4ef; color:var(--wine); display:grid; place-items:center; overflow:hidden; cursor:pointer; text-align:center; margin-bottom:12px; }
        .promo-image-drop img { width:100%; height:100%; object-fit:cover; }
        .promo-image-drop input { display:none; }
        .promo-head { justify-content:center; position:relative; }
        .promo-head .icon-button { position:absolute; right:18px; top:50%; transform:translateY(-50%); }
        .promo-step { display:none; padding:24px; }
        .promo-step.active { display:block; }
        .promo-picker { max-width:720px; margin:0 auto; display:grid; gap:18px; }
        .search-field { position:relative; }
        .search-field i { position:absolute; left:14px; bottom:13px; color:var(--muted); }
        .search-field input { width:100%; padding-left:40px; }
        .promo-item-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .promo-item-card { position:relative; border:1px solid var(--line); border-radius:18px; background:#fff; padding:15px 46px 15px 15px; text-align:left; box-shadow:0 12px 24px rgba(43,13,21,.05); }
        .promo-item-card strong { display:block; font-size:13px; color:var(--ink); }
        .promo-item-card small { display:block; margin-top:4px; color:var(--muted); }
        .promo-item-card .price { display:block; margin-top:8px; }
        .select-dot { position:absolute; right:13px; top:13px; width:26px; height:26px; border-radius:50%; border:1px solid var(--line); background:#fff; color:#fff; display:grid; place-items:center; }
        .promo-item-card.selected .select-dot { border-color:var(--wine); background:var(--wine); }
        .promo-create-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:22px; }
        .promo-create-grid > div { min-width:0; }
        .promo-selected-panel { display:grid; gap:10px; margin-bottom:16px; }
        .promo-selected-item { display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; border:1px solid var(--line); border-radius:18px; padding:12px; background:#fff; box-shadow:0 12px 24px rgba(43,13,21,.05); }
        .promo-selected-item strong { display:block; color:var(--ink); font-size:13px; }
        .promo-selected-item small { display:block; color:var(--muted); margin-top:3px; }
        .promo-selected-item .qty { display:flex; align-items:center; gap:8px; }
        .promo-selected-item .qty button { width:30px; height:30px; border:1px solid var(--line); border-radius:50%; background:#fff; color:var(--wine); }
        .promo-price-tags { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:16px; }
        .promo-price-tag { border:1px solid var(--line); border-radius:18px; padding:14px; text-align:left; background:#fff; }
        .promo-price-tag span { display:block; color:var(--muted); font-size:10px; text-transform:uppercase; letter-spacing:.12em; font-weight:800; }
        .promo-price-tag strong { display:block; margin-top:5px; color:var(--wine); font-size:18px; }
        .product-head { justify-content:center; position:relative; }
        .product-head .icon-button { position:absolute; right:18px; top:50%; transform:translateY(-50%); }
        .product-editor-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:22px; padding:22px; overflow:hidden; }
        .product-editor-grid > div { min-width:0; }
        .product-image-drop { height:260px; min-height:0; border:1px dashed rgba(86,29,42,.28); border-radius:24px; background:#f7f4ef; color:var(--wine); display:grid; place-items:center; overflow:hidden; cursor:pointer; text-align:center; margin-bottom:12px; }
        .product-image-drop img { width:100%; height:100%; object-fit:cover; }
        .product-image-drop input { display:none; }
        .price-type-tabs { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; }
        .price-type-tabs button { border:1px solid var(--line); border-radius:16px; background:#fff; color:var(--muted); padding:13px; font-size:12px; font-weight:900; }
        .price-type-tabs button.active { background:var(--wine); border-color:var(--wine); color:#fff; }
        .price-panel { display:none; }
        .price-panel.active { display:grid; gap:12px; }
        .overlay { position:fixed; inset:0; z-index:60; display:none; place-items:center; background:rgba(23,17,20,.56); padding:22px; }
        .overlay.open { display:grid; }
        .modal { width:min(960px,100%); max-height:90vh; overflow:auto; border-radius:28px; background:#fff; box-shadow:0 40px 90px rgba(0,0,0,.25); }
        .modal-head { display:flex; justify-content:space-between; align-items:center; padding:20px 22px; border-bottom:1px solid var(--line); }
        .modal-grid { display:grid; grid-template-columns:1fr 1fr; gap:22px; padding:22px; }
        .modal-actions { display:flex; justify-content:flex-end; padding:0 22px 22px; }
        .pricing-options { display:grid; gap:10px; margin-top:12px; }
        .variation-card { display:grid; grid-template-columns:minmax(0,1fr) minmax(105px,130px); gap:10px; padding:12px; border:1px solid var(--line); border-radius:18px; background:#fff; box-shadow:0 12px 24px rgba(43,13,21,.05); overflow:hidden; }
        .variation-card .field input { min-width:0; width:100%; }
        .variation-card input { border:0; border-radius:12px; background:#f7f4ef; padding:12px; outline:0; }
        .color-picker-row { display:grid; gap:10px; }
        .color-trigger { width:100%; min-height:46px; border:1px solid var(--line); border-radius:16px; background:#fff; display:flex; align-items:center; justify-content:space-between; padding:0 12px; color:var(--ink); font-weight:800; }
        .color-dot { width:22px; height:22px; border-radius:50%; border:2px solid #fff; box-shadow:0 0 0 1px rgba(23,17,20,.16); background:var(--editor-accent, var(--wine)); }
        .hidden-color-input { position:absolute; opacity:0; pointer-events:none; width:1px; height:1px; }
        .toggle-row { display:flex; align-items:center; justify-content:space-between; gap:12px; border:1px solid var(--line); border-radius:18px; padding:14px; }
        .toggle-button { width:52px; height:28px; border:0; border-radius:999px; background:#e7ded6; padding:3px; transition:.2s ease; }
        .toggle-button span { display:block; width:22px; height:22px; border-radius:50%; background:#fff; box-shadow:0 4px 10px rgba(0,0,0,.12); transition:.2s ease; }
        .toggle-button.active { background:var(--editor-accent, var(--wine)); }
        .toggle-button.active span { transform:translateX(24px); }
        .success-modal { text-align:center; padding:34px; }
        .success-modal input { width:100%; text-align:center; margin-top:18px; }
        @media (max-width:980px) { .topbar,.establishment-bar,.workspace,.modal-grid,.promo-create-grid,.promo-item-grid,.product-editor-grid,.promo-card-grid { grid-template-columns:1fr; } .utility,.size-control { justify-content:flex-start; flex-wrap:wrap; } .address-fields { justify-content:flex-start; } .product-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="editor-shell">
    <header class="topbar">
        <div></div>
        <label class="field size-control"><span>Tamanho</span><input id="zoomInput" type="range" min="80" max="120" step="5"></label>
        <div class="utility">
            <a class="upload-pill" href="<?php echo BASE_URL; ?>/admin-contratante/cardapios.php" style="text-decoration:none"><i class="fa-solid fa-arrow-left"></i> Sair do editor</a>
            <button class="publish-button" type="button" onclick="publishMenu()">Publicar Cardapio</button>
        </div>
    </header>

    <section class="establishment-bar">
        <div class="brand-fields">
            <div class="logo-preview" id="logoPreview">D</div>
            <label class="upload-pill"><i class="fa-solid fa-camera"></i> Perfil<input id="logoFile" type="file" accept="image/jpeg,image/png,image/gif,image/webp"></label>
            <input id="logoInput" type="hidden">
            <label class="field"><span>Nome do Restaurante</span><input id="restaurantName"></label>
        </div>
        <div class="status-badge" id="statusBadge">Aberto</div>
        <div class="address-fields">
            <button class="pin-button" type="button" onclick="openMapPicker()"><i class="fa-solid fa-location-dot"></i></button>
            <label class="field"><span>Endereço</span><input id="restaurantAddress" placeholder="Rua, número, bairro"></label>
        </div>
    </section>

    <main class="workspace route-panel active" id="route-cardapio">
        <section class="preview" id="preview"></section>
    </main>
    <section class="route-panel" id="route-promocoes"><div class="route-card"><h2>Promocoes</h2><p><strong>Crie e edite promocoes</strong> usando os produtos cadastrados no cardapio.</p><div class="promo-card-grid" id="promoPageGrid"></div></div></section>
    <section class="route-panel" id="route-fidelidade"><div class="route-card"><h2>Fidelidade</h2><p>Modulo em preparacao para planos com fidelidade habilitada.</p></div></section>
    <nav class="bottom-nav" aria-label="Modulos do editor">
        <button class="active" type="button" data-route="cardapio" onclick="switchEditorRoute('cardapio')"><i class="fa-solid fa-list"></i><span>Cardapio</span></button>
        <button type="button" data-route="promocoes" onclick="switchEditorRoute('promocoes')"><i class="fa-solid fa-tags"></i><span>Promocoes</span></button>
        <button type="button" data-route="fidelidade" onclick="switchEditorRoute('fidelidade')"><i class="fa-solid fa-award"></i><span>Fidelidade</span></button>
    </nav>
</div>

<div class="overlay" id="productOverlay">
    <div class="modal">
        <div class="modal-head product-head"><strong>Editar produto</strong><button class="icon-button" onclick="closeOverlay('productOverlay')"><i class="fa-solid fa-xmark"></i></button></div>
        <div class="product-editor-grid">
            <div>
                <label class="product-image-drop" id="productImageDrop"><span><i class="fa-solid fa-image"></i><br>Enviar imagem do produto</span><input id="productImageFile" type="file" accept="image/jpeg,image/png,image/gif,image/webp"></label>
                <input id="productImage" type="hidden">
                <label class="field"><span>Titulo</span><input id="productTitle"></label>
                <label class="field"><span>Descricao</span><textarea id="productDescription" rows="6"></textarea></label>
            </div>
            <div>
                <input id="productPricing" type="hidden" value="single">
                <div class="price-type-tabs">
                    <button id="priceSingleTab" type="button" onclick="setProductPricing('single')">Preco unico</button>
                    <button id="priceStartingTab" type="button" onclick="setProductPricing('starting_at')">A partir de</button>
                </div>
                <div class="price-panel" id="singlePricePanel">
                    <label class="field"><span>Preco</span><input id="productPrice" type="number" step="0.01"></label>
                </div>
                <div class="price-panel" id="startingPricePanel">
                    <div class="pricing-options" id="variationList"></div>
                    <button class="icon-button" type="button" onclick="addVariation()"><i class="fa-solid fa-plus"></i></button>
                </div>
            </div>
        </div>
        <div class="modal-actions"><button class="primary-button" type="button" onclick="saveProduct()">Pronto</button></div>
    </div>
</div>
<div class="overlay" id="bannerOverlay">
    <div class="modal">
        <div class="modal-head"><strong>Aparencia</strong><button class="icon-button" onclick="closeOverlay('bannerOverlay')"><i class="fa-solid fa-xmark"></i></button></div>
        <div class="modal-grid">
            <div class="field color-picker-row">
                <span>Cor do painel</span>
                <button class="color-trigger" type="button" onclick="bannerColorInput.click()"><span>Selecionar cor</span><span class="color-dot" id="bannerColorDot"></span></button>
                <input class="hidden-color-input" id="bannerColorInput" type="color" value="#2b0d15">
            </div>
            <div class="field">
                <span>Painel transparente</span>
                <div class="toggle-row"><strong id="bannerTransparentLabel">Nao, manter painel</strong><button id="bannerTransparentToggle" class="toggle-button" type="button" onclick="toggleBannerTransparency()"><span></span></button></div>
            </div>
        </div>
        <div class="modal-actions"><button class="primary-button" type="button" onclick="saveBannerSettings()">Aplicar</button></div>
    </div>
</div>

<div class="overlay" id="categoryOverlay">
    <div class="modal">
        <div class="modal-head"><strong>Nova categoria</strong><button class="icon-button" onclick="closeOverlay('categoryOverlay')"><i class="fa-solid fa-xmark"></i></button></div>
        <div style="padding:22px">
            <label class="field"><span>Nome da categoria</span><input id="categoryNameInput" placeholder="Ex: Bebidas, Massas, Sobremesas"></label>
        </div>
        <div class="modal-actions"><button class="primary-button" type="button" onclick="saveCategory()">Criar categoria</button></div>
    </div>
</div>

<div class="overlay" id="promotionOverlay">
    <div class="modal promo-card" id="promoCard">
        <div class="modal-head promo-head"><strong>Promocao</strong><button class="icon-button" onclick="closeOverlay('promotionOverlay')"><i class="fa-solid fa-xmark"></i></button></div>
        <div class="promo-step active" id="promoEditStep">
            <div class="promo-picker">
                <label class="field search-field"><span>Buscar itens</span><i class="fa-solid fa-magnifying-glass"></i><input id="promoSearch" oninput="renderPromoItems()" placeholder="Digite para filtrar"></label>
                <div class="promo-item-grid" id="promoItems"></div>
                <div class="modal-actions" style="padding:0"><button class="primary-button" type="button" onclick="showPromoCreate()">Editar</button></div>
            </div>
        </div>
        <div class="promo-step" id="promoCreateStep">
            <div class="promo-create-grid">
                <div>
                    <label class="promo-image-drop" id="promoImageDrop"><span><i class="fa-solid fa-image"></i><br>Enviar imagem da promocao</span><input id="promoImageFile" type="file" accept="image/jpeg,image/png,image/gif,image/webp"></label>
                    <label class="field"><span>Titulo</span><input id="promoTitle"></label>
                    <label class="field"><span>Descricao</span><textarea id="promoDescription" rows="5"></textarea></label>
                    <label class="field"><span>Tema</span><input id="promoTheme" placeholder="Ex: Festival Italiano"></label>
                </div>
                <div>
                    <div class="promo-selected-panel" id="promoSelectedPanel"></div>
                    <label class="field"><span>Desconto aplicado</span><select id="promoDiscountType"><option value="fixed">Valor fixo</option><option value="percent">Porcentagem</option></select></label>
                    <label class="field"><span id="promoDiscountLabel">Digite o valor</span><input id="promoDiscountValue" type="number" step="0.01"></label>
                    <div class="promo-price-tags">
                        <div class="promo-price-tag"><span>Valor real</span><strong id="promoOriginalValue">R$ 0,00</strong></div>
                        <div class="promo-price-tag"><span>Valor promocional</span><strong id="promoFinalValue">R$ 0,00</strong></div>
                    </div>
                </div>
            </div>
            <div class="modal-actions"><button class="icon-button" type="button" onclick="showPromoEdit()"><i class="fa-solid fa-arrow-left"></i></button><button class="primary-button" type="button" onclick="createPromotion()">Criar</button></div>
        </div>
    </div>
</div>
<div class="overlay" id="successOverlay">
    <div class="modal success-modal">
        <h2>Cardápio publicado</h2>
        <p>URL final estruturada para testes e QR Code:</p>
        <input id="publishedUrl" readonly>
        <button class="primary-button" style="margin-top:18px" onclick="closeOverlay('successOverlay')">Fechar</button>
    </div>
</div>

<div class="overlay" id="mapOverlay">
    <div class="modal">
        <div class="modal-head"><strong>Selecionar localizacao</strong><button class="icon-button" onclick="closeOverlay('mapOverlay')"><i class="fa-solid fa-xmark"></i></button></div>
        <div style="padding:22px;display:grid;gap:14px">
            <label class="field"><span>Endereco</span><input id="mapAddressInput" placeholder="Rua, numero, bairro"></label>
            <div style="border:1px solid var(--line);border-radius:22px;padding:26px;background:#f7f4ef;color:var(--muted);text-align:center">
                <i class="fa-solid fa-map-location-dot" style="font-size:42px;color:var(--wine)"></i>
                <p>Preview preparado para integracao com Google Maps ou Mapbox.</p>
                <a id="mapExternalLink" target="_blank" rel="noopener" class="primary-button" style="display:inline-flex;text-decoration:none;margin-top:10px">Abrir no Maps</a>
            </div>
        </div>
        <div class="modal-actions"><button class="primary-button" type="button" onclick="applyMapAddress()">Usar este endereco</button></div>
    </div>
</div>

<script>
const csrfToken = <?php echo json_encode($csrf_token); ?>;
const editorToken = <?php echo json_encode($editor_token); ?>;
const publicId = <?php echo json_encode($menu['public_id']); ?>;
const apiUrl = <?php echo json_encode(BASE_URL . '/admin_contratante/cardapios/api'); ?>;
let state = <?php echo $editorJson ?: '{}'; ?>;
state.restaurant = state.restaurant || {};
state.theme = state.theme || {};
state.categories = state.categories || [];
state.promotions = state.promotions || [];
let currentProductPath = null;
let selectedPromoIds = [];
let selectedPromoQuantities = {};
let promoImageUrl = '';
let editingPromotionIndex = null;
let selectedCategoryIndex = 0;

function normalizeStarterContent() {
    const firstTitle = state.categories?.[0]?.titles?.[0];
    const firstSubtitle = firstTitle?.subtitles?.[0];
    const firstProduct = firstSubtitle?.products?.[0];
    if (firstTitle?.id === 'pizzas' && firstProduct?.id === 'produto-demo') {
        firstTitle.id = 'titulo-inicial';
        firstTitle.name = state.categories?.[0]?.name || 'GERAL';
        firstSubtitle.id = 'subtitulo-inicial';
        firstSubtitle.name = 'Descricao da categoria';
        firstProduct.title = 'Novo produto';
        firstProduct.description = 'Descricao do produto. Clique no lapis para editar foto, texto e preco.';
        firstProduct.pricing_type = 'single';
        firstProduct.price = 0;
        firstProduct.variations = [];
    }
    if (firstTitle?.name === 'Novo titulo') firstTitle.name = state.categories?.[0]?.name || 'GERAL';
    if (firstSubtitle?.name === 'Novo subtitulo') firstSubtitle.name = 'Descricao da categoria';
}
normalizeStarterContent();

function uid(prefix) { return prefix + '-' + Math.random().toString(36).slice(2, 8); }
function money(value) { return 'R$ ' + Number(value || 0).toFixed(2).replace('.', ','); }
function toPublicUrl(path) {
    if (!path) return '';
    return path.indexOf('http') === 0 ? path : <?php echo json_encode(BASE_URL); ?> + path;
}
async function uploadEditorAsset(file) {
    const form = new FormData();
    form.append('action', 'upload_editor_asset');
    form.append('csrf_token', csrfToken);
    form.append('jwt_token', editorToken);
    form.append('public_id', publicId);
    form.append('asset', file);
    const response = await fetch(apiUrl, { method:'POST', body:form });
    const result = await response.json();
    if (!result.ok) throw new Error(result.error || 'Erro no upload');
    return result.url;
}
function switchEditorRoute(route) {
    document.querySelectorAll('.route-panel').forEach(panel => panel.classList.remove('active'));
    document.getElementById('route-' + route).classList.add('active');
    document.querySelectorAll('.bottom-nav [data-route]').forEach(button => button.classList.toggle('active', button.dataset.route === route));
    window.location.hash = route;
}
function openMapPicker() {
    mapAddressInput.value = restaurantAddress.value;
    updateMapExternalLink();
    openOverlay('mapOverlay');
}
function updateMapExternalLink() {
    mapExternalLink.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(mapAddressInput.value || restaurantAddress.value || '');
}
function applyMapAddress() {
    restaurantAddress.value = mapAddressInput.value;
    closeOverlay('mapOverlay');
    render();
}
function getProducts() {
    const products = [];
    const seen = new Set();
    state.categories.forEach((cat, ci) => (cat.titles || []).forEach((title, ti) => (title.subtitles || []).forEach((sub, si) => (sub.products || []).forEach((product, pi) => {
        if (product.id === 'produto-demo') return;
        if ((product.title || '').trim().toLowerCase() === 'novo produto' && Number(product.price || 0) === 0 && !product.image_url) return;
        if (seen.has(product.id)) return;
        seen.add(product.id);
        products.push({ product, path:[ci,ti,si,pi], subtitle:sub.name });
    }))));
    return products;
}

function bindFields() {
    restaurantName.value = state.restaurant.name || '';
    restaurantAddress.value = state.restaurant.address || '';
    logoInput.value = state.restaurant.logo_url || '';
    zoomInput.value = state.theme.zoom || 100;
    statusBadge.textContent = state.restaurant.status || 'Aberto';
    updateLogo();
}

function persistFields() {
    state.restaurant.name = restaurantName.value;
    state.restaurant.address = restaurantAddress.value;
    state.restaurant.logo_url = logoInput.value;
    state.theme.zoom = Number(zoomInput.value);
    state.theme.background_color = '#ffffff';
    state.theme.background_image = '';
}

function updateLogo() {
    logoPreview.innerHTML = logoInput.value ? '<img src="' + toPublicUrl(logoInput.value).replace(/"/g, '&quot;') + '" alt="">' : (restaurantName.value || 'D').slice(0, 1).toUpperCase();
}

function render() {
    persistFields();
    if (!state.categories[selectedCategoryIndex]) selectedCategoryIndex = 0;
    document.documentElement.style.setProperty('--editor-accent', state.theme.banner_color || '#2b0d15');
    preview.style.backgroundColor = 'transparent';
    preview.style.setProperty('--menu-bg-image', 'none');
    preview.style.transform = 'scale(' + (state.theme.zoom || 100) / 100 + ')';
    preview.style.transformOrigin = 'top center';
    updateLogo();

    const header = '<div class="preview-header"><div class="preview-title"><h1>' + (state.restaurant.name || 'Restaurante') + '</h1><p>' + (state.restaurant.address || 'Endereco do estabelecimento') + '</p></div><span class="status-badge">' + (state.restaurant.status || 'Aberto') + '</span></div>';
    const banner = '<section class="banner" id="categoryBanner"><div class="category-strip" id="categoryStrip"></div><div class="banner-actions"><button class="icon-button" type="button" onclick="editBanner()"><i class="fa-solid fa-pen"></i></button><button class="icon-button" type="button" onclick="addCategory()"><i class="fa-solid fa-plus"></i></button></div></section>';
    preview.innerHTML = header + banner + renderCategories();

    const bannerEl = document.getElementById('categoryBanner');
    const stripEl = document.getElementById('categoryStrip');
    if (bannerEl) {
        bannerEl.classList.toggle('is-transparent', !!state.theme.banner_transparent);
        bannerEl.style.background = state.theme.banner_transparent ? 'transparent' : (state.theme.banner_color || '#2b0d15');
    }
    if (stripEl) stripEl.innerHTML = state.categories.map((cat, index) => '<button class="category-pill ' + (index === selectedCategoryIndex ? 'active' : '') + '" onclick="selectCategory(' + index + ')" style="background:' + (state.theme.banner_color || '#2b0d15') + ';border-color:' + (state.theme.banner_color || '#2b0d15') + '">' + cat.name + '</button>').join('');
    renderPromotionsPage();
}

function selectCategory(index) {
    selectedCategoryIndex = index;
    render();
}

function renderCategories() {
    const cat = state.categories[selectedCategoryIndex];
    if (!cat) return '';
    const titles = selectedCategoryIndex === 0 ? (cat.titles || []).filter(title => title.id !== 'titulo-inicial') : [(cat.titles || [])[0]].filter(Boolean);
    return '<section class="category-section"><h2 class="category-page-title">' + cat.name + '</h2>' + titles.map((title, ti) => {
        const realTi = selectedCategoryIndex === 0 ? (cat.titles || []).findIndex(item => item.id === title.id) : 0;
        const titleRow = selectedCategoryIndex === 0 ? '<div class="title-row"><h2>' + title.name + '</h2><button class="icon-button" onclick="renameTitle(' + selectedCategoryIndex + ',' + realTi + ')"><i class="fa-solid fa-pen"></i></button><button class="icon-button" onclick="deleteTitleAndCategory(' + realTi + ')"><i class="fa-solid fa-trash"></i></button></div>' : '';
        return titleRow + (title.subtitles || []).map((sub, si) => renderProductBlock(selectedCategoryIndex, realTi, si, sub)).join('');
    }).join('') + '</section>';
}

function renderPromotionsPage() {
    const grid = document.getElementById('promoPageGrid');
    if (!grid) return;
    const cards = (state.promotions || []).map((promo, index) => {
        const img = promo.image_url ? '<img src="' + toPublicUrl(promo.image_url).replace(/"/g, '&quot;') + '" alt="">' : '<span class="promo-summary-image"></span>';
        return '<article class="promo-summary-card">' + img + '<div class="promo-summary-body"><strong>' + (promo.title || 'Promocao') + '</strong><p>' + (promo.description || promo.theme || '') + '</p><span class="price">' + money(promotionFinalValue(promo)) + '</span><div class="banner-actions" style="margin-top:10px"><button class="icon-button" onclick="editPromotion(' + index + ')"><i class="fa-solid fa-pen"></i></button><button class="icon-button" onclick="deletePromotion(' + index + ')"><i class="fa-solid fa-trash"></i></button></div></div></article>';
    }).join('');
    grid.innerHTML = cards + '<button class="promo-open-card" type="button" onclick="openPromotions()"><i class="fa-solid fa-tags"></i><span>Abrir editor de promocoes</span></button>';
}

function promotionFinalValue(promo) {
    const selected = promo.items || (promo.item_ids || []).map(id => ({ id, quantity:1 }));
    const total = getProducts().filter(row => selected.some(item => item.id === row.product.id)).reduce((sum, row) => {
        const item = selected.find(selectedItem => selectedItem.id === row.product.id);
        return sum + Number(row.product.price || 0) * Number(item?.quantity || 1);
    }, 0);
    const discount = Number(promo.discount_value || 0);
    return promo.discount_type === 'percent' ? total * (1 - discount / 100) : Math.max(0, total - discount);
}

function renderProductBlock(ci, ti, si, sub) {
    return '<div class="category-description-row"><h3>' + sub.name + '</h3><button class="icon-button" onclick="renameSubtitle(' + ci + ',' + ti + ',' + si + ')"><i class="fa-solid fa-pen"></i></button></div><div class="product-grid">' + (sub.products || []).map((product, pi) => renderProduct(product, [ci,ti,si,pi], (sub.products || []).length)).join('') + renderAddProductCard(ci, ti, si) + '</div>';
}

function createEmptyProduct() {
    return { id:uid('prod'), title:'Novo produto', description:'Descricao do produto. Clique no lapis para editar foto, texto e preco.', image_url:'', pricing_type:'single', price:0, variations:[] };
}

function syncLinkedTitle(ci, ti) {
    const source = state.categories[ci]?.titles?.[ti];
    if (!source) return;
    const sourceCopy = JSON.parse(JSON.stringify(source));
    state.categories.forEach((cat, catIndex) => {
        const matchIndex = (cat.titles || []).findIndex(title => title.id === source.id);
        if (matchIndex >= 0 && !(catIndex === ci && matchIndex === ti)) {
            cat.titles[matchIndex] = JSON.parse(JSON.stringify(sourceCopy));
        }
    });
}

function renderProduct(product, path, totalProducts) {
    const price = product.pricing_type === 'starting_at' ? 'A partir de ' + money(product.price) : money(product.price);
    const image = product.image_url ? '<img src="' + toPublicUrl(product.image_url).replace(/"/g, '&quot;') + '" alt="">' : '<div class="product-empty"></div>';
    return '<article class="product-card">' + image + '<div><h4>' + product.title + '</h4><p>' + product.description + '</p><span class="price">' + price + '</span></div><button class="edit-product" onclick="openProduct(' + path.join(',') + ')"><i class="fa-solid fa-pen"></i></button><button class="delete-product" onclick="deleteProduct(' + path.join(',') + ',' + totalProducts + ')"><i class="fa-solid fa-trash"></i></button></article>';
}

function renderAddProductCard(ci, ti, si) {
    return '<button class="add-product-card" type="button" onclick="addProduct(' + ci + ',' + ti + ',' + si + ')"><i class="fa-solid fa-plus"></i><span>Novo card</span></button>';
}

function deleteTitleAndCategory(ti) {
    const geral = state.categories[0];
    const title = geral?.titles?.[ti];
    if (!title || title.id === 'titulo-inicial') return;
    if (!confirm('Excluir esta categoria, descricao e todos os cards?')) return;
    geral.titles.splice(ti, 1);
    state.categories = state.categories.filter((cat, index) => index === 0 || !(cat.titles || []).some(item => item.id === title.id));
    selectedCategoryIndex = 0;
    render();
}

function addCategory() {
    categoryNameInput.value = '';
    openOverlay('categoryOverlay');
}

function saveCategory() {
    const name = categoryNameInput.value.trim();
    if (!name) return;
    const category = { id: uid('cat'), name:name.toUpperCase(), titles:[{ id:uid('title'), name:name.toUpperCase(), subtitles:[{ id:uid('sub'), name:'Descricao da categoria', products:[createEmptyProduct()] }] }] };
    state.categories.push(category);
    const geral = state.categories[0];
    if (geral && geral.id === 'geral') {
        geral.titles.push(JSON.parse(JSON.stringify(category.titles[0])));
    }
    selectedCategoryIndex = state.categories.length - 1;
    closeOverlay('categoryOverlay');
    render();
}

function editBanner() {
    bannerColorInput.value = state.theme.banner_color || '#2b0d15';
    updateBannerAppearanceControls();
    openOverlay('bannerOverlay');
}

function saveBannerSettings() {
    state.theme.banner_color = bannerColorInput.value || '#2b0d15';
    closeOverlay('bannerOverlay');
    render();
}

function updateBannerAppearanceControls() {
    const color = bannerColorInput.value || state.theme.banner_color || '#2b0d15';
    bannerColorDot.style.background = color;
    bannerTransparentToggle.classList.toggle('active', !!state.theme.banner_transparent);
    bannerTransparentLabel.textContent = state.theme.banner_transparent ? 'Sim, destacar apenas a tag' : 'Nao, manter painel';
}

function toggleBannerTransparency() {
    state.theme.banner_transparent = !state.theme.banner_transparent;
    updateBannerAppearanceControls();
}

function addTitle(ci) {
    const name = prompt('Nome do título:', 'Novo título');
    if (!name) return;
    state.categories[ci].titles.push({ id:uid('title'), name, subtitles:[] });
    render();
}

function renameTitle(ci, ti) {
    const name = prompt('Novo nome:', state.categories[ci].titles[ti].name);
    if (name) state.categories[ci].titles[ti].name = name;
    syncLinkedTitle(ci, ti);
    render();
}

function addSubtitle(ci, ti) {
    const name = prompt('Nome do subtítulo:', 'Novo subtítulo');
    if (!name) return;
    state.categories[ci].titles[ti].subtitles.push({ id:uid('sub'), name, products:[] });
    render();
}

function renameSubtitle(ci, ti, si) {
    const sub = state.categories[ci].titles[ti].subtitles[si];
    const name = prompt('Novo subtítulo:', sub.name);
    if (name) sub.name = name;
    syncLinkedTitle(ci, ti);
    render();
}

function addProduct(ci, ti, si) {
    state.categories[ci].titles[ti].subtitles[si].products.push(createEmptyProduct());
    syncLinkedTitle(ci, ti);
    render();
}

function deleteProduct(ci, ti, si, pi, totalProducts) {
    if (totalProducts <= 1) {
        alert('Sempre deve existir ao menos um card de exemplo.');
        return;
    }
    state.categories[ci].titles[ti].subtitles[si].products.splice(pi, 1);
    syncLinkedTitle(ci, ti);
    render();
}
function openProduct(ci, ti, si, pi) {
    currentProductPath = [ci, ti, si, pi];
    const product = state.categories[ci].titles[ti].subtitles[si].products[pi];
    productTitle.value = product.title || '';
    productDescription.value = product.description || '';
    productImage.value = product.image_url || '';
    productPrice.value = product.price || 0;
    renderVariations(product.variations || []);
    setProductPricing(product.pricing_type || 'single');
    renderProductImagePreview();
    openOverlay('productOverlay');
}

function renderVariations(variations) {
    variationList.innerHTML = variations.concat([{ title:'', price:'' }]).map((variation, index) => '<div class="variation-card"><label class="field"><span>Titulo da variacao</span><input data-var-title="' + index + '" placeholder="Ex: Pequeno, medio, grande" value="' + (variation.title || '') + '"></label><label class="field"><span>Valor</span><input data-var-price="' + index + '" type="number" step="0.01" placeholder="R$" value="' + (variation.price || '') + '"></label></div>').join('');
}

function setProductPricing(type) {
    productPricing.value = type;
    priceSingleTab.classList.toggle('active', type === 'single');
    priceStartingTab.classList.toggle('active', type === 'starting_at');
    singlePricePanel.classList.toggle('active', type === 'single');
    startingPricePanel.classList.toggle('active', type === 'starting_at');
}

function renderProductImagePreview() {
    const image = productImage.value;
    productImageDrop.innerHTML = image ? '<img src="' + toPublicUrl(image).replace(/"/g, '&quot;') + '" alt=""><input id="productImageFile" type="file" accept="image/jpeg,image/png,image/gif,image/webp">' : '<span><i class="fa-solid fa-image"></i><br>Enviar imagem do produto</span><input id="productImageFile" type="file" accept="image/jpeg,image/png,image/gif,image/webp">';
    bindProductImageUpload();
}

function addVariation() {
    const product = getCurrentProduct();
    product.variations = readVariations();
    product.variations.push({ title:'', price:0 });
    renderVariations(product.variations);
}

function readVariations() {
    return Array.from(document.querySelectorAll('[data-var-title]')).map((titleInput, index) => {
        const priceInput = document.querySelector('[data-var-price="' + index + '"]');
        return { title:titleInput.value, price:Number(priceInput.value || 0) };
    }).filter(item => item.title || item.price > 0);
}

function getCurrentProduct() {
    const [ci, ti, si, pi] = currentProductPath;
    return state.categories[ci].titles[ti].subtitles[si].products[pi];
}

function saveProduct() {
    const product = getCurrentProduct();
    product.title = productTitle.value;
    product.description = productDescription.value;
    product.image_url = productImage.value;
    product.pricing_type = productPricing.value;
    product.price = Number(productPrice.value || 0);
    product.variations = product.pricing_type === 'starting_at' ? readVariations() : [];
    if (product.pricing_type === 'starting_at' && product.variations.length) {
        product.price = Math.min.apply(null, product.variations.map(item => Number(item.price || 0)).filter(Boolean));
    }
    syncLinkedTitle(currentProductPath[0], currentProductPath[1]);
    closeOverlay('productOverlay');
    render();
}

function openPromotions() {
    promoEditStep.classList.add('active');
    promoCreateStep.classList.remove('active');
    renderPromoItems();
    openOverlay('promotionOverlay');
}

function renderPromoItems() {
    const term = (promoSearch.value || '').toLowerCase();
    const rows = getProducts().filter(row => row.product.title.toLowerCase().includes(term));
    promoItems.innerHTML = rows.map(row => {
        const selected = selectedPromoIds.includes(row.product.id);
        const safeId = String(row.product.id).replace(/'/g, "\\'");
        return `<div class="promo-item-card ${selected ? 'selected' : ''}"><button type="button" class="select-dot" onclick="togglePromoItem('${safeId}')"><i class="fa-solid fa-check"></i></button><strong>${row.product.title}</strong><small>${row.subtitle}</small><span class="price">${money(row.product.price)}</span></div>`;
    }).join('') || '<p style="color:var(--muted);font-size:13px">Nenhum item cadastrado no cardapio.</p>';
}

function togglePromoItem(id) {
    if (selectedPromoIds.includes(id)) {
        selectedPromoIds = selectedPromoIds.filter(item => item !== id);
        delete selectedPromoQuantities[id];
    } else {
        selectedPromoIds = selectedPromoIds.concat(id);
        selectedPromoQuantities[id] = 1;
    }
    renderPromoItems();
    updatePromoMath();
}

function changePromoQty(id, delta) {
    selectedPromoQuantities[id] = Math.max(1, (selectedPromoQuantities[id] || 1) + delta);
    renderPromoItems();
    renderSelectedPromoItems();
    updatePromoMath();
}

function showPromoCreate() {
    promoEditStep.classList.remove('active');
    promoCreateStep.classList.add('active');
    renderSelectedPromoItems();
    updatePromoMath();
}

function showPromoEdit() {
    promoCreateStep.classList.remove('active');
    promoEditStep.classList.add('active');
    renderPromoItems();
}

function updatePromoMath() {
    const total = getProducts().filter(row => selectedPromoIds.includes(row.product.id)).reduce((sum, row) => sum + Number(row.product.price || 0) * (selectedPromoQuantities[row.product.id] || 1), 0);
    const discount = Number(promoDiscountValue.value || 0);
    const finalValue = promoDiscountType.value === 'percent' ? total * (1 - discount / 100) : Math.max(0, total - discount);
    promoDiscountLabel.textContent = promoDiscountType.value === 'percent' ? 'Digite a porcentagem' : 'Digite o valor';
    promoOriginalValue.textContent = money(total);
    promoFinalValue.textContent = money(finalValue);
}

function renderSelectedPromoItems() {
    if (!promoSelectedPanel) return;
    const selectedRows = getProducts().filter(row => selectedPromoIds.includes(row.product.id));
    promoSelectedPanel.innerHTML = selectedRows.length
        ? selectedRows.map(row => {
            const safeId = String(row.product.id).replace(/'/g, "\\'");
            const qty = selectedPromoQuantities[row.product.id] || 1;
            return `<div class="promo-selected-item"><div><strong>${row.product.title}</strong><small>${row.subtitle} - ${money(row.product.price)}</small></div><div class="qty"><button type="button" onclick="changePromoQty('${safeId}',-1)">-</button><strong>${qty}</strong><button type="button" onclick="changePromoQty('${safeId}',1)">+</button></div></div>`;
        }).join('')
        : '<div class="promo-price-tag"><span>Produtos selecionados</span><strong>Nenhum item</strong></div>';
}

function resetPromoImageInput() {
    promoImageDrop.innerHTML = '<span><i class="fa-solid fa-image"></i><br>Enviar imagem da promocao</span><input id="promoImageFile" type="file" accept="image/jpeg,image/png,image/gif,image/webp">';
    bindPromoImageUpload();
}

function createPromotion() {
    updatePromoMath();
    const promoData = { id:editingPromotionIndex === null ? uid('promo') : state.promotions[editingPromotionIndex].id, theme:promoTheme.value || 'Promocao', title:promoTitle.value || 'Nova promocao', description:promoDescription.value || '', image_url:promoImageUrl, items:selectedPromoIds.map(id => ({ id, quantity:selectedPromoQuantities[id] || 1 })), item_ids:selectedPromoIds, discount_type:promoDiscountType.value, discount_value:Number(promoDiscountValue.value || 0) };
    if (editingPromotionIndex === null) state.promotions.push(promoData);
    else state.promotions[editingPromotionIndex] = promoData;
    selectedPromoIds = [];
    selectedPromoQuantities = {};
    promoImageUrl = '';
    editingPromotionIndex = null;
    promoTitle.value = '';
    promoDescription.value = '';
    promoTheme.value = '';
    promoDiscountValue.value = '';
    resetPromoImageInput();
    closeOverlay('promotionOverlay');
    render();
}

function editPromotion(index) {
    const promo = state.promotions[index];
    editingPromotionIndex = index;
    selectedPromoIds = (promo.items || []).map(item => item.id);
    if (!selectedPromoIds.length) selectedPromoIds = promo.item_ids || [];
    selectedPromoQuantities = {};
    (promo.items || []).forEach(item => selectedPromoQuantities[item.id] = item.quantity || 1);
    selectedPromoIds.forEach(id => selectedPromoQuantities[id] = selectedPromoQuantities[id] || 1);
    promoImageUrl = promo.image_url || '';
    promoTitle.value = promo.title || '';
    promoDescription.value = promo.description || '';
    promoTheme.value = promo.theme || '';
    promoDiscountType.value = promo.discount_type || 'fixed';
    promoDiscountValue.value = promo.discount_value || 0;
    if (promoImageUrl) promoImageDrop.innerHTML = '<img src="' + toPublicUrl(promoImageUrl).replace(/"/g, '&quot;') + '" alt=""><input id="promoImageFile" type="file" accept="image/jpeg,image/png,image/gif,image/webp">';
    bindPromoImageUpload();
    openPromotions();
    showPromoCreate();
}

function deletePromotion(index) {
    if (!confirm('Excluir esta promocao?')) return;
    state.promotions.splice(index, 1);
    render();
}
function openOverlay(id) { document.getElementById(id).classList.add('open'); }
function closeOverlay(id) { document.getElementById(id).classList.remove('open'); }

function bindPromoImageUpload() {
    const input = document.getElementById('promoImageFile');
    if (!input) return;
    input.addEventListener('change', async () => {
        if (!input.files.length) return;
        try {
            promoImageUrl = await uploadEditorAsset(input.files[0]);
            promoImageDrop.innerHTML = '<img src="' + toPublicUrl(promoImageUrl).replace(/"/g, '&quot;') + '" alt=""><input id="promoImageFile" type="file" accept="image/jpeg,image/png,image/gif,image/webp">';
            bindPromoImageUpload();
        } catch (error) {
            alert(error.message);
        }
    });
}

function bindProductImageUpload() {
    const input = document.getElementById('productImageFile');
    if (!input) return;
    input.addEventListener('change', async () => {
        if (!input.files.length) return;
        try {
            productImage.value = await uploadEditorAsset(input.files[0]);
            renderProductImagePreview();
        } catch (error) {
            alert(error.message);
        }
    });
}

async function saveEditor(silent = false) {
    persistFields();
    const form = new FormData();
    form.append('action', 'save_editor');
    form.append('csrf_token', csrfToken);
    form.append('jwt_token', editorToken);
    form.append('public_id', publicId);
    form.append('editor_data', JSON.stringify(state));
    const response = await fetch(apiUrl, { method:'POST', body:form });
    const result = await response.json();
    if (!result.ok) {
        alert(result.error || 'Erro ao salvar');
        return false;
    }
    if (!silent) alert(result.message || 'Salvo');
    return true;
}

async function publishMenu() {
    const saved = await saveEditor(true);
    if (!saved) return;
    const form = new FormData();
    form.append('action', 'publish_editor');
    form.append('csrf_token', csrfToken);
    form.append('jwt_token', editorToken);
    form.append('public_id', publicId);
    const response = await fetch(apiUrl, { method:'POST', body:form });
    const result = await response.json();
    if (!result.ok) return alert(result.error || 'Erro ao publicar');
    publishedUrl.value = result.url;
    openOverlay('successOverlay');
}

[restaurantName, restaurantAddress, logoInput, zoomInput].forEach(el => el.addEventListener('input', render));
mapAddressInput.addEventListener('input', updateMapExternalLink);
bannerColorInput.addEventListener('input', () => {
    state.theme.banner_color = bannerColorInput.value || '#2b0d15';
    updateBannerAppearanceControls();
    render();
});
logoFile.addEventListener('change', async () => {
    if (!logoFile.files.length) return;
    try {
        logoInput.value = await uploadEditorAsset(logoFile.files[0]);
        render();
    } catch (error) {
        alert(error.message);
    }
});
[promoDiscountType, promoDiscountValue].forEach(el => el.addEventListener('input', updatePromoMath));
bindPromoImageUpload();
bindProductImageUpload();
bindFields();
render();
if (['cardapio', 'promocoes', 'fidelidade'].includes(window.location.hash.replace('#', ''))) {
    switchEditorRoute(window.location.hash.replace('#', ''));
}
</script>
</body>
</html>
