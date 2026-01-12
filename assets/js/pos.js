/**
 * POS Logic - Manage carts, products and tabs
 */

document.addEventListener('DOMContentLoaded', () => {
    // State management
    const state = {
        activeTab: 1,
        tabs: {
            1: {
                cart: [],
                customer: 'CONSUMIDOR FINAL',
                paymentMethod: 'efectivo'
            }
        },
        products: [
            { id: 1, code: '7861011211051', name: 'AC LAC BABY LOCION FCO*200ML', price: 5.00, stock: 5, category: 'Salud' },
            { id: 2, code: '7861011200727', name: 'AC-LAC PH 3.5 LOCION FCO*200ML', price: 5.00, stock: 5, category: 'Salud' },
            { id: 3, code: '7803510003632', name: 'ACCUALAXAN 8.5G SOBRES CAJA X 7', price: 0.21, stock: 9, category: 'Salud' },
            { id: 4, code: '7862116271001', name: 'ACEITE CANIME FCOX30ML PAQX12 - GEAN LAB', price: 0.10, stock: 10, category: 'Salud' },
            { id: 5, code: '7862116270172', name: 'ACEITE COCO FCOX30ML PAQUETE X12 - GEAN', price: 0.01, stock: 46, category: 'Salud' },
            { id: 6, code: '7861001846058', name: 'ACEITE PARA MI BEBE MANZANILLA FCO*100ML', price: 2.00, stock: 3, category: 'Salud' }
        ]
    };

    // DOM Elements
    const productList = document.getElementById('product-list');
    const cartItemsContainer = document.getElementById('cart-items');
    const subtotalEl = document.getElementById('subtotal');
    const taxEl = document.getElementById('tax');
    const totalEl = document.getElementById('total');
    const productSearch = document.getElementById('product-search');
    const addTabBtn = document.getElementById('add-sale-tab');
    const tabsContainer = document.getElementById('pos-tabs');

    // Initialize
    renderProducts(state.products);
    updateCartUI();

    // Event Listeners
    productSearch.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const filtered = state.products.filter(p => 
            p.name.toLowerCase().includes(query) || 
            p.code.includes(query)
        );
        renderProducts(filtered);
    });

    addTabBtn.addEventListener('click', () => {
        const nextId = Math.max(...Object.keys(state.tabs).map(Number)) + 1;
        state.tabs[nextId] = {
            cart: [],
            customer: 'CONSUMIDOR FINAL',
            paymentMethod: 'efectivo'
        };
        
        // Create tab button
        const newTab = document.createElement('button');
        newTab.className = 'pos-tab';
        newTab.dataset.id = nextId;
        newTab.innerHTML = `Venta ${nextId} <i class="fas fa-times close-tab"></i>`;
        tabsContainer.insertBefore(newTab, addTabBtn);
        
        setActiveTab(nextId);
    });

    tabsContainer.addEventListener('click', (e) => {
        const tabBtn = e.target.closest('.pos-tab');
        if (tabBtn && !e.target.classList.contains('close-tab')) {
            setActiveTab(parseInt(tabBtn.dataset.id));
        } else if (e.target.classList.contains('close-tab')) {
            const tabId = parseInt(tabBtn.dataset.id);
            if (Object.keys(state.tabs).length > 1) {
                delete state.tabs[tabId];
                tabBtn.remove();
                if (state.activeTab === tabId) {
                    setActiveTab(parseInt(Object.keys(state.tabs)[0]));
                }
            }
        }
    });

    // Payment method selection
    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            state.tabs[state.activeTab].paymentMethod = this.dataset.method;
        });
    });

    // Functions
    function renderProducts(products) {
        productList.innerHTML = '';
        products.forEach(product => {
            const card = document.getElementById('product-card-template').content.cloneNode(true);
            const container = card.querySelector('.product-card');
            
            card.querySelector('.product-code').textContent = product.code;
            card.querySelector('.product-name').textContent = product.name;
            card.querySelector('.product-price').textContent = `$ ${product.price.toFixed(2)}`;
            card.querySelector('.product-stock').textContent = `Stock: ${product.stock}`;
            
            container.addEventListener('click', () => addToCart(product));
            productList.appendChild(card);
        });
    }

    function addToCart(product) {
        const currentCart = state.tabs[state.activeTab].cart;
        const existing = currentCart.find(item => item.id === product.id);
        
        if (existing) {
            existing.qty++;
        } else {
            currentCart.push({ ...product, qty: 1 });
        }
        
        updateCartUI();
    }

    function updateCartUI() {
        const currentCart = state.tabs[state.activeTab].cart;
        cartItemsContainer.innerHTML = '';
        
        if (currentCart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Carrito vacío</p>
                </div>
            `;
            updateTotals(0);
            return;
        }

        let runningSubtotal = 0;
        
        currentCart.forEach(item => {
            const itemTotal = item.price * item.qty;
            runningSubtotal += itemTotal;
            
            const itemRow = document.createElement('div');
            itemRow.className = 'cart-item-row';
            itemRow.style.display = 'flex';
            itemRow.style.padding = '10px';
            itemRow.style.borderBottom = '1px solid #eee';
            itemRow.style.fontSize = '0.85rem';
            
            itemRow.innerHTML = `
                <div style="flex: 1; font-weight: 500;">${item.name}</div>
                <div style="width: 50px; text-align: center;">
                    <input type="number" value="${item.qty}" min="1" style="width: 40px; text-align: center; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div style="width: 80px; text-align: right;">$ ${item.price.toFixed(2)}</div>
                <div style="width: 80px; text-align: right; font-weight: 600;">$ ${itemTotal.toFixed(2)}</div>
            `;
            
            // Handle quantity change
            const qtyInput = itemRow.querySelector('input');
            qtyInput.addEventListener('change', (e) => {
                const newQty = parseInt(e.target.value);
                if (newQty > 0) {
                    item.qty = newQty;
                    updateCartUI();
                }
            });
            
            cartItemsContainer.appendChild(itemRow);
        });

        updateTotals(runningSubtotal);
    }

    function updateTotals(subtotal) {
        const taxRate = 0.15;
        const taxAmount = subtotal * taxRate;
        const grandTotal = subtotal + taxAmount;

        subtotalEl.textContent = `$ ${subtotal.toFixed(2)}`;
        taxEl.textContent = `$ ${taxAmount.toFixed(2)}`;
        totalEl.textContent = `$ ${grandTotal.toFixed(2)}`;
    }

    function setActiveTab(id) {
        state.activeTab = id;
        document.querySelectorAll('.pos-tab').forEach(tab => {
            tab.classList.toggle('active', parseInt(tab.dataset.id) === id);
        });
        
        // Update payment method UI
        const currentMethod = state.tabs[id].paymentMethod;
        document.querySelectorAll('.payment-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.method === currentMethod);
        });
        
        updateCartUI();
    }
});
