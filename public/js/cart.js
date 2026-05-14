/**
 * DELICACY - Carrinho de Compras (localStorage)
 * 
 * Gerencia carrinho sem backend, usando localStorage.
 * Integra com o formulário de pedido.
 */

const CART_KEY = 'delicacy_cart';

// ======================
// FUNÇÕES DO CARRINHO
// ======================

function getCart() {
    try {
        const data = localStorage.getItem(CART_KEY);
        return data ? JSON.parse(data) : [];
    } catch (e) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartUI();
}

function addToCart(itemId, itemName, itemPrice) {
    const cart = getCart();
    const existing = cart.find(item => item.id === itemId);

    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({
            id: itemId,
            name: itemName,
            price: parseFloat(itemPrice),
            quantity: 1,
            notes: ''
        });
    }

    saveCart(cart);

    // Feedback visual
    showAddedFeedback(itemId);
}

function removeFromCart(itemId) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== itemId);
    saveCart(cart);
}

function updateQuantity(itemId, delta) {
    const cart = getCart();
    const item = cart.find(i => i.id === itemId);

    if (!item) return;

    item.quantity += delta;

    if (item.quantity <= 0) {
        removeFromCart(itemId);
        return;
    }

    saveCart(cart);
}

function clearCart() {
    localStorage.removeItem(CART_KEY);
    updateCartUI();
}

function getCartTotal() {
    const cart = getCart();
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

function getCartCount() {
    const cart = getCart();
    return cart.reduce((count, item) => count + item.quantity, 0);
}

// ======================
// UI DO CARRINHO
// ======================

function updateCartUI() {
    const cart = getCart();
    const count = getCartCount();
    const total = getCartTotal();

    // Botão flutuante
    const cartFloat = document.getElementById('cartFloat');
    const cartCount = document.getElementById('cartCount');

    if (cartFloat) {
        cartFloat.style.display = count > 0 ? 'block' : 'none';
    }
    if (cartCount) {
        cartCount.textContent = count;
    }

    // Modal do carrinho
    renderCartItems();
}

function renderCartItems() {
    const cart = getCart();
    const cartItemsEl = document.getElementById('cartItems');
    const cartTotalSection = document.getElementById('cartTotalSection');
    const cartActions = document.getElementById('cartActions');
    const cartTotalEl = document.getElementById('cartTotal');

    if (!cartItemsEl) return;

    if (cart.length === 0) {
        cartItemsEl.innerHTML = '<div class="cart-empty"><p>🛒 Seu carrinho está vazio</p></div>';
        if (cartTotalSection) cartTotalSection.style.display = 'none';
        if (cartActions) cartActions.style.display = 'none';
        return;
    }

    let html = '';
    cart.forEach(item => {
        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <h4>${escapeHtml(item.name)}</h4>
                    <small>${formatPrice(item.price)} cada</small>
                </div>
                <div class="cart-item-controls">
                    <button onclick="updateQuantity(${item.id}, -1)" title="Remover">−</button>
                    <span class="cart-item-qty">${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, 1)" title="Adicionar">+</button>
                    <strong style="min-width:80px;text-align:right;">${formatPrice(item.price * item.quantity)}</strong>
                </div>
            </div>
        `;
    });

    cartItemsEl.innerHTML = html;

    if (cartTotalSection) {
        cartTotalSection.style.display = 'flex';
    }
    if (cartTotalEl) {
        cartTotalEl.textContent = formatPrice(getCartTotal());
    }
    if (cartActions) {
        cartActions.style.display = 'block';
    }
}

function openCart() {
    const overlay = document.getElementById('cartOverlay');
    if (overlay) {
        overlay.classList.add('active');
        renderCartItems();
    }
}

function closeCart() {
    const overlay = document.getElementById('cartOverlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// Fechar modal ao clicar fora
document.addEventListener('click', function(e) {
    const overlay = document.getElementById('cartOverlay');
    if (overlay && e.target === overlay) {
        closeCart();
    }
});

// ======================
// ENVIAR PEDIDO
// ======================

function submitOrder() {
    const cart = getCart();
    if (cart.length === 0) {
        alert('Seu carrinho está vazio!');
        return;
    }

    const name = document.getElementById('customer_name').value.trim();
    const phone = document.getElementById('customer_phone').value.trim();

    if (!name) {
        alert('Por favor, informe seu nome.');
        document.getElementById('customer_name').focus();
        return;
    }

    if (!phone) {
        alert('Por favor, informe seu WhatsApp.');
        document.getElementById('customer_phone').focus();
        return;
    }

    // Preenche o formulário oculto
    document.getElementById('cartDataInput').value = JSON.stringify(cart);
    document.getElementById('customerNameInput').value = name;
    document.getElementById('customerPhoneInput').value = phone;
    document.getElementById('deliveryAddressInput').value = 
        document.getElementById('delivery_address').value.trim();

    // Desabilita botão para evitar duplo clique
    const btn = document.getElementById('btnCheckout');
    if (btn) {
        btn.disabled = true;
        btn.textContent = '⏳ Enviando pedido...';
    }

    // Submete o formulário
    document.getElementById('orderSubmitForm').submit();
}

// ======================
// UTILITÁRIOS
// ======================

function formatPrice(value) {
    return 'R$ ' + parseFloat(value).toFixed(2).replace('.', ',');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function showAddedFeedback(itemId) {
    const menuItem = document.querySelector(`[data-item-id="${itemId}"]`);
    if (!menuItem) return;

    const btn = menuItem.querySelector('.btn-add-cart');
    if (!btn) return;

    const originalText = btn.textContent;
    btn.textContent = '✅ Adicionado!';
    btn.style.background = '#27ae60';

    setTimeout(() => {
        btn.textContent = originalText;
        btn.style.background = '';
    }, 1000);
}

// ======================
// INICIALIZAÇÃO
// ======================

document.addEventListener('DOMContentLoaded', function() {
    updateCartUI();
});
