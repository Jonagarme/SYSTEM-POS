<?php
/**
 * New Inventory Adjustment - Nuevo Ajuste
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_ajustes';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Ajuste | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .section-panel {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .section-header {
            background: #fff;
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #2563eb;
        }

        .section-body {
            padding: 20px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .required {
            color: #ef4444;
        }

        .product-search-bar {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 60px;
            gap: 15px;
            align-items: flex-end;
        }

        @media (max-width: 1200px) {
            .product-search-bar {
                grid-template-columns: 1fr 1fr 1fr;
            }

            .btn-add-prod {
                grid-column: span 3;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .product-search-bar {
                grid-template-columns: 1fr 1fr;
            }

            .btn-add-prod {
                grid-column: span 2;
            }
        }

        @media (max-width: 480px) {
            .product-search-bar {
                grid-template-columns: 1fr;
            }

            .btn-add-prod {
                grid-column: span 1;
            }
        }

        .btn-add-prod {
            height: 42px;
            background: #198754;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ajuste-detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ajuste-detail-table th {
            text-align: left;
            padding: 12px 15px;
            background: #f8fafc;
            color: #1e293b;
            font-size: 0.8rem;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }

        .ajuste-detail-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #94a3b8;
        }

        .item-count-badge {
            background: #2563eb;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-left: auto;
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-save {
            background: #0d6efd;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Product Table in Modal */
        .modal-product-search {
            margin-bottom: 20px;
        }

        .product-results-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .product-results-table th {
            text-align: left;
            padding: 10px;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .product-results-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
        }

        .product-results-table tr:hover td {
            background: #f1f5f9;
        }

        .table-container-fixed {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .selected-product-info {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
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
                <div class="section-panel">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i> Información General
                    </div>
                    <div class="section-body">
                        <div class="form-grid-3">
                            <div>
                                <label class="form-label">Fecha</label>
                                <input type="text" class="form-control" value="11/01/2026" readonly
                                    style="background: #f8fafc;">
                            </div>
                            <div>
                                <label class="form-label">Tipo de Ajuste <span class="required">*</span></label>
                                <select class="form-control">
                                    <option>Seleccionar...</option>
                                    <option>Entrada</option>
                                    <option>Salida</option>
                                    <option>Corrección</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Motivo <span class="required">*</span></label>
                                <select class="form-control">
                                    <option>Seleccionar...</option>
                                    <option>Inventario Físico</option>
                                    <option>Corrección de Costo</option>
                                    <option>Daño/Mermas</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" placeholder="Nota opcional..."
                                    style="height: 42px; resize: none;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-panel">
                    <div class="section-header">
                        <i class="fas fa-box-open"></i> Agregar Productos
                    </div>
                    <div class="section-body">
                        <div id="selected-product-display" class="selected-product-info">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Producto seleccionado: <strong id="selected-prod-name">Ninguno</strong></span>
                                <button class="btn-text" onclick="clearSelectedProduct()"><i class="fas fa-times"></i>
                                    Cambiar</button>
                            </div>
                        </div>

                        <div class="product-search-bar">
                            <div class="search-container">
                                <label class="form-label">Producto <span class="required">*</span></label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" class="form-control"
                                        placeholder="Escriba para buscar o haga clic en la lupa..."
                                        id="prod-search-input" readonly onclick="openProductModal()">
                                    <button class="btn btn-secondary" style="padding: 0 15px;"
                                        onclick="openProductModal()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="selected-prod-id">
                            </div>
                            <div>
                                <label class="form-label">Stock Actual</label>
                                <input type="text" id="prod-stock-actual" class="form-control" readonly
                                    style="background: #f8fafc;">
                            </div>
                            <div>
                                <label class="form-label">Precio Actual</label>
                                <input type="text" id="prod-precio-actual" class="form-control" readonly
                                    style="background: #f8fafc;">
                            </div>
                            <div>
                                <label class="form-label">Nuevo Precio</label>
                                <input type="number" id="prod-nuevo-precio" class="form-control" step="0.01">
                            </div>
                            <div>
                                <label class="form-label">Nueva Cantidad</label>
                                <input type="number" id="prod-nueva-cantidad" class="form-control"
                                    oninput="calcDifference()">
                            </div>
                            <div>
                                <button class="btn-add-prod" onclick="addProductToTable()"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div
                            style="text-align: right; margin-top: 10px; font-size: 0.75rem; font-weight: 700; color: #64748b;">
                            Diferencia de Stock: <span id="stock-diff-label" style="color: #1e293b;">0</span>
                        </div>
                    </div>
                </div>

                <div class="section-panel">
                    <div class="section-header">
                        <i class="fas fa-list-ul"></i> Detalle del Ajuste
                        <span class="item-count-badge">0 items</span>
                    </div>
                    <div class="section-body" style="padding: 0;">
                        <table class="ajuste-detail-table" id="detalle-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Precio Act.</th>
                                    <th>P. Nuevo</th>
                                    <th>Stock Sist.</th>
                                    <th>Cant. Nueva</th>
                                    <th>Dif.</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="detalle-body">
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <i class="fas fa-arrow-up"
                                            style="display: block; font-size: 1.5rem; margin-bottom: 10px;"></i>
                                        Agregue productos para realizar el ajuste
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-footer">
                    <a href="ajustes.php" class="btn-cancel">Cancelar</a>
                    <button class="btn-save" onclick="saveAdjustment()"><i class="fas fa-save"></i> Guardar
                        Ajuste</button>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL PRODUCTOS -->
    <div class="modal-overlay" id="product-modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-search-plus"></i> Seleccionar Producto</h2>
                <button class="btn-text" onclick="closeProductModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-product-search">
                    <div class="input-with-icon">
                        <i class="fas fa-search"
                            style="position: absolute; left: 35px; top: 110px; color: #94a3b8;"></i>
                        <input type="text" id="modal-search-input" class="form-control"
                            placeholder="Buscar por nombre o código principal..." style="padding-left: 40px;"
                            oninput="searchProducts()">
                    </div>
                </div>
                <div class="table-container-fixed">
                    <table class="product-results-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Stock</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody id="product-results-body">
                            <!-- JS loaded -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeProductModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        let addedProducts = [];
        let selectedProduct = null;

        function openProductModal() {
            document.getElementById('product-modal').style.display = 'flex';
            document.getElementById('modal-search-input').focus();
            searchProducts(); // Load initial
        }

        function closeProductModal() {
            document.getElementById('product-modal').style.display = 'none';
        }

        function searchProducts() {
            const query = document.getElementById('modal-search-input').value;
            fetch(`ajax_buscar_productos.php?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    const body = document.getElementById('product-results-body');
                    body.innerHTML = '';

                    if (data.length === 0) {
                        body.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px;">No se encontraron productos</td></tr>';
                        return;
                    }

                    data.forEach(p => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${p.barcode || 'S/C'}</td>
                            <td>${p.nombre}</td>
                            <td>${p.stock}</td>
                            <td>$${parseFloat(p.price).toFixed(2)}</td>
                        `;
                        tr.onclick = () => selectProduct(p);
                        body.appendChild(tr);
                    });
                });
        }

        function selectProduct(p) {
            selectedProduct = p;
            document.getElementById('prod-search-input').value = p.nombre;
            document.getElementById('selected-prod-id').value = p.id;
            document.getElementById('prod-stock-actual').value = p.stock;
            document.getElementById('prod-precio-actual').value = parseFloat(p.price).toFixed(2);
            document.getElementById('prod-nuevo-precio').value = parseFloat(p.price).toFixed(2);
            document.getElementById('prod-nueva-cantidad').value = p.stock;

            document.getElementById('selected-prod-name').innerText = p.nombre;
            document.getElementById('selected-product-display').style.display = 'block';

            calcDifference();
            closeProductModal();
        }

        function clearSelectedProduct() {
            selectedProduct = null;
            document.getElementById('prod-search-input').value = '';
            document.getElementById('selected-prod-id').value = '';
            document.getElementById('prod-stock-actual').value = '';
            document.getElementById('prod-precio-actual').value = '';
            document.getElementById('prod-nuevo-precio').value = '';
            document.getElementById('prod-nueva-cantidad').value = '';
            document.getElementById('selected-product-display').style.display = 'none';
            document.getElementById('stock-diff-label').innerText = '0';
        }

        function calcDifference() {
            const stockActual = parseFloat(document.getElementById('prod-stock-actual').value) || 0;
            const nuevaCant = parseFloat(document.getElementById('prod-nueva-cantidad').value) || 0;
            const diff = nuevaCant - stockActual;

            const label = document.getElementById('stock-diff-label');
            label.innerText = (diff > 0 ? '+' : '') + diff;
            label.style.color = diff > 0 ? '#10b981' : (diff < 0 ? '#ef4444' : '#1e293b');
        }

        function addProductToTable() {
            if (!selectedProduct) {
                alert('Por favor seleccione un producto primero');
                return;
            }

            const id = selectedProduct.id;
            const nuevoPrecio = parseFloat(document.getElementById('prod-nuevo-precio').value) || 0;
            const nuevaCant = parseFloat(document.getElementById('prod-nueva-cantidad').value) || 0;
            const stockActual = parseFloat(selectedProduct.stock);
            const diff = nuevaCant - stockActual;

            // Check if already added
            const exists = addedProducts.find(p => p.id === id);
            if (exists) {
                alert('Este producto ya fue agregado al detalle');
                return;
            }

            const item = {
                id: id,
                barcode: selectedProduct.barcode,
                nombre: selectedProduct.nombre,
                precio_actual: parseFloat(selectedProduct.price),
                precio_nuevo: nuevoPrecio,
                stock_actual: stockActual,
                cantidad_nueva: nuevaCant,
                diferencia: diff
            };

            addedProducts.push(item);
            renderTable();
            clearSelectedProduct();
        }

        function renderTable() {
            const body = document.getElementById('detalle-body');
            const countBadge = document.querySelector('.item-count-badge');

            if (addedProducts.length === 0) {
                body.innerHTML = `<tr><td colspan="8" class="empty-state"><i class="fas fa-arrow-up" style="display: block; font-size: 1.5rem; margin-bottom: 10px;"></i>Agregue productos para realizar el ajuste</td></tr>`;
                countBadge.innerText = '0 items';
                return;
            }

            body.innerHTML = '';
            addedProducts.forEach((p, idx) => {
                const diffStyle = p.diferencia > 0 ? 'color: #10b981; font-weight: 700;' : (p.diferencia < 0 ? 'color: #ef4444; font-weight: 700;' : '');
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${p.barcode || 'S/C'}</td>
                    <td>${p.nombre}</td>
                    <td>$${p.precio_actual.toFixed(2)}</td>
                    <td>$${p.precio_nuevo.toFixed(2)}</td>
                    <td>${p.stock_actual}</td>
                    <td>${p.cantidad_nueva}</td>
                    <td style="${diffStyle}">${(p.diferencia > 0 ? '+' : '') + p.diferencia}</td>
                    <td style="text-align: center;">
                        <button class="btn-text text-danger" onclick="removeItem(${idx})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                body.appendChild(tr);
            });

            countBadge.innerText = `${addedProducts.length} items`;
        }

        function removeItem(idx) {
            addedProducts.splice(idx, 1);
            renderTable();
        }

        function saveAdjustment() {
            if (addedProducts.length === 0) {
                alert('Debe agregar al menos un producto');
                return;
            }

            // Implement save logic here
            console.log('Saving adjustment:', addedProducts);
            alert('Funcionalidad de guardado en preparación. Los datos del detalle se han procesado en el cliente.');
        }
    </script>
</body>

</html>