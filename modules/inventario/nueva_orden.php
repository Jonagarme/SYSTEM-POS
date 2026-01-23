<?php
/**
 * New Purchase Order Form - Nueva Orden de Compra
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_ordenes';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Orden de Compra | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .no-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .no-title h1 {
            font-size: 1.4rem;
            color: #1e293b;
            margin: 0;
            font-weight: 700;
        }

        .no-breadcrumb {
            display: flex;
            gap: 5px;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 5px;
        }

        .no-breadcrumb a {
            color: #2563eb;
        }

        .btn-back-no {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-panel {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .section-header {
            padding: 12px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bg-blue {
            background: #2563eb;
        }

        .bg-green {
            background: #198754;
        }

        .section-body {
            padding: 20px;
        }

        .form-grid-no {
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

        .product-add-bar {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 60px;
            gap: 15px;
            align-items: flex-end;
        }

        @media (max-width: 768px) {
            .product-add-bar {
                grid-template-columns: 1fr;
            }

            .btn-add-prod {
                width: 100%;
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

        .order-table-container {
            overflow-x: auto;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .order-table th {
            text-align: left;
            padding: 12px 15px;
            background: #f8fafc;
            color: #1e293b;
            font-size: 0.8rem;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }

        .order-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #94a3b8;
        }

        .no-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .info-note {
            background: #e0f2fe;
            color: #0369a1;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 0.8rem;
            max-width: 500px;
        }

        .total-panel {
            background: #22c55e;
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: right;
            min-width: 250px;
            box-shadow: var(--shadow-sm);
        }

        .total-panel .val {
            font-size: 1.8rem;
            font-weight: 800;
            display: block;
        }

        .total-panel .lbl {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .btn-save-order {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Modal Styles */
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

        .table-container-fixed {
            max-height: 350px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .selected-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
            font-size: 0.85rem;
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
                <div class="no-header">
                    <div class="no-title">
                        <h1>Nueva Orden de Compra</h1>
                        <div class="no-breadcrumb">
                            <a href="../../index.php">Dashboard</a> / <a href="ordenes_compra.php">Órdenes de Compra</a>
                            / <span>Nueva Orden</span>
                        </div>
                    </div>
                    <a href="ordenes_compra.php" class="btn-back-no">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <!-- Info Section -->
                <div class="section-panel">
                    <div class="section-header bg-blue">
                        <span><i class="fas fa-info-circle"></i> Información de la Orden</span>
                    </div>
                    <div class="section-body">
                        <div class="form-grid-no">
                            <div>
                                <label class="form-label">Proveedor <span class="required">*</span></label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="prov-display" class="form-control"
                                        placeholder="Seleccionar..." readonly onclick="openProviderModal()">
                                    <button class="btn btn-secondary" onclick="openProviderModal()"><i
                                            class="fas fa-search"></i></button>
                                </div>
                                <input type="hidden" id="selected-prov-id">
                            </div>
                            <div>
                                <label class="form-label">Prioridad</label>
                                <select id="order-priority" class="form-control">
                                    <option value="Normal">Normal</option>
                                    <option value="Urgente">Urgente</option>
                                    <option value="Baja">Baja</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Ubicación Destino <span class="required">*</span></label>
                                <select id="order-location" class="form-control">
                                    <option value="">Seleccione...</option>
                                    <?php
                                    try {
                                        $stmt_ub = $pdo->query("SELECT id, nombre FROM inventario_ubicacion WHERE activo = 1 AND anulado = 0");
                                        while ($u = $stmt_ub->fetch()) {
                                            echo "<option value='" . $u['id'] . "'>" . htmlspecialchars($u['nombre']) . "</option>";
                                        }
                                    } catch (Exception $e) {
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Fecha Esperada</label>
                                <input type="date" id="order-date" class="form-control"
                                    value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                            </div>
                            <div>
                                <label class="form-label">Observaciones</label>
                                <textarea id="order-obs" class="form-control" rows="1"
                                    placeholder="Opcional..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Add Section -->
                <div class="section-panel">
                    <div class="section-header bg-green">
                        <span><i class="fas fa-boxes"></i> Agregar Productos</span>
                    </div>
                    <div class="section-body">
                        <div id="selected-prod-box" class="selected-box">
                            <span>Producto: <strong id="selected-prod-name">Ninguno</strong></span>
                            <button class="btn-text" onclick="clearProduct()" style="float: right;"><i
                                    class="fas fa-times"></i></button>
                        </div>
                        <div class="product-add-bar">
                            <div>
                                <label class="form-label">Producto <span class="required">*</span></label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="prod-display" class="form-control" placeholder="Buscar..."
                                        readonly onclick="openProductModal()">
                                    <button class="btn btn-secondary" onclick="openProductModal()"><i
                                            class="fas fa-search"></i></button>
                                </div>
                                <input type="hidden" id="selected-prod-id">
                            </div>
                            <div>
                                <label class="form-label">Costo Est. ($)</label>
                                <input type="number" id="prod-costo" class="form-control" step="0.01"
                                    placeholder="0.00">
                            </div>
                            <div>
                                <label class="form-label">Cantidad</label>
                                <input type="number" id="prod-cant" class="form-control" placeholder="0">
                            </div>
                            <div>
                                <button class="btn-add-prod" onclick="addProduct()"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="section-panel">
                    <div class="section-header" style="background: #475569;">
                        <span><i class="fas fa-list"></i> Detalle de Orden</span>
                        <span id="item-count" style="font-size: 0.75rem;">0 productos</span>
                    </div>
                    <div class="section-body" style="padding: 0;">
                        <div class="order-table-container">
                            <table class="order-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Costo Est.</th>
                                        <th>Cant.</th>
                                        <th>Subtotal</th>
                                        <th style="text-align: center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="order-body">
                                    <tr>
                                        <td colspan="6" class="empty-state">No hay productos en la orden</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="no-footer">
                    <div class="info-note">
                        <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Esta orden se guardará como borrador.
                        Los costos son estimados y pueden variar al recibir la factura real de compra.
                    </div>
                    <div class="total-area">
                        <div class="total-panel">
                            <div class="lbl">Total Estimado</div>
                            <div class="val" id="total-val">$ 0.00</div>
                        </div>
                        <button class="btn-save-order" onclick="saveOrder()">
                            <i class="fas fa-save"></i> Guardar Orden
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL PROVEEDORES -->
    <div class="modal-overlay" id="prov-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Seleccionar Proveedor</h2>
                <button class="btn-text" onclick="closeProviderModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <input type="text" id="prov-search" class="form-control" placeholder="Buscar RUC o Nombre..."
                    oninput="searchProviders()">
                <div class="table-container-fixed" style="margin-top: 15px;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>RUC</th>
                                <th>Proveedor</th>
                            </tr>
                        </thead>
                        <tbody id="prov-results"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PRODUCTOS -->
    <div class="modal-overlay" id="prod-modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2>Seleccionar Producto</h2>
                <button class="btn-text" onclick="closeProductModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <input type="text" id="prod-search" class="form-control" placeholder="Buscar nombre o código..."
                    oninput="searchProducts()">
                <div class="table-container-fixed" style="margin-top: 15px;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>P. Venta</th>
                            </tr>
                        </thead>
                        <tbody id="prod-results"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        let items = [];
        let selProd = null;
        let selProv = null;

        // Providers
        function openProviderModal() {
            document.getElementById('prov-modal').style.display = 'flex';
            document.getElementById('prov-search').focus();
            searchProviders();
        }
        function closeProviderModal() { document.getElementById('prov-modal').style.display = 'none'; }

        function searchProviders() {
            const q = document.getElementById('prov-search').value;
            fetch(`ajax_buscar_proveedores.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    const b = document.getElementById('prov-results'); b.innerHTML = '';
                    data.forEach(p => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${p.ruc}</td><td>${p.razonSocial || p.nombreComercial}</td>`;
                        tr.onclick = () => {
                            selProv = p;
                            document.getElementById('prov-display').value = p.razonSocial || p.nombreComercial;
                            document.getElementById('selected-prov-id').value = p.id;
                            closeProviderModal();
                        };
                        b.appendChild(tr);
                    });
                });
        }

        // Products
        function openProductModal() {
            document.getElementById('prod-modal').style.display = 'flex';
            document.getElementById('prod-search').focus();
            searchProducts();
        }
        function closeProductModal() { document.getElementById('prod-modal').style.display = 'none'; }

        function searchProducts() {
            const q = document.getElementById('prod-search').value;
            fetch(`ajax_buscar_productos.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    const b = document.getElementById('prod-results'); b.innerHTML = '';
                    data.forEach(p => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${p.barcode || 'S/C'}</td><td>${p.nombre}</td><td>${p.stock}</td><td>$${parseFloat(p.price).toFixed(2)}</td>`;
                        tr.onclick = () => {
                            selProd = p;
                            document.getElementById('prod-display').value = p.nombre;
                            document.getElementById('selected-prod-id').value = p.id;
                            document.getElementById('prod-costo').value = (p.price * 0.75).toFixed(2);
                            document.getElementById('selected-prod-name').innerText = p.nombre;
                            document.getElementById('selected-prod-box').style.display = 'block';
                            closeProductModal();
                            document.getElementById('prod-costo').focus();
                        };
                        b.appendChild(tr);
                    });
                });
        }

        function clearProduct() {
            selProd = null;
            document.getElementById('prod-display').value = '';
            document.getElementById('selected-prod-id').value = '';
            document.getElementById('prod-costo').value = '';
            document.getElementById('prod-cant').value = '';
            document.getElementById('selected-prod-box').style.display = 'none';
        }

        function addProduct() {
            if (!selProd) { alert('Seleccione un producto'); return; }
            const costo = parseFloat(document.getElementById('prod-costo').value) || 0;
            const cant = parseFloat(document.getElementById('prod-cant').value) || 0;
            if (costo <= 0 || cant <= 0) { alert('Verifique costo y cantidad'); return; }

            items.push({
                id: selProd.id,
                barcode: selProd.barcode,
                nombre: selProd.nombre,
                costo: costo,
                cant: cant,
                total: costo * cant
            });

            renderTable();
            clearProduct();
        }

        function renderTable() {
            const b = document.getElementById('order-body'); b.innerHTML = '';
            let total = 0;
            if (items.length === 0) {
                b.innerHTML = '<tr><td colspan="6" class="empty-state">No hay productos</td></tr>';
            } else {
                items.forEach((it, i) => {
                    total += it.total;
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${it.barcode || 'S/C'}</td><td>${it.nombre}</td><td>$${it.costo.toFixed(2)}</td><td>${it.cant}</td><td>$${it.total.toFixed(2)}</td><td style="text-align:center;"><button class="btn-text danger" onclick="removeItem(${i})"><i class="fas fa-trash"></i></button></td>`;
                    b.appendChild(tr);
                });
            }
            document.getElementById('total-val').innerText = `$ ${total.toFixed(2)}`;
            document.getElementById('item-count').innerText = `${items.length} productos`;
        }

        function removeItem(i) { items.splice(i, 1); renderTable(); }

        function saveOrder() {
            if (!selProv) { alert('Seleccione un proveedor'); return; }
            if (items.length === 0) { alert('Agregue productos'); return; }
            alert('Orden guardada exitosamente (Simulación)');
            console.log({ provider: selProv, items: items });
        }
    </script>
</body>

</html>