/**
 * Product Form Logic: Calculations and Validations
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const costInput = document.querySelector('input[name="precio_compra"]');
    const priceInput = document.querySelector('input[name="precio_venta"]');
    const pvpInput = document.querySelector('input[name="pvp_unidad"]');
    const stockActualInput = document.querySelector('input[name="stock_actual"]');
    
    // Dashboard elements
    const marginVal = document.querySelector('.price-stat .val:nth-child(2)'); // We'll need better selectors
    const marginDisplay = document.querySelector('.price-stat:nth-child(1) .val');
    const profitDisplay = document.querySelector('.price-stat:nth-child(2) .val');
    const inventoryValDisplay = document.querySelector('.price-stat:nth-child(3) .val');
    const stockBadge = document.querySelector('.stock-badge');

    function calculateValues() {
        const cost = parseFloat(costInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const stock = parseFloat(stockActualInput.value) || 0;

        // Calculate Margin and Profit
        let margin = 0;
        let profit = 0;
        
        if (price > 0) {
            profit = price - cost;
            margin = (profit / price) * 100;
        }

        // Update Dashboard
        marginDisplay.textContent = margin.toFixed(2) + '%';
        marginDisplay.classList.toggle('danger', margin <= 0);
        
        profitDisplay.textContent = '$' + profit.toFixed(2);
        
        const totalInventory = stock * cost;
        inventoryValDisplay.textContent = '$' + totalInventory.toFixed(2);

        // Update Stock Badge
        if (stock <= 0) {
            stockBadge.textContent = 'Sin Stock';
            stockBadge.className = 'stock-badge danger';
        } else if (stock < 5) {
            stockBadge.textContent = 'Bajo';
            stockBadge.className = 'stock-badge warning';
        } else {
            stockBadge.textContent = 'Bueno';
            stockBadge.className = 'stock-badge success';
        }
    }

    [costInput, priceInput, stockActualInput].forEach(el => {
        if (el) el.addEventListener('input', calculateValues);
    });

    // Form submission via AJAX
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        
        // Basic frontend validation
        if (parseFloat(priceInput.value) < parseFloat(costInput.value)) {
            if (!confirm('El precio de venta es menor al costo. ¿Desea continuar?')) return;
        }

        try {
            const response = await fetch('save_product.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                alert('¡Éxito! ' + result.message);
                window.location.href = 'index.php';
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ocurrió un error al procesar la solicitud.');
        }
    });
});
