<?php
/**
 * New Purchase Form - Nueva Compra
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_compras';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Compra | Warehouse POS</title>
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

        .form-grid-nc {
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
            grid-template-columns: 2fr 1fr 1fr 1fr 60px;
            gap: 15px;
            align-items: flex-end;
        }

        @media (max-width: 992px) {
            .product-search-bar {
                grid-template-columns: 1fr 1fr;
            }

            .btn-add-prod {
                grid-column: span 2;
                width: 100%;
            }
        }

        @media (max-width: 576px) {
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

        .compra-detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .compra-detail-table th {
            text-align: left;
            padding: 12px 15px;
            background: #f8fafc;
            color: #1e293b;
            font-size: 0.8rem;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }

        .compra-detail-table td {
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
            justify-content: space-between;
            align-items: flex-start;
            gap: 30px;
            margin-top: 25px;
        }

        .totals-panel {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            min-width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .total-row.grand-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            font-weight: 800;
            font-size: 1.2rem;
            color: #1e293b;
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
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            justify-content: center;
            margin-top: 15px;
        }

        /* Modal Table styling */
        .table-container-fixed {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .results-table th {
            text-align: left;
            padding: 10px;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .results-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
        }

        .results-table tr:hover td {
            background: #f1f5f9;
        }

        .selected-info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
            font-size: 0.85rem;
            color: #1e40af;
        }

        .btn-text.danger {
            color: #ef4444;
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
                        <i class="fas fa-file-invoice"></i> Datos de la Compra
                    </div>
                    <div class="section-body">
                        <div class="form-grid-nc">
                            <div>
                                <label class="form-label">Proveedor <span class="required">*</span></label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="prov-nombre-display" class="form-control"
                                        placeholder="Seleccionar proveedor..." readonly onclick="openProviderModal()">
                                    <button class="btn btn-secondary" onclick="openProviderModal()"><i
                                            class="fas fa-search"></i></button>
                                </div>
                                <input type="hidden" id="selected-prov-id">
                            </div>
                            <div>
                                <label class="form-label">No. Factura Proveedor</label>
                                <input type="text" id="compra-factura" class="form-control"
                                    placeholder="Ej: 001-001-0000123">
                            </div>
                            <div>
                                <label class="form-label">Fecha Factura <span class="required">*</span></label>
                                <input type="date" id="compra-fecha" class="form-control"
                                    value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div>
                                <label class="form-label">Tipo de Pago</label>
                                <select id="compra-pago" class="form-control">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Crédito">Crédito</option>
                                    <option value="Transferencia">Transferencia</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-panel">
                    <div class="section-header">
                        <i class="fas fa-box-open"></i> Agregar Productos
                    </div>
                    <div class="section-body">
                        <div id="selected-prod-box" class="selected-info-box">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Producto: <strong id="selected-prod-name">Ninguno</strong></span>
                                <button class="btn-text" onclick="clearSelectedProduct()"><i class="fas fa-times"></i>
                                    Cambiar</button>
                            </div>
                        </div>
                        <div class="product-search-bar">
                            <div>
                                <label class="form-label">Producto <span class="required">*</span></label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="prod-nombre-display" class="form-control"
                                        placeholder="Buscar producto..." readonly onclick="openProductModal()">
                                    <button class="btn btn-secondary" onclick="openProductModal()"><i
                                            class="fas fa-search"></i></button>
                                </div>
                                <input type="hidden" id="selected-prod-id">
                            </div>
                            <div>
                                <label class="form-label">Costo Unitario <span class="required">*</span></label>
                                <input type="number" id="prod-costo" class="form-control" step="0.01"
                                    placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Cantidad <span class="required">*</span></label>
                                <input type="number" id="prod-cant" class="form-control" placeholder="0">
                            </div>
                            <div>
                                <label class="form-label">P. Venta Sugerido</label>
                                <input type="number" id="prod-pventa" class="form-control" step="0.01"
                                    placeholder="0.00">
                            </div>
                            <div>
                                <button class="btn-add-prod" onclick="addProductToTable()"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-panel">
                    <div class="section-header">
                        <i class="fas fa-list-ul"></i> Detalle de la Compra
                        <span class="item-count-badge" id="item-count">0 items</span>
                    </div>
                    <div class="section-body" style="padding: 0;">
                        <table class="compra-detail-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Costo Unit.</th>
                                    <th>Cant.</th>
                                    <th>Total</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="detalle-body">
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-shopping-basket"
                                            style="display: block; font-size: 1.5rem; margin-bottom: 10px;"></i>
                                        Agregue productos para procesar la compra
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-footer">
                    <div>
                        <a href="compras.php" class="btn-cancel">Cancelar</a>
                    </div>
                    <div class="totals-panel">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span id="subtotal-val">$ 0.00</span>
                        </div>
                        <div class="total-row">
                            <span>IVA (15%):</span>
                            <span id="iva-val">$ 0.00</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total Compra:</span>
                            <span id="total-val">$ 0.00</span>
                        </div>
                        <button class="btn-save" onclick="savePurchase()">
                            <i class="fas fa-save"></i> Procesar Compra
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL PROVEEDORES -->
    <div class="modal-overlay" id="provider-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-truck"></i> Seleccionar Proveedor</h2>
                <button class="btn-text" onclick="closeProviderModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <input type="text" id="prov-search-input" class="form-control"
                    placeholder="Buscar por RUC o Razón Social..." oninput="searchProviders()">
                <div class="table-container-fixed" style="margin-top: 15px;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>RUC</th>
                                <th>Razón Social / Nombre</th>
                            </tr>
                        </thead>
                        <tbody id="prov-results-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PRODUCTOS -->
    <div class="modal-overlay" id="product-modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-search-plus"></i> Seleccionar Producto</h2>
                <button class="btn-text" onclick="closeProductModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <input type="text" id="prod-search-input-modal" class="form-control"
                    placeholder="Buscar por nombre o código..." oninput="searchProducts()">
                <div class="table-container-fixed" style="margin-top: 15px;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Stock</th>
                                <th>Costo Act.</th>
                            </tr>
                        </thead>
                        <tbody id="prod-results-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        let addedProducts = [];
        let selectedProduct = null;
        let selectedProvider = null;

        // Provider Functions
        function openProviderModal() {
            document.getElementById('provider-modal').style.display = 'flex';
            document.getElementById('prov-search-input').focus();
            searchProviders();
        }

        function closeProviderModal() {
            document.getElementById('provider-modal').style.display = 'none';
        }

        function searchProviders() {
            const q = document.getElementById('prov-search-input').value;
            fetch(`ajax_buscar_proveedores.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    const body = document.getElementById('prov-results-body');
                    body.innerHTML = '';
                    data.forEach(p => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${p.ruc}</td><td>${p.razonSocial || p.nombreComercial}</td>`;
                        tr.onclick = () => selectProvider(p);
                        body.appendChild(tr);
                    });
                });
        }

        function selectProvider(p) {
            selectedProvider = p;
            document.getElementById('prov-nombre-display').value = p.razonSocial || p.nombreComercial;
            document.getElementById('selected-prov-id').value = p.id;
            closeProviderModal();
        }

        // Product Functions
        function openProductModal() {
            document.getElementById('product-modal').style.display = 'flex';
            document.getElementById('prod-search-input-modal').focus();
            searchProducts();
        }

        function closeProductModal() {
            document.getElementById('product-modal').style.display = 'none';
        }

        function searchProducts() {
            const q = document.getElementById('prod-search-input-modal').value;
            fetch(`ajax_buscar_productos.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    const body = document.getElementById('prod-results-body');
                    body.innerHTML = '';
                    data.forEach(p => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${p.barcode || 'S/C'}</td>
                            <td>${p.nombre}</td>
                            <td>${p.stock}</td>
                            <td>$${parseFloat(p.price * 0.7).toFixed(2)}</td>
                        `;
                        tr.onclick = () => selectProduct(p);
                        body.appendChild(tr);
                    });
                });
        }

        function selectProduct(p) {
            selectedProduct = p;
            document.getElementById('prod-nombre-display').value = p.nombre;
            document.getElementById('selected-prod-id').value = p.id;
            document.getElementById('prod-pventa').value = parseFloat(p.price).toFixed(2);
            document.getElementById('prod-costo').focus();

            document.getElementById('selected-prod-name').innerText = p.nombre;
            document.getElementById('selected-prod-box').style.display = 'block';
            closeProductModal();
        }

        function clearSelectedProduct() {
            selectedProduct = null;
            document.getElementById('prod-nombre-display').value = '';
            document.getElementById('selected-prod-id').value = '';
            document.getElementById('prod-costo').value = '';
            document.getElementById('prod-cant').value = '';
            document.getElementById('prod-pventa').value = '';
            document.getElementById('selected-prod-box').style.display = 'none';
        }

        function addProductToTable() {
            if (!selectedProduct) { alert('Seleccione un producto'); return; }
            const costo = parseFloat(document.getElementById('prod-costo').value);
            const cant = parseFloat(document.getElementById('prod-cant').value);

            if (isNaN(costo) || costo <= 0 || isNaN(cant) || cant <= 0) {
                alert('Costo y cantidad deben ser mayores a 0');
                return;
            }

            const item = {
                id: selectedProduct.id,
                barcode: selectedProduct.barcode,
                nombre: selectedProduct.nombre,
                costo: costo,
                cant: cant,
                p_venta: parseFloat(document.getElementById('prod-pventa').value) || 0,
                total: costo * cant
            };

            addedProducts.push(item);
            renderTable();
            clearSelectedProduct();
        }

        function renderTable() {
            const body = document.getElementById('detalle-body');
            body.innerHTML = '';
            let subtotal = 0;

            if (addedProducts.length === 0) {
                body.innerHTML = '<tr><td colspan="6" class="empty-state">Agregue productos...</td></tr>';
            } else {
                addedProducts.forEach((p, i) => {
                    subtotal += p.total;
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${p.barcode || 'S/C'}</td>
                        <td>${p.nombre}</td>
                        <td>$${p.costo.toFixed(2)}</td>
                        <td>${p.cant}</td>
                        <td>$${p.total.toFixed(2)}</td>
                        <td style="text-align: center;">
                            <button class="btn-text danger" onclick="removeItem(${i})"><i class="fas fa-trash"></i></button>
                        </td>
                    `;
                    body.appendChild(tr);
                });
            }

            const iva = subtotal * 0.15;
            const total = subtotal + iva;

            document.getElementById('subtotal-val').innerText = `$ ${subtotal.toFixed(2)}`;
            document.getElementById('iva-val').innerText = `$ ${iva.toFixed(2)}`;
            document.getElementById('total-val').innerText = `$ ${total.toFixed(2)}`;
            document.getElementById('item-count').innerText = `${addedProducts.length} items`;
        }

        function removeItem(i) {
            addedProducts.splice(i, 1);
            renderTable();
        }

        function savePurchase() {
            if (!selectedProvider) { alert('Debe seleccionar un proveedor'); return; }
            if (addedProducts.length === 0) { alert('Agregue productos a la compra'); return; }

            alert('Funcionalidad de guardado en preparación. Se procesará factura: ' + document.getElementById('compra-factura').value);
            console.log('Purchase data:', {
                provider: selectedProvider,
                details: addedProducts,
                total: document.getElementById('total-val').innerText
            });
        }
    </script>
</body>

</html>