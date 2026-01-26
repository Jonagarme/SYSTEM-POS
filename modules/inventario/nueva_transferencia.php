<?php
/**
 * New Stock Transfer Form - Nueva Transferencia
 */
session_start();
$root = '../../';
require_once $root . 'includes/db.php';

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

        // Ensure a valid user_id for foreign key constraints
        $creador_id = $_SESSION['user_id'] ?? null;

        // Check if session user_id actually exists in usuarios
        if ($creador_id) {
            try {
                $check_u = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND activo = 1");
                $check_u->execute([$creador_id]);
                if (!$check_u->fetch()) {
                    $creador_id = null;
                }
            } catch (Exception $ex) {
                $creador_id = null;
            }
        }

        if (!$creador_id) {
            // Fallback: get the first available active user in usuarios
            try {
                $stmt_u = $pdo->query("SELECT id FROM usuarios WHERE activo = 1 ORDER BY id ASC LIMIT 1");
                $creador_id = $stmt_u->fetchColumn();
            } catch (Exception $ex) {
                throw new Exception("Error al buscar usuario en la tabla 'usuarios': " . $ex->getMessage());
            }
        }

        if (!$creador_id) {
            throw new Exception("No existe ningún usuario activo en la tabla 'usuarios'. No se puede registrar la transferencia.");
        }

        // Insert Master
        $stmt = $pdo->prepare("INSERT INTO inventario_transferenciastock 
            (numero_transferencia, fecha_creacion, estado, tipo, observaciones, motivo, 
             creadoDate, editadoDate, anulado, creadoPor_id, usuario_creacion_id, 
             ubicacion_destino_id, ubicacion_origen_id) 
            VALUES (:num, NOW(), 'PENDIENTE', 'MANUAL', :obs, :mot, 
             NOW(), NOW(), 0, :u_cp, :u_cc, :dest, :orig)");

        $stmt->execute([
            ':num' => $numero_transferencia,
            ':obs' => $observaciones,
            ':mot' => $motivo,
            ':u_cp' => $creador_id,
            ':u_cc' => $creador_id,
            ':dest' => $destino_id,
            ':orig' => $origen_id
        ]);

        $transfer_id = $pdo->lastInsertId();

        // Insert Details
        $stmt_det = $pdo->prepare("INSERT INTO inventario_detalletransferencia 
            (cantidad, cantidad_recibida, stock_origen_antes, stock_destino_antes, observaciones, 
             producto_id, transferencia_id, lote_id, precio_origen, precio_destino, cambio_precio,
             cantidad_cajas, cantidad_fracciones, unidades_por_caja) 
            VALUES (:cant, 0, :stock_orig, 0, '', :prod_id, :trans_id, :lote_id, :p_orig, :p_dest, :c_precio, :c_cajas, :c_frac, :u_caja)");

        foreach ($productos as $p) {
            $stmt_det->execute([
                ':cant' => $p['cantidad'],
                ':stock_orig' => 0, // Mock for now
                ':prod_id' => $p['id'],
                ':trans_id' => $transfer_id,
                ':lote_id' => $p['lote_id'],
                ':p_orig' => $p['precio_origen'] ?? 0,
                ':p_dest' => $p['nuevo_precio'] ?? 0,
                ':c_precio' => $p['cambia_precio'] ? 1 : 0,
                ':c_cajas' => $p['cantidad_cajas'] ?? 0,
                ':c_frac' => $p['cantidad_fracciones'] ?? 0,
                ':u_caja' => $p['unidades_por_caja'] ?? 1
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

    <!-- MODAL: CONFIGURAR PRODUCTO PARA TRANSFERENCIA (MULTI-PASO) -->
    <div class="modal-overlay" id="product-modal">
        <div class="modal-content" style="max-width: 850px;">
            <div class="modal-header" style="background: #2563eb; color: white;">
                <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0;">
                    <i class="fas fa-edit"></i> Configurar Producto para Transferencia
                </h2>
                <button type="button" class="btn-text" style="color: white; font-size: 1.2rem;"
                    onclick="closeProductModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <!-- Product Header Info -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                    <div class="form-group-nt">
                        <label>Producto</label>
                        <input type="text" id="modal-product-name" class="form-control" readonly
                            style="background: #f8fafc;">
                    </div>
                    <div class="form-group-nt">
                        <label>Stock Total Disponible</label>
                        <input type="text" id="modal-product-stock" class="form-control" readonly
                            style="background: #f8fafc;">
                    </div>
                </div>

                <!-- STEP 1: SELECT BATCH -->
                <div class="step-container" id="step-1">
                    <div class="step-header">
                        <i class="fas fa-boxes"></i> PASO 1: Seleccionar Lote y Caducidad
                    </div>
                    <div class="step-body">
                        <button type="button" class="btn-search-lotes" id="btn-load-lotes" onclick="loadLotes()">
                            <i class="fas fa-search"></i> Buscar Lotes Disponibles
                        </button>

                        <div id="lotes-container" style="display: none; margin-top: 15px;">
                            <table class="lotes-table">
                                <thead>
                                    <tr>
                                        <th>Selec.</th>
                                        <th>Lote</th>
                                        <th>Caducidad</th>
                                        <th>Días</th>
                                        <th>Stock Disponible</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="lotes-list-body">
                                    <!-- Lotes via AJAX -->
                                </tbody>
                            </table>
                        </div>

                        <div id="selected-lote-summary" class="selected-summary" style="display: none;">
                            <!-- Summary of selection -->
                        </div>
                    </div>
                </div>

                <!-- STEP 2: DEFINE QUANTITY -->
                <div class="step-container" id="step-2" style="display: none; margin-top: 20px;">
                    <div class="step-header">
                        <i class="fas fa-calculator"></i> PASO 2: Definir Cantidad a Transferir
                    </div>
                    <div class="step-body">
                        <label
                            style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 10px; display: block;">Tipo
                            de Transferencia</label>
                        <div class="transfer-type-grid">
                            <button type="button" class="btn-type active" id="btn-type-unidad"
                                onclick="setTransferType('unidad')">
                                <i class="fas fa-box"></i> Solo Unidades
                            </button>
                            <button type="button" class="btn-type" id="btn-type-caja" onclick="setTransferType('caja')">
                                <i class="fas fa-boxes"></i> Cajas Completas
                            </button>
                            <button type="button" class="btn-type" id="btn-type-fraccion"
                                onclick="setTransferType('fraccion')">
                                <i class="fas fa-layer-group"></i> Cajas + Fracciones
                            </button>
                        </div>

                        <!-- Dynamic Inputs for Qty -->
                        <div id="qty-inputs-container" style="margin-top: 20px;">
                            <!-- Only Units View -->
                            <div id="view-only-units">
                                <label
                                    style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Cantidad
                                    (Unidades) *</label>
                                <input type="number" id="input-qty-unidades" class="form-control"
                                    placeholder="Ingrese la cantidad..." oninput="calculateTotalUnits()">
                            </div>

                            <!-- Full Boxes View -->
                            <div id="view-full-boxes" style="display: none; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div>
                                    <label
                                        style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Unidades
                                        por Caja *</label>
                                    <input type="number" id="u-por-caja-c" class="form-control" value="1"
                                        oninput="calculateTotalUnits()">
                                </div>
                                <div>
                                    <label
                                        style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Número
                                        de Cajas *</label>
                                    <input type="number" id="num-cajas-c" class="form-control"
                                        placeholder="Cajas a transferir" oninput="calculateTotalUnits()">
                                </div>
                            </div>

                            <!-- Boxes + Fractions View -->
                            <div id="view-boxes-fractions"
                                style="display: none; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                                <div>
                                    <label
                                        style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Unidades
                                        por Caja</label>
                                    <input type="number" id="u-por-caja-f" class="form-control" value="1"
                                        oninput="calculateTotalUnits()">
                                </div>
                                <div>
                                    <label
                                        style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Cajas
                                        Completas</label>
                                    <input type="number" id="num-cajas-f" class="form-control" value="0"
                                        oninput="calculateTotalUnits()">
                                </div>
                                <div>
                                    <label
                                        style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block;">Fracciones
                                        (Sueltas)</label>
                                    <input type="number" id="num-frac-f" class="form-control" value="0"
                                        oninput="calculateTotalUnits()">
                                </div>
                            </div>

                            <!-- Summary Box -->
                            <div
                                style="background: #e0f2fe; padding: 12px; border-radius: 8px; margin-top: 15px; color: #0369a1; font-weight: 600; font-size: 0.9rem;">
                                Total de unidades: <span id="total-units-label">0</span>
                            </div>
                            <p id="max-lote-hint" style="font-size: 0.75rem; color: #64748b; margin-top: 8px;"></p>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: PRICE AT DESTINATION -->
                <div class="step-container" id="step-3" style="display: none; margin-top: 20px;">
                    <div class="step-header" style="background: #6366f1;">
                        <i class="fas fa-dollar-sign"></i> PASO 3: Precio en Destino (Opcional)
                    </div>
                    <div class="step-body">
                        <label class="checkbox-container">
                            <input type="checkbox" id="check-change-price" onchange="togglePriceFields()">
                            <span class="checkmark"></span>
                            Cambiar precio PVP en la ubicación destino
                        </label>

                        <div id="price-fields"
                            style="display: none; margin-top: 15px; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group-nt">
                                <label>Precio Actual (Origen)</label>
                                <div class="input-with-symbol">
                                    <span>$</span>
                                    <input type="text" id="price-origin" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group-nt">
                                <label>Nuevo Precio (Destino)</label>
                                <div class="input-with-symbol">
                                    <span>$</span>
                                    <input type="number" step="0.01" id="price-destination" class="form-control"
                                        oninput="calculatePriceDiff()">
                                </div>
                            </div>
                            <div
                                style="grid-column: 1 / -1; background: #f1f5f9; padding: 12px; border-radius: 8px; font-size: 0.85rem; color: #475569;">
                                Diferencia: <b id="price-diff-val">$0.00</b> (<span id="price-diff-perc">0%</span>)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"
                style="padding: 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 12px; background: #fff;">
                <button type="button" class="btn btn-secondary" onclick="closeProductModal()"
                    style="background: #64748b; border: none; padding: 10px 20px; border-radius: 6px; color: white;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" id="btn-add-final" class="btn btn-primary" onclick="confirmAddProduct()" disabled
                    style="background: #a5b4fc; border: none; padding: 10px 20px; border-radius: 6px; color: white; cursor: not-allowed;">
                    <i class="fas fa-check"></i> Agregar a Transferencia
                </button>
            </div>
        </div>
    </div>

    <!-- SEPARATE SEARCH MODAL -->
    <div class="modal-overlay" id="search-modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 style="font-size: 1.1rem; font-weight: 700; color: #1e293b;"><i class="fas fa-search"></i> Buscar
                    Producto</h2>
                <button type="button" class="btn-text" onclick="closeSearchModal()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <input type="text" id="search-input" class="form-control" placeholder="Nombre o código del producto...">
                <div id="search-results" style="margin-top: 15px; max-height: 400px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>

    <style>
        /* New Styles for Multi-Step Modal */
        .step-container {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .step-header {
            background: #8b5cf6;
            color: white;
            padding: 10px 15px;
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .step-body {
            padding: 20px;
            background: white;
        }

        .btn-search-lotes {
            width: 100%;
            background: #06b6d4;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-search-lotes:hover {
            background: #0891b2;
        }

        .lotes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            margin-top: 10px;
        }

        .lotes-table th {
            text-align: left;
            padding: 10px;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
        }

        .lotes-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .badge-lote {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.7rem;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background: #fff7ed;
            color: #f97316;
        }

        .badge-danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .selected-summary {
            background: #e0f2fe;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 4px solid #3b82f6;
            font-size: 0.85rem;
            color: #1e40af;
        }

        .transfer-type-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .btn-type {
            background: white;
            border: 1px solid #cbd5e1;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #64748b;
            transition: all 0.2s;
        }

        .btn-type.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
            user-select: none;
        }

        .input-with-symbol {
            position: relative;
        }

        .input-with-symbol span {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-weight: 600;
        }

        .input-with-symbol .form-control {
            padding-left: 30px;
        }

        /* Generic modal reset */
        .btn-text {
            background: none;
            border: none;
            cursor: pointer;
        }
    </style>

    <script>
        let selectedProducts = [];
        let currentProduct = null;
        let selectedLote = null;
        let transferType = 'unidad';

        function openProductModal() {
            const origen = document.getElementById('origen_id').value;
            if (!origen) {
                alert('Debe seleccionar primero una ubicación de origen.');
                return;
            }
            document.getElementById('search-modal').style.display = 'flex';
            document.getElementById('search-input').value = '';
            document.getElementById('search-input').focus();
            searchProducts('');
        }

        function closeSearchModal() {
            document.getElementById('search-modal').style.display = 'none';
        }

        function closeProductModal() {
            document.getElementById('product-modal').style.display = 'none';
        }

        // Search Product Logic with Debounce to avoid flickering
        let searchTimeout = null;
        document.getElementById('search-input').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchProducts(e.target.value);
            }, 300);
        });

        function searchProducts(q) {
            const resultsDiv = document.getElementById('search-results');

            // Only show spinner if it's the first search or if search is taking long
            // To avoid flickering, we don't clear resultsDiv immediately if q is small
            if (q.length > 0 && q.length < 3) return; // Optional: wait for more chars

            fetch(`ajax_buscar_productos.php?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        resultsDiv.innerHTML = '<p style="text-align: center; padding: 20px; color: #64748b;">No se encontraron productos.</p>';
                        return;
                    }
                    let html = '';
                    data.forEach(p => {
                        // Securely stringify product for onclick
                        const pData = JSON.stringify(p).replace(/'/g, "&apos;");
                        html += `
                            <div class="search-item-box" style="padding: 12px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: background 0.2s;" 
                                 onclick='initProductConfig(${pData})'
                                 onmouseover="this.style.background='#f8fafc'" 
                                 onmouseout="this.style.background='transparent'">
                                <div>
                                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 600; color: #1e293b;">${p.nombre}</h4>
                                    <div style="display: flex; gap: 10px; margin-top: 4px;">
                                        <small style="color: #64748b;"><i class="fas fa-barcode"></i> ${p.barcode || 'S/N'}</small>
                                        <small style="color: #64748b;"><i class="fas fa-dollar-sign"></i> PVP: ${parseFloat(p.price).toFixed(2)}</small>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-weight: 700; color: #2563eb; background: #eff6ff; padding: 4px 8px; border-radius: 6px; font-size: 0.85rem;">
                                        ${parseFloat(p.stock).toFixed(2)} disp.
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                    resultsDiv.innerHTML = html;
                })
                .catch(err => {
                    resultsDiv.innerHTML = '<p style="text-align: center; padding: 20px; color: #ef4444;">Error al buscar productos.</p>';
                });
        }

        function initProductConfig(p) {
            currentProduct = p;
            selectedLote = null;
            closeSearchModal();

            // Set Header Info
            document.getElementById('modal-product-name').value = `${p.id}-${p.nombre} (${p.barcode || 'S/N'})`;
            document.getElementById('modal-product-stock').value = parseFloat(p.stock).toFixed(2);
            document.getElementById('price-origin').value = parseFloat(p.price).toFixed(2);

            document.getElementById('check-change-price').checked = false;
            document.getElementById('price-fields').style.display = 'none';
            document.getElementById('btn-add-final').disabled = true;
            document.getElementById('btn-add-final').style.background = '#a5b4fc';
            document.getElementById('btn-add-final').style.cursor = 'not-allowed';

            document.getElementById('product-modal').style.display = 'flex';

            // IF PRODUCT DOES NOT MANAGE LOTS, SKIP STEP 1
            if (p.manejaLote == 0) {
                document.getElementById('step-1').style.display = 'none';
                document.getElementById('step-2').style.display = 'block';
                document.getElementById('step-3').style.display = 'block';
                document.getElementById('max-lote-hint').innerText = `Stock total disponible: ${p.stock}`;
                selectedLote = { id: 0, lote: 'N/A', caducidad_fmt: 'N/A', stock: p.stock };
            } else {
                document.getElementById('step-1').style.display = 'block';
                document.getElementById('step-2').style.display = 'none';
                document.getElementById('step-3').style.display = 'none';
                document.getElementById('lotes-container').style.display = 'none';
                document.getElementById('selected-lote-summary').style.display = 'none';
                document.getElementById('btn-load-lotes').style.display = 'block';
                document.getElementById('btn-load-lotes').disabled = false;
                document.getElementById('btn-load-lotes').innerHTML = '<i class="fas fa-search"></i> Buscar Lotes Disponibles';
            }
        }

        function loadLotes() {
            const btn = document.getElementById('btn-load-lotes');
            const ubicacion_origen = document.getElementById('origen_id').value;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando lotes...';

            fetch(`ajax_get_lotes_producto.php?producto_id=${currentProduct.id}&ubicacion_id=${ubicacion_origen}`)
                .then(res => res.json())
                .then(lotes => {
                    const tbody = document.getElementById('lotes-list-body');
                    if (lotes.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No hay lotes con stock en esta ubicación.</td></tr>';
                    } else {
                        tbody.innerHTML = lotes.map(l => `
                            <tr>
                                <td style="text-align: center;">
                                    <input type="radio" name="lote_selection" value="${l.id}" onchange='selectLote(${JSON.stringify(l)})'>
                                </td>
                                <td style="font-weight: 700;">${l.lote}</td>
                                <td style="font-weight: 600;">${l.caducidad_fmt}</td>
                                <td style="text-align: center;"><span class="badge-lote ${l.dias > 30 ? 'badge-success' : 'badge-warning'}">${l.dias} días</span></td>
                                <td style="text-align: right; font-weight: 700;">${l.stock} unid.</td>
                                <td style="text-align: center;"><span class="badge-lote ${l.estado_class}">${l.estado}</span></td>
                            </tr>
                        `).join('');
                    }
                    document.getElementById('lotes-container').style.display = 'block';
                    btn.style.display = 'none';
                });
        }

        function selectLote(lote) {
            selectedLote = lote;

            // Show Summary
            const summary = document.getElementById('selected-lote-summary');
            summary.innerHTML = `<b>Lote seleccionado:</b> ${lote.lote} - Vence: ${lote.caducidad_fmt} - Disponible: ${lote.stock}`;
            summary.style.display = 'block';

            // Show Step 2 & 3
            document.getElementById('step-2').style.display = 'block';
            document.getElementById('step-3').style.display = 'block';
            document.getElementById('max-lote-hint').innerText = `Máximo disponible en lote: ${lote.stock}`;

            // Reset fields
            document.getElementById('input-qty-unidades').value = '';
            document.getElementById('num-cajas-c').value = '';
            document.getElementById('num-cajas-f').value = 0;
            document.getElementById('num-frac-f').value = 0;
            calculateTotalUnits();
        }

        function setTransferType(type) {
            transferType = type;
            const btns = ['unidad', 'caja', 'fraccion'];
            btns.forEach(b => {
                document.getElementById(`btn-type-${b}`).classList.toggle('active', b === type);
                const view = document.getElementById(`view-${b === 'unidad' ? 'only-units' : b === 'caja' ? 'full-boxes' : 'boxes-fractions'}`);
                if (view) view.style.display = (b === type) ? (b === 'unidad' ? 'block' : 'grid') : 'none';
            });
            calculateTotalUnits();
        }

        function calculateTotalUnits() {
            let total = 0;
            if (transferType === 'unidad') {
                total = parseFloat(document.getElementById('input-qty-unidades').value) || 0;
            } else if (transferType === 'caja') {
                const uPorCaja = parseFloat(document.getElementById('u-por-caja-c').value) || 0;
                const numCajas = parseFloat(document.getElementById('num-cajas-c').value) || 0;
                total = uPorCaja * numCajas;
            } else if (transferType === 'fraccion') {
                const uPorCaja = parseFloat(document.getElementById('u-por-caja-f').value) || 0;
                const numCajas = parseFloat(document.getElementById('num-cajas-f').value) || 0;
                const numFrac = parseFloat(document.getElementById('num-frac-f').value) || 0;
                total = (uPorCaja * numCajas) + numFrac;
            }

            document.getElementById('total-units-label').innerText = total;
            validateQty(total);
        }

        function validateQty(total) {
            const btn = document.getElementById('btn-add-final');
            const maxAllowed = (currentProduct.manejaLote == 1) ? (selectedLote ? selectedLote.stock : 0) : currentProduct.stock;

            if (total > 0 && total <= maxAllowed) {
                if (currentProduct.manejaLote == 1 && !selectedLote) {
                    btn.disabled = true;
                    btn.style.background = '#a5b4fc';
                    btn.style.cursor = 'not-allowed';
                } else {
                    btn.disabled = false;
                    btn.style.background = '#2563eb';
                    btn.style.cursor = 'pointer';
                }
            } else {
                btn.disabled = true;
                btn.style.background = '#a5b4fc';
                btn.style.cursor = 'not-allowed';
            }
        }

        function togglePriceFields() {
            const checked = document.getElementById('check-change-price').checked;
            document.getElementById('price-fields').style.display = checked ? 'grid' : 'none';
        }

        function calculatePriceDiff() {
            const oldP = parseFloat(document.getElementById('price-origin').value) || 0;
            const newP = parseFloat(document.getElementById('price-destination').value) || 0;
            const diff = newP - oldP;
            const perc = oldP > 0 ? (diff / oldP) * 100 : 0;

            document.getElementById('price-diff-val').innerText = `$${diff.toFixed(2)}`;
            document.getElementById('price-diff-perc').innerText = `${perc.toFixed(1)}%`;
            document.getElementById('price-diff-val').style.color = diff >= 0 ? '#10b981' : '#ef4444';
        }

        function confirmAddProduct() {
            const totalUnits = parseFloat(document.getElementById('total-units-label').innerText);
            const changePrice = document.getElementById('check-change-price').checked;
            const newPrice = changePrice ? parseFloat(document.getElementById('price-destination').value) : null;
            const originPrice = parseFloat(document.getElementById('price-origin').value) || 0;

            let unitsPerBox = 1;
            let boxes = 0;
            let fractions = 0;

            if (transferType === 'unidad') {
                fractions = totalUnits;
            } else if (transferType === 'caja') {
                unitsPerBox = parseFloat(document.getElementById('u-por-caja-c').value) || 1;
                boxes = parseFloat(document.getElementById('num-cajas-c').value) || 0;
            } else {
                unitsPerBox = parseFloat(document.getElementById('u-por-caja-f').value) || 1;
                boxes = parseFloat(document.getElementById('num-cajas-f').value) || 0;
                fractions = parseFloat(document.getElementById('num-frac-f').value) || 0;
            }

            if (selectedProducts.find(p => p.id === currentProduct.id && p.lote_id === (selectedLote ? selectedLote.id : 0))) {
                alert('Este producto con este lote ya ha sido agregado.');
                return;
            }

            selectedProducts.push({
                id: currentProduct.id,
                nombre: currentProduct.nombre,
                barcode: currentProduct.barcode,
                manejaLote: currentProduct.manejaLote,
                lote_id: selectedLote ? selectedLote.id : 0,
                lote_numero: selectedLote ? selectedLote.lote : 'N/A',
                lote_vencimiento: selectedLote ? selectedLote.caducidad_fmt : 'N/A',
                cantidad: totalUnits,
                tipo_transferencia: transferType,
                unidades_por_caja: unitsPerBox,
                cantidad_cajas: boxes,
                cantidad_fracciones: fractions,
                cambia_precio: changePrice,
                precio_origen: originPrice,
                nuevo_precio: newPrice
            });

            renderProducts();
            closeProductModal();
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
                    <td>
                        <div style="font-weight: 700;">${p.nombre}</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Lote: ${p.lote_numero} - Vence: ${p.lote_vencimiento}</div>
                    </td>
                    <td>${p.barcode || '-'}</td>
                    <td style="text-align: center;">
                        <span style="font-weight: 700;">${p.cantidad}</span> <small>${p.tipo_transferencia === 'unidad' ? 'unid.' : 'cajas'}</small>
                        ${p.cambia_precio ? `<br><small style="color: #6366f1;">PVP: $${p.nuevo_precio}</small>` : ''}
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-text" style="color: #ef4444;" onclick="removeProduct('${p.id}-${p.lote_id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function removeProduct(key) {
            const [id, lote_id] = key.split('-');
            selectedProducts = selectedProducts.filter(p => !(p.id == id && p.lote_id == lote_id));
            renderProducts();
        }

        // Form Submit
        document.getElementById('transfer-form').addEventListener('submit', function (e) {
            e.preventDefault();
            if (selectedProducts.length === 0) {
                showToast('Atención', 'Debe agregar al menos un producto para realizar la transferencia.', 'warning');
                return;
            }

            const formData = new FormData(this);
            formData.append('action', 'save_transfer');
            formData.append('productos', JSON.stringify(selectedProducts));

            const btn = this.querySelector('.btn-create-nt');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            console.log('Sending transfer data:', Object.fromEntries(formData.entries()));

            fetch('nueva_transferencia.php', { method: 'POST', body: formData })
                .then(res => {
                    console.log('Response status:', res.status);
                    return res.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Invalid JSON response:', text);
                            throw new Error('La respuesta del servidor no es un JSON válido.');
                        }
                    });
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        showToast('¡Éxito!', data.message, 'success');
                        setTimeout(() => {
                            window.location.href = 'transferencias.php';
                        }, 1500);
                    } else {
                        showToast('Error', data.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save"></i> Crear Transferencia';
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    showToast('Error de Conexión', err.message || 'No se pudo comunicar con el servidor.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Crear Transferencia';
                });
        });

        // Close on overlay click
        window.onclick = function (event) {
            if (event.target == document.getElementById('product-modal')) closeProductModal();
            if (event.target == document.getElementById('search-modal')) closeSearchModal();
        }
    </script>
    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>