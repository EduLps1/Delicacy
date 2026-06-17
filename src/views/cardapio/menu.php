<?php
/**
 * DELICACY - CardÃ¡pio Digital PÃºblico
 *
 * VariÃ¡veis: $menu, $itemsGrouped, $editorData
 */
$clientState = $editorData ?? [];
if (empty($clientState['categories']) && !empty($itemsGrouped)) {
    $products = [];
    foreach ($itemsGrouped as $category => $items) {
        foreach ($items as $item) {
            $products[] = [
                'id' => 'legacy-' . $item['id'],
                'title' => $item['name'],
                'description' => $item['description'] ?? '',
                'image_url' => !empty($item['image_url']) ? BASE_URL . $item['image_url'] : '',
                'pricing_type' => 'single',
                'price' => (float)$item['price'],
                'variations' => [],
            ];
        }
    }
    $clientState = [
        'restaurant' => ['name' => $menu['restaurant_name'], 'address' => '', 'status' => 'Aberto', 'logo_url' => $menu['restaurant_logo'] ?? ''],
        'theme' => ['banner_color' => '#2b0d15', 'background_color' => '#ffffff'],
        'categories' => [['id' => 'geral', 'name' => 'GERAL', 'titles' => [['id' => 'menu', 'name' => $menu['name'], 'subtitles' => [['id' => 'itens', 'name' => 'Itens', 'products' => $products]]]]]],
        'promotions' => [],
        'loyalty_enabled' => false,
    ];
}
$stateJson = json_encode($clientState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$loyaltyEnabled = !empty($clientState['loyalty_enabled']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($menu['name']); ?> - <?php echo htmlspecialchars($menu['restaurant_name']); ?></title>
    <meta name="description" content="CardÃ¡pio digital de <?php echo htmlspecialchars($menu['restaurant_name']); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --wine:#561d2a; --dark:#201114; --gold:#c79a54; --muted:#766b6d; --line:#eee8e1; --soft:#faf7f2; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; background:#fff; color:var(--dark); font-family:Inter,system-ui,sans-serif; padding-bottom:86px; }
        button,input,textarea { font:inherit; }
        button { cursor:pointer; }
        .app { max-width:760px; margin:0 auto; min-height:100vh; background:#fff; }
        .hero { position:sticky; top:0; z-index:10; padding:22px 18px 18px; color:#fff; background:var(--wine); border-radius:0 0 28px 28px; box-shadow:0 18px 45px rgba(43,13,21,.18); }
        .hero-top { display:flex; gap:12px; align-items:center; }
        .avatar { width:54px; height:54px; border-radius:18px; display:grid; place-items:center; background:rgba(255,255,255,.16); overflow:hidden; font-weight:900; }
        .avatar img { width:100%; height:100%; object-fit:cover; }
        .hero h1 { margin:0; font-size:22px; letter-spacing:-.04em; }
        .hero p { margin:5px 0 0; color:rgba(255,255,255,.72); font-size:12px; }
        .status { margin-left:auto; padding:8px 10px; border-radius:999px; background:#ecfdf5; color:#047857; font-size:10px; font-weight:900; }
        .content { padding:20px 16px; }
        .tab-panel { display:none; animation:enter .2s ease both; }
        .tab-panel.active { display:block; }
        @keyframes enter { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        .category-strip { display:flex; gap:8px; overflow:auto; padding-bottom:12px; margin-bottom:12px; }
        .category-strip button { border:1px solid var(--line); border-radius:999px; background:#fff; padding:10px 14px; color:var(--wine); font-size:11px; font-weight:900; white-space:nowrap; }
        .category-strip button.active { background:var(--wine); border-color:var(--wine); color:#fff; }
        .section-title { margin:24px 0 10px; font-size:20px; letter-spacing:-.04em; }
        .subtitle { margin:18px 0 10px; color:var(--muted); font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:.1em; }
        .product-list { display:grid; gap:12px; }
        .product { display:grid; grid-template-columns:96px 1fr; gap:12px; padding:12px; border:1px solid var(--line); border-radius:22px; background:#fff; box-shadow:0 14px 34px rgba(43,13,21,.06); text-align:left; }
        .product img,.product-empty { width:96px; height:96px; border-radius:16px; object-fit:cover; background:linear-gradient(135deg,#f3e7dc,#fff); }
        .product h3 { margin:0; font-size:15px; }
        .product p { margin:6px 0; color:var(--muted); font-size:12px; line-height:1.45; }
        .price { color:var(--wine); font-size:13px; font-weight:900; }
        .promo-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; align-items:stretch; }
        .promo-grid .product { height:100%; min-height:166px; grid-template-columns:1fr; align-content:start; padding:0; overflow:hidden; border-radius:24px; }
        .promo-grid .product img,.promo-grid .product .product-empty { width:100%; height:112px; border-radius:0; }
        .promo-grid .product > div { padding:14px; }
        .promo-grid .product p { min-height:34px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .promo-card { border-radius:24px; padding:18px; margin-bottom:12px; background:linear-gradient(135deg,var(--wine),#2b0d15); color:#fff; overflow:hidden; }
        .promo-card img { width:100%; height:150px; object-fit:cover; border-radius:18px; margin-bottom:14px; background:rgba(255,255,255,.12); }
        .promo-card small { color:var(--gold); text-transform:uppercase; letter-spacing:.12em; font-weight:900; }
        .promo-card button { margin-top:12px; border:0; border-radius:999px; padding:12px 14px; background:#fff; color:var(--wine); font-weight:900; }
        .cart-list { display:grid; gap:10px; }
        .cart-row { display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; padding:14px; border:1px solid var(--line); border-radius:18px; }
        .qty { display:flex; align-items:center; gap:8px; }
        .qty button,.trash { width:32px; height:32px; border-radius:50%; border:1px solid var(--line); background:#fff; }
        .checkout { width:100%; margin-top:18px; border:0; border-radius:999px; background:var(--wine); color:#fff; padding:15px; font-weight:900; }
        .bottom-nav { position:fixed; left:50%; bottom:14px; transform:translateX(-50%); z-index:40; width:min(720px, calc(100% - 24px)); display:grid; grid-template-columns:repeat(5,1fr); gap:6px; padding:8px; border:1px solid var(--line); border-radius:24px; background:rgba(255,255,255,.94); box-shadow:0 18px 48px rgba(43,13,21,.16); backdrop-filter:blur(14px); }
        .bottom-nav button { border:0; border-radius:18px; background:transparent; color:var(--muted); padding:9px 4px; font-size:10px; font-weight:800; }
        .bottom-nav button.active { background:var(--wine); color:#fff; }
        .bottom-nav i { display:block; margin-bottom:4px; font-size:15px; }
        .modal { position:fixed; inset:0; z-index:60; display:none; align-items:flex-end; background:rgba(0,0,0,.55); }
        .modal.open { display:flex; }
        .sheet { width:100%; max-width:760px; margin:0 auto; max-height:88vh; overflow:auto; border-radius:28px 28px 0 0; background:#fff; padding:18px; animation:up .25s ease both; }
        @keyframes up { from { transform:translateY(100%); } to { transform:translateY(0); } }
        .sheet-head { display:flex; justify-content:space-between; align-items:center; gap:12px; }
        .sheet h2 { margin:0; font-size:22px; }
        .close { border:0; background:#f4f1ee; border-radius:50%; width:40px; height:40px; }
        .option { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:13px; border:1px solid var(--line); border-radius:16px; margin-top:10px; }
        .option input { width:18px; height:18px; }
        .buy-footer { position:sticky; bottom:-18px; margin:16px -18px -18px; padding:14px 18px; display:grid; grid-template-columns:140px 1fr; gap:10px; background:#fff; border-top:1px solid var(--line); }
        .add-cart { border:0; border-radius:999px; background:var(--wine); color:#fff; font-weight:900; }
        .profile-grid,.address-form { display:grid; gap:12px; }
        .field { display:grid; gap:5px; }
        .field span { color:var(--muted); font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
        .field input { border:1px solid var(--line); border-radius:14px; padding:12px; outline:0; }
        .segmented { display:flex; gap:8px; }
        .segmented button { border:1px solid var(--line); border-radius:999px; background:#fff; padding:10px 14px; font-size:12px; }
        .empty { text-align:center; color:var(--muted); padding:34px 10px; }
        .payment-options { display:grid; gap:10px; margin-top:14px; }
        .payment-options button { border:1px solid var(--line); border-radius:18px; background:#fff; padding:14px; text-align:left; font-weight:900; }
        .notice { margin-top:12px; padding:12px; border-radius:16px; background:var(--soft); color:var(--muted); font-size:13px; }
        @media (max-width:560px) { .promo-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<div class="app">
    <header class="hero" id="hero">
        <div class="hero-top">
            <div class="avatar" id="restaurantAvatar">D</div>
            <div><h1 id="restaurantName"></h1><p id="restaurantAddress"></p></div>
            <span class="status" id="restaurantStatus">Aberto</span>
        </div>
    </header>

    <main class="content">
        <section id="panel-cardapio" class="tab-panel active">
            <div class="category-strip" id="categoryStrip"></div>
            <div id="menuContent"></div>
        </section>
        <section id="panel-promocoes" class="tab-panel"><div id="promotionsContent"></div></section>
        <section id="panel-carrinho" class="tab-panel"><h2 class="section-title">Carrinho</h2><div id="cartContent"></div></section>
        <?php if ($loyaltyEnabled): ?>
            <section id="panel-fidelidade" class="tab-panel"><div class="empty"><h2>Estamos Trabalhando</h2><p>O mÃ³dulo de fidelidade serÃ¡ liberado para este restaurante em breve.</p></div></section>
        <?php endif; ?>
        <section id="panel-pedidos" class="tab-panel">
            <div class="category-strip"><button onclick="showOrdersSubtab('historico')">Pedidos</button><button onclick="showOrdersSubtab('enderecos')">EndereÃ§os</button><button onclick="showOrdersSubtab('conta')">Conta</button></div>
            <div id="ordersSubtab"></div>
        </section>
    </main>
</div>

<nav class="bottom-nav" style="grid-template-columns: repeat(<?php echo $loyaltyEnabled ? 5 : 4; ?>, 1fr);">
    <button class="active" data-tab="cardapio"><i class="fa-solid fa-utensils"></i>CardÃ¡pio</button>
    <button data-tab="promocoes"><i class="fa-solid fa-tags"></i>PromoÃ§Ãµes</button>
    <button data-tab="carrinho"><i class="fa-solid fa-cart-shopping"></i>Carrinho</button>
    <?php if ($loyaltyEnabled): ?><button data-tab="fidelidade"><i class="fa-solid fa-award"></i>Fidelidade</button><?php endif; ?>
    <button data-tab="pedidos"><i class="fa-solid fa-receipt"></i>Meus Pedidos</button>
</nav>

<div class="modal" id="productModal">
    <div class="sheet">
        <div class="sheet-head"><h2 id="modalTitle"></h2><button class="close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button></div>
        <p id="modalDescription" style="color:var(--muted);line-height:1.6"></p>
        <div id="modalOptions"></div>
        <div class="buy-footer">
            <div class="qty"><button onclick="changeModalQty(-1)">-</button><strong id="modalQty">1</strong><button onclick="changeModalQty(1)">+</button></div>
            <button class="add-cart" onclick="addCurrentToCart()">Adicionar ao Carrinho Â· <span id="modalTotal"></span></button>
        </div>
    </div>
</div>

<div class="modal" id="paymentModal">
    <div class="sheet">
        <div class="sheet-head"><h2>Pagamento</h2><button class="close" onclick="paymentModal.classList.remove('open')"><i class="fa-solid fa-xmark"></i></button></div>
        <p style="color:var(--muted)">Escolha uma opcao para finalizar o pedido.</p>
        <div class="payment-options">
            <button onclick="showPixNotice()"><i class="fa-brands fa-pix"></i> PIX</button>
            <button onclick="processTestPayment()"><i class="fa-solid fa-flask"></i> Teste</button>
        </div>
        <div id="paymentNotice"></div>
    </div>
</div>

<script>
const state = <?php echo $stateJson ?: '{}'; ?>;
const storageKey = 'delicacy_cart_<?php echo (int)$menu['id']; ?>';
const baseUrl = <?php echo json_encode(rtrim(BASE_URL, '/')); ?>;
let cart = JSON.parse(localStorage.getItem(storageKey) || '[]');
let currentProduct = null;
let currentPromotionIndex = null;
let currentQty = 1;
let currentVariation = null;
let selectedCategoryIndex = 0;
let orders = JSON.parse(localStorage.getItem(storageKey + '_orders') || '[]');

function money(value) { return 'R$ ' + Number(value || 0).toFixed(2).replace('.', ','); }
function publicUrl(path) {
    if (!path) return '';
    if (/^https?:\/\//i.test(path) || path.startsWith('data:')) return path;
    return baseUrl + '/' + String(path).replace(/^\/+/, '');
}
function allProducts() {
    const products = [];
    const seen = new Set();
    (state.categories || []).forEach(cat => (cat.titles || []).filter(title => title.id !== 'titulo-inicial').forEach(title => (title.subtitles || []).forEach(sub => (sub.products || []).forEach(product => {
        if (product.id === 'produto-demo' || seen.has(product.id)) return;
        seen.add(product.id);
        products.push(product);
    }))));
    return products;
}

function renderApp() {
    document.documentElement.style.setProperty('--wine', state.theme?.banner_color || '#561d2a');
    document.body.style.background = state.theme?.background_color || '#ffffff';
    restaurantName.textContent = state.restaurant?.name || <?php echo json_encode($menu['restaurant_name']); ?>;
    restaurantAddress.textContent = state.restaurant?.address || 'CardÃ¡pio digital';
    restaurantStatus.textContent = state.restaurant?.status || 'Aberto';
    restaurantAvatar.innerHTML = state.restaurant?.logo_url ? '<img src="' + publicUrl(state.restaurant.logo_url) + '" alt="">' : (restaurantName.textContent || 'D').slice(0, 1);
    categoryStrip.innerHTML = (state.categories || []).map((cat, index) => `<button class="${index === selectedCategoryIndex ? 'active' : ''}" onclick="selectCategory(${index})">${cat.name}</button>`).join('');
    menuContent.innerHTML = renderMenu();
    promotionsContent.innerHTML = renderPromotions();
    renderCart();
    showOrdersSubtab('historico');
}

function renderMenu() {
    const cat = (state.categories || [])[selectedCategoryIndex];
    if (!cat) return '';
    const titles = selectedCategoryIndex === 0 ? (cat.titles || []).filter(title => title.id !== 'titulo-inicial') : [(cat.titles || [])[0]].filter(Boolean);
    return '<div id="cat-' + cat.id + '"><h2 class="section-title">' + cat.name + '</h2>' + titles.map(title => (title.subtitles || []).map(sub => '<h3 class="subtitle">' + sub.name + '</h3><div class="product-list">' + (sub.products || []).filter(product => product.id !== 'produto-demo').map(renderProduct).join('') + '</div>').join('')).join('') + '</div>';
}

function selectCategory(index) {
    selectedCategoryIndex = index;
    categoryStrip.innerHTML = (state.categories || []).map((cat, itemIndex) => `<button class="${itemIndex === selectedCategoryIndex ? 'active' : ''}" onclick="selectCategory(${itemIndex})">${cat.name}</button>`).join('');
    menuContent.innerHTML = renderMenu();
}
function renderProduct(product) {
    const image = product.image_url ? '<img src="' + publicUrl(product.image_url) + '" alt="">' : '<div class="product-empty"></div>';
    const price = product.pricing_type === 'starting_at' ? 'A partir de ' + money(product.price) : money(product.price);
    return `<button class="product" onclick="openProduct('${product.id}')">${image}<div><h3>${product.title}</h3><p>${product.description}</p><span class="price">${price}</span></div></button>`;
}

function renderPromotions() {
    if (!(state.promotions || []).length) return '<div class="empty"><h2>Nenhuma promocao ativa</h2><p>Volte em breve para novas ofertas.</p></div>';
    return '<div class="promo-grid">' + state.promotions.map((promo, index) => {
        const image = promo.image_url ? '<img src="' + publicUrl(promo.image_url) + '" alt="">' : '<div class="product-empty"></div>';
        return '<button class="product" onclick="openPromotion(' + index + ')">' + image + '<div><h3>' + (promo.title || 'Promocao') + '</h3><p>' + (promo.description || promo.theme || '') + '</p><span class="price">' + money(promotionPrice(promo)) + '</span></div></button>';
    }).join('') + '</div>';
}

function promotionPrice(promo) {
    const products = allProducts();
    const total = (promo.items || (promo.item_ids || []).map(id => ({ id, quantity:1 }))).reduce((sum, item) => {
        const product = products.find(row => row.id === item.id);
        return sum + Number(product?.price || 0) * (item.quantity || 1);
    }, 0);
    const discount = Number(promo.discount_value || 0);
    return promo.discount_type === 'percent' ? total * (1 - discount / 100) : Math.max(0, total - discount);
}

function openProduct(id) {
    currentPromotionIndex = null;
    currentProduct = allProducts().find(product => product.id === id);
    currentQty = 1;
    currentVariation = null;
    modalTitle.textContent = currentProduct.title;
    modalDescription.textContent = currentProduct.description || '';
    modalOptions.innerHTML = '';
    if (currentProduct.pricing_type === 'starting_at') {
        modalOptions.innerHTML = (currentProduct.variations || []).map((variation, index) => '<label class="option"><span>' + variation.title + '<strong style="display:block;color:var(--wine)">' + money(variation.price) + '</strong></span><input type="radio" name="variation" value="' + index + '" onchange="selectVariation(' + index + ')"></label>').join('');
    }
    updateModalTotal();
    productModal.classList.add('open');
}

function openPromotion(index) {
    currentProduct = null;
    currentPromotionIndex = index;
    const promo = state.promotions[index];
    currentQty = 1;
    currentVariation = null;
    modalTitle.textContent = promo.title || 'Promocao';
    modalDescription.textContent = promo.description || promo.theme || '';
    modalOptions.innerHTML = '<div class="option"><span>Valor promocional<strong style="display:block;color:var(--wine)">' + money(promotionPrice(promo)) + '</strong></span></div>';
    updateModalTotal();
    productModal.classList.add('open');
}

function selectVariation(index) {
    currentVariation = currentProduct.variations[index];
    updateModalTotal();
}

function currentUnitPrice() {
    if (currentPromotionIndex !== null) return promotionPrice(state.promotions[currentPromotionIndex]);
    return currentVariation ? Number(currentVariation.price) : Number(currentProduct.price || 0);
}

function changeModalQty(delta) {
    currentQty = Math.max(1, currentQty + delta);
    updateModalTotal();
}

function updateModalTotal() {
    modalQty.textContent = currentQty;
    modalTotal.textContent = money(currentUnitPrice() * currentQty);
}

function addCurrentToCart() {
    if (currentPromotionIndex !== null) {
        const promo = state.promotions[currentPromotionIndex];
        cart.push({ id:'promo-' + promo.id + '-' + Date.now(), product_id:promo.id, title:promo.title || 'Promocao', description:promo.description || promo.theme || '', price:promotionPrice(promo), quantity:currentQty });
        saveCart();
        closeModal();
        switchTab('carrinho');
        return;
    }
    if (currentProduct.pricing_type === 'starting_at' && !currentVariation) {
        alert('Selecione uma opÃ§Ã£o para continuar.');
        return;
    }
    cart.push({ id:currentProduct.id + '-' + Date.now(), product_id:currentProduct.id, title:currentProduct.title, description:currentVariation?.title || currentProduct.description || '', price:currentUnitPrice(), quantity:currentQty });
    saveCart();
    closeModal();
    switchTab('carrinho');
}

function closeModal() { productModal.classList.remove('open'); }
function saveCart() { localStorage.setItem(storageKey, JSON.stringify(cart)); renderCart(); }

function renderCart() {
    if (!cart.length) {
        cartContent.innerHTML = '<div class="empty"><h2>Seu carrinho estÃ¡ vazio</h2><p>Escolha um item no cardÃ¡pio para comeÃ§ar.</p></div>';
        return;
    }
    const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    cartContent.innerHTML = '<div class="cart-list">' + cart.map(item => `<div class="cart-row"><div><strong>${item.title}</strong><p style="margin:4px 0;color:var(--muted);font-size:12px">${item.description}</p><span class="price">${money(item.price * item.quantity)}</span></div><div class="qty"><button onclick="updateCartQty('${item.id}',-1)">-</button><strong>${item.quantity}</strong><button onclick="updateCartQty('${item.id}',1)">+</button><button class="trash" onclick="removeCart('${item.id}')"><i class="fa-solid fa-trash"></i></button></div></div>`).join('') + `</div><button class="checkout" onclick="openPayment()">Finalizar Pedido - ${money(total)}</button>`;
}

function openPayment() {
    paymentNotice.innerHTML = '';
    paymentModal.classList.add('open');
}

function showPixNotice() {
    paymentNotice.innerHTML = '<div class="notice">A implementacao com gateway PIX ainda esta em desenvolvimento.</div>';
}

function processTestPayment() {
    const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const order = { id:'#T' + Date.now().toString().slice(-5), date:new Date().toLocaleDateString('pt-BR'), time:new Date().toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'}), method:'Teste', rating:'5 estrelas', total, status:'Em preparo', items:[...cart] };
    orders.unshift(order);
    localStorage.setItem(storageKey + '_orders', JSON.stringify(orders));
    cart = [];
    saveCart();
    paymentModal.classList.remove('open');
    switchTab('pedidos');
    showOrdersSubtab('historico');
}
function updateCartQty(id, delta) {
    cart = cart.map(item => item.id === id ? { ...item, quantity:Math.max(1, item.quantity + delta) } : item);
    saveCart();
}

function removeCart(id) { cart = cart.filter(item => item.id !== id); saveCart(); }

function switchTab(tab) {
    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
    document.querySelector('#panel-' + tab).classList.add('active');
    document.querySelectorAll('.bottom-nav button').forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tab));
    if (tab === 'carrinho') renderCart();
}

document.querySelectorAll('.bottom-nav button').forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

function showOrdersSubtab(tab) {
    if (tab === 'historico') {
        ordersSubtab.innerHTML = orders.length ? '<div class="cart-list">' + orders.map(order => '<div class="cart-row"><div><strong>' + order.id + '</strong><p style="color:var(--muted);font-size:12px">' + order.date + ' - ' + order.time + ' - ' + order.method + ' - ' + order.rating + '</p><span class="price">' + money(order.total) + '</span></div><span class="status">' + (order.status || 'Em preparo') + '</span></div>').join('') + '</div>' : '<div class="empty"><h2>Nenhum pedido ainda</h2><p>Finalize um pedido teste para acompanhar aqui.</p></div>';
    }
    if (tab === 'enderecos') {
        ordersSubtab.innerHTML = '<div class="address-form"><div class="segmented"><button>Casa</button><button>Trabalho</button><button>Outro</button></div>' + ['Como quer chamar*','CEP','Endereco*','Numero*','Complemento*','Bairro*','Ponto de Referencia'].map(label => '<label class="field"><span>' + label + '</span><input></label>').join('') + '<button class="checkout">Salvar Endereco</button></div>';
    }
    if (tab === 'conta') {
        const customer = JSON.parse(localStorage.getItem(storageKey + '_customer') || '{}');
        ordersSubtab.innerHTML = '<div class="profile-grid"><div class="avatar" style="color:#fff;background:var(--wine)">CL</div><h2>Dados Pessoais</h2><label class="field"><span>Nome</span><input id="customerNameInput" value="' + (customer.name || '') + '"></label><label class="field"><span>CPF</span><input id="customerCpfInput" value="' + (customer.cpf || '') + '"></label><h2>Contato</h2><label class="field"><span>Celular</span><input id="customerPhoneInput" value="' + (customer.phone || '') + '"></label><label class="field"><span>E-mail</span><input id="customerEmailInput" value="' + (customer.email || '') + '"></label><button class="checkout" onclick="saveTestCustomer()">Salvar Alteracoes</button></div>';
    }
}

function saveTestCustomer() {
    const customer = {
        name:customerNameInput.value || 'Cliente teste',
        cpf:customerCpfInput.value || '',
        phone:customerPhoneInput.value || '',
        email:customerEmailInput.value || '',
        updated_at:new Date().toLocaleString('pt-BR')
    };
    localStorage.setItem(storageKey + '_customer', JSON.stringify(customer));
    alert('Cliente teste salvo.');
}
renderApp();
</script>
</body>
</html>


