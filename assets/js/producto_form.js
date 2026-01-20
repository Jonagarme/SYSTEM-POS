/**
 * Product Form Logic: Calculations and Validations
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    const costInput = document.querySelector('input[name="precio_compra"]');
    const priceInput = document.querySelector('input[name="precio_venta"]');
    const stockActualInput = document.querySelector('input[name="stock_actual"]');
    
    // Dashboard elements
    const marginDisplay = document.getElementById('display-margin');
    const profitDisplay = document.getElementById('display-profit');
    const inventoryValDisplay = document.getElementById('display-inventory');
    const stockBadge = document.getElementById('display-stock-status');

    function calculateValues() {
        if (!costInput || !priceInput || !stockActualInput) return;

        const cost = parseFloat(costInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const stock = parseFloat(stockActualInput.value) || 0;

        // Calculate Margin and Profit
        let margin = 0;
        let profit = 0;
        
        if (price > 0) {
            profit = price - cost;
            margin = (profit / price) * 100;
        } else if (cost > 0) {
            // Price is 0 but cost > 0 means 100% loss or just 0 margin
            profit = -cost;
            margin = -100;
        }

        // Update Dashboard
        if (marginDisplay) {
            marginDisplay.textContent = margin.toFixed(2) + '%';
            marginDisplay.className = margin <= 0 ? 'val danger' : 'val success';
        }
        
        if (profitDisplay) {
            profitDisplay.textContent = '$' + profit.toFixed(2);
            profitDisplay.className = profit < 0 ? 'val danger' : 'val';
        }
        
        if (inventoryValDisplay) {
            const totalInventory = stock * cost;
            inventoryValDisplay.textContent = '$' + totalInventory.toFixed(2);
        }

        // Update Stock Badge
        if (stockBadge) {
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
    }

    // Bind events
    [costInput, priceInput, stockActualInput].forEach(el => {
        if (el) el.addEventListener('input', calculateValues);
    });

    // Initial calculation for Edit mode
    calculateValues();

    // Form submission via AJAX
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            // Basic frontend validation
            if (parseFloat(priceInput.value) < parseFloat(costInput.value)) {
                if (!confirm('El precio de venta es menor al costo. ¿Desea continuar?')) return;
            }

            try {
                // Show loading state if needed
                const btnSave = form.querySelector('button[type="submit"]');
                const originalText = btnSave.innerHTML;
                btnSave.disabled = true;
                btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

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
                    btnSave.disabled = false;
                    btnSave.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ocurrió un error al procesar la solicitud.');
            }
        });
    }
});
