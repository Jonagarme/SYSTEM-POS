<?php
/**
 * Create New Return - Nueva Devolución
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'ventas_devoluciones';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Devolución | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .search-banner-green {
            background: #10b981;
            padding: 30px;
            border-radius: 12px;
            color: white;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-banner-green h2 {
            margin: 0;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-banner-green .search-box {
            display: flex;
            gap: 0;
            max-width: 700px;
            width: 100%;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 8px;
        }

        .search-banner-green input {
            flex: 1;
            background: transparent;
            border: none;
            color: white;
            padding: 10px 15px;
            font-size: 1rem;
            outline: none;
        }

        .search-banner-green input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-search-inner {
            background: #10b981;
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .return-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .return-section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0369a1;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .return-product-row {
            background: white;
            border-bottom: 1px solid #f1f5f9;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .product-main-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        .product-meta h4 {
            font-size: 0.85rem;
            margin: 0;
            color: #1e293b;
        }

        .product-meta span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .return-qty-controls {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item .label {
            display: block;
            font-size: 0.65rem;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .stat-item .value-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .stat-item .value-badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .return-input-box {
            text-align: right;
        }

        .return-input-box label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .return-input-box input {
            width: 80px;
            text-align: center;
            padding: 6px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .motivo-box {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 80px;
        }

        .footer-return-bar {
            position: fixed;
            bottom: 0;
            left: 260px;
            right: 0;
            background: white;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .warning-note {
            background: #fff8e1;
            color: #856404;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 60%;
        }

        .return-total-section {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .total-box-label {
            text-align: right;
        }

        .total-box-label span {
            display: block;
            font-size: 0.8rem;
            color: #64748b;
        }

        .total-box-label strong {
            font-size: 1.4rem;
            color: #c5221f;
        }

        .btn-confirm-return {
            background: #c5221f;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        body.sidebar-collapsed .footer-return-bar {
            left: 70px;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <?php
        $root = '../../';
        include $root . 'includes/sidebar.php';
        ?>

        <main class="main-content">
            <?php include $root . 'includes/navbar.php'; ?>

            <div class="content-wrapper">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1
                            style="font-size: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 10px; margin: 0;">
                            <i class="fas fa-plus-circle" style="color: #10b981;"></i> Crear Nueva Devolución
                        </h1>
                        <p style="color: #64748b; font-size: 0.85rem; margin-top: 5px;">Busque la factura y seleccione
                            los productos a devolver (MODO DEMO)</p>
                    </div>
                    <a href="devoluciones.php" class="btn btn-outline" style="padding: 10px 20px;">
                        <i class="fas fa-arrow-left"></i> Volver a Lista
                    </a>
                </div>

                <!-- Search Section -->
                <div class="search-banner-green">
                    <h2><i class="fas fa-search"></i> Buscar Factura para Devolver</h2>
                    <div class="search-box">
                        <input type="text" id="invoice-id"
                            placeholder="Ingrese el número de factura (ej: 001-001-000000017)"
                            value="">
                        <button class="btn-search-inner" onclick="loadInvoice()"><i class="fas fa-search"></i> Buscar
                            Factura</button>
                    </div>
                    <i class="fas fa-file-invoice" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>

                <div id="return-details" style="display: none;">
                    <div class="return-detail-grid">
                        <div class="pos-panel" style="padding: 20px;">
                            <h3 class="return-section-title"><i class="fas fa-info-circle"></i> Información de la
                                Factura</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.85rem;">
                                <div><strong style="color: #475569;">Número de Factura:</strong></div>
                                <div id="lbl-invoice-no">-</div>
                                <div><strong style="color: #475569;">Fecha de Venta:</strong></div>
                                <div id="lbl-invoice-date">-</div>
                                <div><strong style="color: #475569;">Total Original:</strong></div>
                                <div id="lbl-invoice-total" style="font-weight: 700;">$ 0.00</div>
                            </div>
                        </div>
                        <div class="pos-panel" style="padding: 20px;">
                            <h3 class="return-section-title"><i class="fas fa-user"></i> Información del Cliente</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.85rem;">
                                <div><strong style="color: #475569;">Nombre:</strong></div>
                                <div id="lbl-client-name">-</div>
                                <div><strong style="color: #475569;">Documento:</strong></div>
                                <div id="lbl-client-doc">-</div>
                            </div>
                        </div>
                    </div>

                    <h3 class="return-section-title" style="color: #1e293b;"><i class="fas fa-boxes"></i> Productos
                        Disponibles para Devolución</h3>
                    <div id="products-container"
                        style="background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
                        <!-- Products will be loaded here -->
                    </div>

                    <div class="motivo-box">
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                            <div>
                                <h3 class="return-section-title" style="color: #1e293b;"><i
                                        class="fas fa-comment-dots"></i> Motivo de la Devolución</h3>
                                <label style="display: block; font-size: 0.8rem; margin-bottom: 8px;">Motivo *</label>
                                <select class="form-control" id="return-reason">
                                    <option>Seleccione un motivo</option>
                                    <option>Producto Dañado</option>
                                    <option>Vencimiento Próximo</option>
                                    <option>Error de Compra</option>
                                    <option>Cambio de Opinión</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    style="display: block; font-size: 0.8rem; margin-bottom: 8px; font-weight: 700;">Observaciones</label>
                                <textarea class="form-control" id="return-obs" rows="3"
                                    placeholder="Detalles adicionales sobre la devolución (opcional)"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="footer-return-bar">
                        <div class="warning-note">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Importante: Los productos devueltos serán restituidos al inventario automáticamente.
                                Esta acción no se puede deshacer una vez procesada.</span>
                        </div>
                        <div class="return-total-section">
                            <div class="total-box-label">
                                <span>Total a Devolver</span>
                                <strong id="lbl-return-total">$ 0.00</strong>
                            </div>
                            <button class="btn-confirm-return" onclick="processReturn()">
                                <i class="fas fa-check"></i> PROCESAR DEVOLUCIÓN
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        let currentInvoice = null;
        let invoiceDetails = [];

        function loadInvoice() {
            const numero = document.getElementById('invoice-id').value;
            if (!numero) {
                Swal.fire('Error', 'Ingrese un número de factura', 'error');
                return;
            }

            const btn = document.querySelector('.btn-search-inner');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            btn.disabled = true;

            fetch(`ajax_get_invoice_by_number.php?numero=${numero}`)
                .then(response => response.json())
                .then(data => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;

                    if (data.success) {
                        currentInvoice = data.venta;
                        invoiceDetails = data.detalles;
                        renderInvoiceInfo(data.venta);
                        renderProductRows(data.detalles);
                        document.getElementById('return-details').style.display = 'block';
                        window.scrollTo({ top: 500, behavior: 'smooth' });
                        calculateTotal();
                    } else {
                        Swal.fire('No encontrado', data.error || 'No se encontró la factura', 'warning');
                        document.getElementById('return-details').style.display = 'none';
                    }
                })
                .catch(error => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    console.error('Error:', error);
                    Swal.fire('Error', 'Ocurrió un error al buscar la factura', 'error');
                });
        }

        function renderInvoiceInfo(venta) {
            document.getElementById('lbl-invoice-no').innerText = venta.numeroFactura;
            document.getElementById('lbl-invoice-date').innerText = venta.fechaEmision;
            document.getElementById('lbl-invoice-total').innerText = '$ ' + parseFloat(venta.total).toFixed(2);
            document.getElementById('lbl-client-name').innerText = venta.cliente_nombre || 'Consumidor Final';
            document.getElementById('lbl-client-doc').innerText = venta.cliente_ruc || '9999999999999';
        }

        function renderProductRows(detalles) {
            const container = document.getElementById('products-container');
            container.innerHTML = '';

            detalles.forEach((det, index) => {
                const row = document.createElement('div');
                row.className = 'return-product-row';
                row.innerHTML = `
                    <div class="product-main-info">
                        <input type="checkbox" id="chk-${index}" class="product-checkbox" onchange="calculateTotal()" style="width: 18px; height: 18px; cursor: pointer;">
                        <div class="product-meta">
                            <h4>${det.productoNombre}</h4>
                            <span>Código: ${det.codigo || 'N/A'} | Precio unitario: $ ${parseFloat(det.precioUnitario).toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="return-qty-controls">
                        <div class="stat-item">
                            <span class="label">Cant. Original</span>
                            <span class="value-badge">${parseFloat(det.cantidad).toFixed(2)}</span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Disponible</span>
                            <span class="value-badge-green value-badge">${parseFloat(det.cantidad).toFixed(2)}</span>
                        </div>
                        <div class="return-input-box">
                            <label>Devolver:</label>
                            <input type="number" id="qty-${index}" value="0" min="0" max="${det.cantidad}" step="1" onchange="validateQty(${index}); calculateTotal();" onkeyup="validateQty(${index}); calculateTotal();">
                            <div style="font-size: 0.7rem; color: #15803d; font-weight: 700; margin-top: 4px;">
                                Subtotal disponible: $ <span id="subtotal-${index}">0.00</span>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(row);
            });
        }

        function validateQty(index) {
            const input = document.getElementById(`qty-${index}`);
            const chk = document.getElementById(`chk-${index}`);
            const max = parseFloat(input.max);
            let val = parseFloat(input.value) || 0;

            if (val > max) {
                input.value = max;
                val = max;
            }
            if (val < 0) {
                input.value = 0;
                val = 0;
            }

            if (val > 0) {
                chk.checked = true;
            } else {
                chk.checked = false;
            }
        }

        function calculateTotal() {
            let total = 0;
            invoiceDetails.forEach((det, index) => {
                const chk = document.getElementById(`chk-${index}`);
                const qtyInput = document.getElementById(`qty-${index}`);
                const subtotalSpan = document.getElementById(`subtotal-${index}`);

                const qty = parseFloat(qtyInput.value) || 0;
                const subtotal = qty * parseFloat(det.precioUnitario);
                subtotalSpan.innerText = subtotal.toFixed(2);

                if (chk.checked) {
                    total += subtotal;
                }
            });

            document.getElementById('lbl-return-total').innerText = '$ ' + total.toFixed(2);
        }

        function processReturn() {
            const selectedItems = [];
            invoiceDetails.forEach((det, index) => {
                const chk = document.getElementById(`chk-${index}`);
                const qtyInput = document.getElementById(`qty-${index}`);
                const qty = parseFloat(qtyInput.value) || 0;

                if (chk.checked && qty > 0) {
                    selectedItems.push({
                        detalle_id: det.id,
                        producto_id: det.idProducto,
                        cantidad: qty,
                        nombre: det.productoNombre,
                        precio: det.precioUnitario
                    });
                }
            });

            if (selectedItems.length === 0) {
                Swal.fire('Atención', 'Seleccione al menos un producto con cantidad mayor a cero', 'info');
                return;
            }

            const motivo = document.getElementById('return-reason').value;
            if (!motivo || motivo === 'Seleccione un motivo') {
                Swal.fire('Atención', 'Seleccione el motivo de la devolución', 'info');
                return;
            }

            const observaciones = document.getElementById('return-obs').value;

            Swal.fire({
                title: '¿Confirmar Devolución?',
                text: `Se procesarán ${selectedItems.length} productos. Los productos volverán al inventario.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#c5221f',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, procesar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const payload = {
                        venta_id: currentInvoice.id,
                        motivo: motivo,
                        observaciones: observaciones,
                        items: selectedItems
                    };

                    fetch('ajax_guardar_devolucion.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    '¡Procesado!',
                                    'La devolución se ha registrado correctamente.',
                                    'success'
                                ).then(() => {
                                    window.location.href = 'devoluciones.php';
                                });
                            } else {
                                Swal.fire('Error', data.error || 'Error al guardar la devolución', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'Ocurrió un error al procesar la solicitud', 'error');
                        });
                }
            });
        }
    </script>
</body>

</html>