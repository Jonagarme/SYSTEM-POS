<?php
/**
 * New Stock Transfer Form - Nueva Transferencia
 */
session_start();
require_once '../../includes/db.php';

// Fetch locations for dropdowns
try {
    $ubicaciones = $pdo->query("SELECT id, nombre FROM inventario_ubicacion WHERE activo = 1 AND anulado = 0")->fetchAll();
} catch (PDOException $e) {
    $ubicaciones = [];
}

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_transfer') {
    header('Content-Type: application/json');
    try {
        $pdo->beginTransaction();

        $origen_id = $_POST['origen_id'];
        $destino_id = $_POST['destino_id'];
        $motivo = $_POST['motivo'];
        $observaciones = $_POST['observaciones'];
        $productos_json = $_POST['productos'];
        $productos = json_decode($productos_json, true);

        if (empty($productos)) {
            throw new Exception("Debe agregar al menos un producto.");
        }
        if ($origen_id == $destino_id) {
            throw new Exception("La ubicación origen y destino no pueden ser la misma.");
        }

        // Generate Document Number
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $prefix = "TR-$year$month$day-";
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventario_transferenciastock WHERE numero_transferencia LIKE :prefix");
        $stmt->execute([':prefix' => "$prefix%"]);
        $count = $stmt->fetchColumn() + 1;
        $numero_transferencia = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);

        // Insert Master
        $stmt = $pdo->prepare("INSERT INTO inventario_transferenciastock 
            (numero_transferencia, fecha_creacion, estado, tipo, observaciones, motivo, creadoDate, editadoDate, anulado, usuario_creacion_id, ubicacion_destino_id, ubicacion_origen_id) 
            VALUES (:num, NOW(), 'PENDIENTE', 'MANUAL', :obs, :mot, NOW(), NOW(), 0, :user, :dest, :orig)");

        $stmt->execute([
            ':num' => $numero_transferencia,
            ':obs' => $observaciones,
            ':mot' => $motivo,
            ':user' => $_SESSION['user_id'] ?? 1, // Fallback to 1 if not set
            ':dest' => $destino_id,
            ':orig' => $origen_id
        ]);

        $transfer_id = $pdo->lastInsertId();

        // Insert Details
        $stmt_det = $pdo->prepare("INSERT INTO inventario_detalletransferencia 
            (cantidad, cantidad_recibida, stock_origen_antes, stock_destino_antes, observaciones, producto_id, transferencia_id) 
            VALUES (:cant, 0, :stock_orig, 0, '', :prod_id, :trans_id)");

        foreach ($productos as $p) {
            // Get current stock at origin (mocking for now as we don't have stock per location table fully verified, but using products.stock for simplicity if it's GLOBAL)
            // The Django code mentioned StockUbicacion. 
            // In the provided SQL I haven't seen stock_ubicacion table yet, but I'll assume global stock or just 0 for now to not break the flow.

            $stmt_det->execute([
                ':cant' => $p['cantidad'],
                ':stock_orig' => 0, // Should fetch from StockUbicacion if exists
                ':prod_id' => $p['id'],
                ':trans_id' => $transfer_id
            ]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Transferencia creada con éxito', 'id' => $transfer_id]);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

$current_page = 'inventario_transferencias';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Transferencia | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
        }

        .nt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .nt-header h1 {
            font-size: 1.4rem;
            color: #1e293b;
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back-trans {
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

        .nt-panel {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin-bottom: 25px;
        }

        .nt-panel-header {
            background: var(--primary);
            color: white;
            padding: 12px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nt-panel-body {
            padding: 25px;
        }

        .form-grid-nt {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group-nt label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .btn-add-p-trans {
            background: white;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-add-p-trans:hover {
            background: #f8fafc;
            border-color: var(--primary);
            color: var(--primary);
        }

        .selected-products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .selected-products-table th {
            text-align: left;
            padding: 12px;
            background: #f8fafc;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .selected-products-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .nt-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-create-nt {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-create-nt:hover {
            background: var(--primary-hover);
        }

        .btn-cancel-nt {
            background: #64748b;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
        }

        .search-results-list {
            margin-top: 15px;
        }

        .search-item-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        .search-item-box:hover {
            background: #f8fafc;
        }

        .search-item-info h4 {
            font-size: 0.85rem;
            margin: 0 0 4px 0;
            color: #1e293b;
        }

        .search-item-info span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .stat-badge-s {
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-right: 5px;
        }

        .btn-add-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-input {
            width: 80px;
            padding: 5px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .nt-panel-body {
                padding: 15px;
            }

            .form-grid-nt {
                grid-template-columns: 1fr;
            }

            .nt-footer {
                flex-direction: column;
            }

            .nt-footer button,
            .nt-footer a {
                width: 100%;
                justify-content: center;
            }
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
                <div class="nt-header">
                    <h1><i class="fas fa-exchange-alt"></i> Nueva Transferencia</h1>
                    <a href="transferencias.php" class="btn-back-trans">
                        <i class="fas fa-arrow-left"></i> Volver a Lista
                    </a>
                </div>

                <form id="transfer-form">
                    <div class="nt-panel">
                        <div class="nt-panel-header">
                            <i class="fas fa-info-circle"></i> Información General
                        </div>
                        <div class="nt-panel-body">
                            <div class="form-grid-nt">
                                <div class="form-group-nt">
                                    <label>Ubicación Origen</label>
                                    <select name="origen_id" id="origen_id" class="form-control" required>
                                        <option value="">Seleccione origen...</option>
                                        <?php foreach ($ubicaciones as $u): ?>
                                            <option value="<?php echo $u['id']; ?>">
                                                <?php echo htmlspecialchars($u['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group-nt">
                                    <label>Ubicación Destino</label>
                                    <select name="destino_id" id="destino_id" class="form-control" required>
                                        <option value="">Seleccione destino...</option>
                                        <?php foreach ($ubicaciones as $u): ?>
                                            <option value="<?php echo $u['id']; ?>">
                                                <?php echo htmlspecialchars($u['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group-nt">
                                    <label>Motivo de Transferencia</label>
                                    <select name="motivo" class="form-control" required>
                                        <option value="">Seleccione un motivo...</option>
                                        <option value="Reposición de Stock">Reposición de Stock</option>
                                        <option value="Pedido de Sucursal">Pedido de Sucursal</option>
                                        <option value="Devolución a Bodega">Devolución a Bodega</option>
                                        <option value="Ajuste Operativo">Ajuste Operativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group-nt">
                                <label>Observaciones (Opcional)</label>
                                <textarea name="observaciones" class="form-control" rows="2"
                                    placeholder="Notas adicionales sobre esta transferencia..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="nt-panel">
                        <div class="nt-panel-header"
                            style="justify-content: space-between; display: flex; width: 100%;">
                            <span><i class="fas fa-box"></i> Productos a Transferir</span>
                            <button type="button" class="btn-add-p-trans" onclick="openProductModal()">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>
                        <div class="nt-panel-body" style="padding: 0;">
                            <table class="selected-products-table" id="selected-products-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th style="width: 150px;">Código</th>
                                        <th style="width: 120px; text-align: center;">Cantidad</th>
                                        <th style="width: 80px; text-align: center;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="products-list-body">
                                    <tr id="empty-row">
                                        <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                                            No se han agregado productos. Use el botón superior para buscar.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="nt-footer">
                        <a href="transferencias.php" class="btn-cancel-nt">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-create-nt">
                            <i class="fas fa-save"></i> Crear Transferencia
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- MODAL: AGREGAR PRODUCTO -->
    <div class="modal-overlay" id="product-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="font-size: 1.1rem; font-weight: 700; color: #1e293b;"><i class="fas fa-search"></i> Buscar
                    Producto</h2>
                <button type="button" class="btn-text" onclick="closeProductModal()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group-nt">
                    <input type="text" id="search-input" class="form-control"
                        placeholder="Escriba el nombre o código...">
                </div>
                <div class="search-results-list" id="search-results">
                    <!-- Results via Ajax -->
                </div>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        let selectedProducts = [];

        function openProductModal() {
            document.getElementById('product-modal').style.display = 'flex';
            document.getElementById('search-input').value = ''; // Clean search
            document.getElementById('search-input').focus();
            searchProducts(''); // Load initial products
        }

        function closeProductModal() {
            document.getElementById('product-modal').style.display = 'none';
        }

        // Product search trigger
        document.getElementById('search-input').addEventListener('input', function (e) {
            searchProducts(e.target.value);
        });

        function searchProducts(query) {
            const resultsDiv = document.getElementById('search-results');
            query = query.trim();

            resultsDiv.innerHTML = '<p style="text-align: center; padding: 20px; color: #64748b;"><i class="fas fa-spinner fa-spin"></i> Buscando...</p>';

            fetch(`ajax_buscar_productos.php?q=${encodeURIComponent(query)}`)
                .then(res => {
                    if (!res.ok) throw new Error('Error HTTP ' + res.status);
                    return res.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        let html = '';
                        if (data.error) {
                            html = `<div style="text-align: center; padding: 20px; color: #ef4444;"><b>Error:</b><br>${data.error}</div>`;
                        } else if (data.length === 0) {
                            html = '<p style="text-align: center; padding: 20px; color: #64748b;">No se encontraron resultados.</p>';
                        } else {
                            data.forEach(p => {
                                const safeNombre = p.nombre.replace(/'/g, "\\'").replace(/"/g, "&quot;");
                                html += `
                                    <div class="search-item-box">
                                        <div class="search-item-info">
                                            <h4 title="${safeNombre}">${p.nombre}</h4>
                                            <span><i class="fas fa-barcode"></i> ${p.barcode || 'N/A'}</span>
                                            <span class="stat-badge-s" style="margin-left: 10px;">Stock: ${parseFloat(p.stock).toFixed(2)}</span>
                                        </div>
                                        <button type="button" class="btn-add-circle" onclick="addProduct(${p.id}, '${safeNombre}', '${p.barcode || ''}')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                `;
                            });
                        }
                        resultsDiv.innerHTML = html;
                    } catch (e) {
                        resultsDiv.innerHTML = `<div style="text-align: center; padding: 20px; color: #ef4444;">Error técnico al leer datos.</div>`;
                    }
                })
                .catch(err => {
                    resultsDiv.innerHTML = `<p style="text-align: center; padding: 20px; color: #ef4444;">Error de conexión.</p>`;
                });
        }

        function addProduct(id, nombre, barcode) {
            if (selectedProducts.find(p => p.id === id)) {
                alert('El producto ya ha sido agregado.');
                return;
            }

            selectedProducts.push({ id, nombre, barcode, cantidad: 1 });
            renderProducts();
            closeProductModal();
        }

        function removeProduct(id) {
            selectedProducts = selectedProducts.filter(p => p.id !== id);
            renderProducts();
        }

        function updateQty(id, qty) {
            const prod = selectedProducts.find(p => p.id === id);
            if (prod) prod.cantidad = parseFloat(qty) || 0;
        }

        function renderProducts() {
            const tbody = document.getElementById('products-list-body');
            if (selectedProducts.length === 0) {
                tbody.innerHTML = `
                    <tr id="empty-row">
                        <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                            No se han agregado productos. Use el botón superior para buscar.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = selectedProducts.map(p => `
                <tr>
                    <td>${p.nombre}</td>
                    <td>${p.barcode || '-'}</td>
                    <td style="text-align: center;">
                        <input type="number" class="qty-input" value="${p.cantidad}" min="0.01" step="0.01" onchange="updateQty(${p.id}, this.value)">
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-text" style="color: #ef4444;" onclick="removeProduct(${p.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Form submission
        document.getElementById('transfer-form').addEventListener('submit', function (e) {
            e.preventDefault();

            if (selectedProducts.length === 0) {
                alert('Debe agregar al menos un producto.');
                return;
            }

            const formData = new FormData(this);
            formData.append('action', 'save_transfer');
            formData.append('productos', JSON.stringify(selectedProducts));

            const btn = this.querySelector('.btn-create-nt');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            fetch('nueva_transferencia.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = 'transferencias.php';
                    } else {
                        alert('Error: ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save"></i> Crear Transferencia';
                    }
                })
                .catch(err => {
                    alert('Ocurrió un error al procesar la solicitud.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Crear Transferencia';
                });
        });

        // Close on overlay click
        window.onclick = function (event) {
            if (event.target == document.getElementById('product-modal')) {
                closeProductModal();
            }
        }
    </script>
</body>

</html>