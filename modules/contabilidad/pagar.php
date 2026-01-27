<?php
/**
 * Cuentas por Pagar - Accounting Module
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'contabilidad_pagar';

// Cargar proveedores desde la base de datos
$proveedores = [];
try {
    // Usamos la tabla 'proveedores' que es la que tiene los 24 registros
    $stmt = $pdo->query("SELECT id, razonSocial as nombre, ruc FROM proveedores WHERE anulado = 0 ORDER BY razonSocial");
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar proveedores: " . $e->getMessage());
}

// Cargar cuentas por pagar desde la base de datos
$cuentas = [];
$totales = [
    'total_por_pagar' => 0,
    'pendiente' => 0,
    'vencido' => 0,
    'pagado_mes' => 0
];

try {
    $stmt = $pdo->query("SELECT c.*, p.razonSocial as proveedor_nombre, p.ruc
                         FROM contabilidad_cuentaporpagar c
                         LEFT JOIN proveedores p ON c.proveedor_id = p.id
                         ORDER BY c.fecha_vencimiento ASC");
    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $now = date('Y-m-d');
    foreach ($cuentas as &$c) {
        $totales['total_por_pagar'] += $c['monto_original'];
        $totales['pendiente'] += $c['monto_pendiente'];

        if ($c['estado'] !== 'PAGADA' && $c['fecha_vencimiento'] < $now) {
            $totales['vencido'] += $c['monto_pendiente'];
            $c['status_class'] = 'status-overdue';
            $c['estado_display'] = 'Vencida';
        } else {
            $c['status_class'] = ($c['estado'] === 'PAGADA') ? 'status-paid' : 'status-pending';
            $c['estado_display'] = $c['estado'];
        }
    }

    // Total pagado este mes
    $thisMonth = date('Y-m');
    $stmtM = $pdo->prepare("SELECT SUM(monto) FROM contabilidad_pagocuentaporpagar 
                           WHERE DATE_FORMAT(fecha_pago, '%Y-%m') = ?");
    $stmtM->execute([$thisMonth]);
    $totales['pagado_mes'] = (float) $stmtM->fetchColumn();

    // Cargar cuentas bancarias reales
    $stmtCb = $pdo->query("SELECT id, nombre, banco, numero_cuenta FROM contabilidad_cuentabancaria ORDER BY nombre ASC");
    $cuentas_bancarias = $stmtCb->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error al cargar datos de contabilidad: " . $e->getMessage());
    $error_msg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas por Pagar | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .accounting-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .accounting-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn-accounting {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-accounting:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: #2563eb;
        }

        .btn-success {
            background: #059669;
        }

        .btn-warning {
            background: #f59e0b;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #f1f5f9;
        }

        .stat-info .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: block;
        }

        .stat-info .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .icon-blue {
            background: #eff6ff;
            color: #2563eb;
        }

        .icon-green {
            background: #ecfdf5;
            color: #059669;
        }

        .icon-yellow {
            background: #fefce8;
            color: #ca8a04;
        }

        .icon-red {
            background: #fef2f2;
            color: #dc2626;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .table-toolbar {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .acc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .acc-table th {
            background: #f8fafc;
            padding: 15px 20px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            border-bottom: 2px solid #f1f5f9;
        }

        .acc-table td {
            padding: 18px 20px;
            font-size: 0.9rem;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-pending {
            background: #fff7ed;
            color: #c2410c;
        }

        .status-paid {
            background: #ecfdf5;
            color: #047857;
        }

        .status-overdue {
            background: #fef2f2;
            color: #b91c1c;
        }

        .amount-cell {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            text-align: right;
        }

        .balance-cell {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            text-align: right;
            color: #dc2626;
        }

        .actions-cell {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #eff6ff;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #94a3b8;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #1e293b;
            transition: all 0.2s;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-modal {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-submit {
            background: #2563eb;
            color: white;
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
                <div class="accounting-header">
                    <h1><i class="fas fa-file-invoice-dollar"></i> Cuentas por Pagar</h1>
                    <div class="header-actions">
                        <button class="btn-accounting btn-primary" onclick="openRegistrarFacturaModal()"><i
                                class="fas fa-plus"></i> Registrar Factura</button>
                        <button class="btn-accounting btn-warning" onclick="openProgramarPagosModal()"><i
                                class="fas fa-calendar-check"></i> Programar Pagos</button>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info"><span class="stat-label">Total a Pagar</span><span class="stat-value">$
                                <?php echo number_format($totales['total_por_pagar'], 2); ?>
                            </span></div>
                        <div class="stat-icon icon-blue"><i class="fas fa-wallet"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info"><span class="stat-label">Pendiente</span><span class="stat-value">$
                                <?php echo number_format($totales['pendiente'], 2); ?>
                            </span></div>
                        <div class="stat-icon icon-yellow"><i class="fas fa-hourglass-half"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info"><span class="stat-label">Vencido</span><span class="stat-value">$
                                <?php echo number_format($totales['vencido'], 2); ?>
                            </span></div>
                        <div class="stat-icon icon-red"><i class="fas fa-exclamation-circle"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info"><span class="stat-label">Saldado Mes</span><span class="stat-value">$
                                <?php echo number_format($totales['pagado_mes'], 2); ?>
                            </span></div>
                        <div class="stat-icon icon-green"><i class="fas fa-check-double"></i></div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="search-box"><i class="fas fa-search"></i><input type="text" placeholder="Buscar...">
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="acc-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Proveedor</th>
                                    <th>Emisión</th>
                                    <th>Vencimiento</th>
                                    <th class="amount-cell">Total</th>
                                    <th class="amount-cell">Saldo</th>
                                    <th>Estado</th>
                                    <th style="text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cuentas as $c): ?>
                                    <tr>
                                        <td style="font-weight: 700; color: #64748b;">#
                                            <?php echo str_pad($c['id'], 5, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td style="font-weight: 600;">
                                            <?php echo htmlspecialchars($c['proveedor_nombre'] ?? 'Desconocido'); ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($c['fecha_emision'])); ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($c['fecha_vencimiento'])); ?>
                                        </td>
                                        <td class="amount-cell">$
                                            <?php echo number_format($c['monto_original'], 2); ?>
                                        </td>
                                        <td class="balance-cell">$
                                            <?php echo number_format($c['monto_pendiente'], 2); ?>
                                        </td>
                                        <td><span class="status-badge <?php echo $c['status_class']; ?>">
                                                <?php echo $c['estado_display']; ?>
                                            </span></td>
                                        <td class="actions-cell">
                                            <button class="btn-icon" onclick='verDetalle(<?php echo json_encode($c); ?>)'><i
                                                    class="fas fa-eye"></i></button>
                                            <?php if ($c['monto_pendiente'] > 0): ?>
                                                <button class="btn-icon" style="color: #2563eb;"
                                                    onclick='emitirPago(<?php echo json_encode($c); ?>)'><i
                                                        class="fas fa-check-circle"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Registrar Factura -->
    <div class="modal-overlay" id="modalRegistrarFactura">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-file-invoice"></i> Registrar Factura</h2><button class="modal-close"
                    onclick="closeModal('modalRegistrarFactura')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarFactura" onsubmit="guardarFactura(event)">
                    <div class="form-group">
                        <label>Proveedor *</label>
                        <select name="proveedor_id" required>
                            <option value="">Seleccione un proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?php echo $proveedor['id']; ?>">
                                    <?php echo htmlspecialchars($proveedor['nombre']); ?> (
                                    <?php echo htmlspecialchars($proveedor['ruc']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Número Factura *</label><input type="text" name="factura"
                                required></div>
                        <div class="form-group"><label>Fecha Emisión *</label><input type="date" name="fecha"
                                value="<?php echo date('Y-m-d'); ?>" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Monto Total *</label><input type="number" name="total"
                                step="0.01" required></div>
                        <div class="form-group"><label>Días Crédito *</label><input type="number" name="dias_credito"
                                value="30" required></div>
                    </div>
                    <div class="form-group"><label>Categoría</label><select name="tipo_compra">
                            <option value="Mercaderia">Mercadería</option>
                            <option value="Servicios">Servicios</option>
                        </select></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn-modal btn-cancel"
                    onclick="closeModal('modalRegistrarFactura')">Cancelar</button><button type="submit"
                    form="formRegistrarFactura" class="btn-modal btn-submit">Guardar</button></div>
        </div>
    </div>

    <!-- Modal Programar Pagos -->
    <div class="modal-overlay" id="modalProgramarPagos">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-check"></i> Programar Pagos</h2><button class="modal-close"
                    onclick="closeModal('modalProgramarPagos')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formProgramarPagos" onsubmit="guardarProgramacion(event)">
                    <div class="form-group">
                        <label>Cuenta a Pagar *</label>
                        <select name="cuenta_id" id="programar_cuenta_id" onchange="updateDeudaInfo()" required>
                            <?php if (empty($cuentas)): ?>
                                <option value="">No hay facturas pendientes</option>
                            <?php else: ?>
                                <option value="">Seleccione una factura</option>
                                <?php foreach ($cuentas as $c):
                                    if ($c['monto_pendiente'] > 0): ?>
                                        <option value="<?php echo $c['id']; ?>" data-saldo="<?php echo $c['monto_pendiente']; ?>">#
                                            <?php echo str_pad($c['id'], 5, '0', STR_PAD_LEFT); ?> -
                                            <?php echo htmlspecialchars($c['proveedor_nombre']); ?> ($
                                            <?php echo number_format($c['monto_pendiente'], 2); ?>)
                                        </option>
                                    <?php endif; endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div id="info_pago"
                        style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; display:none;">
                        <span style="color: #64748b; font-size: 0.9rem;">Saldo Pendiente:</span>
                        <span id="programar_saldo"
                            style="color:#dc2626; font-weight:700; font-size: 1.1rem; float: right;"></span>
                        <div style="clear: both;"></div>
                    </div>

                    <div id="campos_programacion" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Número de Cuotas *</label>
                                <input type="number" name="num_cuotas" id="num_cuotas" min="1" max="24" value="1"
                                    onchange="generatePaymentSchedule()">
                            </div>
                            <div class="form-group">
                                <label>Frecuencia *</label>
                                <select name="frecuencia" id="frecuencia" onchange="generatePaymentSchedule()">
                                    <option value="semanal">Semanal</option>
                                    <option value="quincenal">Quincenal</option>
                                    <option value="mensual" selected>Mensual</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Fecha del Primer Pago *</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio"
                                value="<?php echo date('Y-m-d'); ?>" onchange="generatePaymentSchedule()">
                        </div>

                        <div id="paymentSchedule"
                            style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 15px; display: none;">
                            <label
                                style="font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Cronograma
                                Estimado:</label>
                            <div id="scheduleItems" style="margin-top: 10px; max-height: 200px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel"
                    onclick="closeModal('modalProgramarPagos')">Cancelar</button>
                <button type="submit" form="formProgramarPagos" class="btn-modal btn-submit" id="btn_guardar_prog"
                    style="display: none;">Guardar Programación</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
        function openRegistrarFacturaModal() { openModal('modalRegistrarFactura'); }
        function openProgramarPagosModal() { openModal('modalProgramarPagos'); }

        function updateDeudaInfo() {
            const sel = document.getElementById('programar_cuenta_id');
            const opt = sel.options[sel.selectedIndex];
            if (opt.value) {
                document.getElementById('info_pago').style.display = 'block';
                document.getElementById('campos_programacion').style.display = 'block';
                document.getElementById('btn_guardar_prog').style.display = 'block';
                document.getElementById('programar_saldo').textContent = '$' + parseFloat(opt.getAttribute('data-saldo')).toFixed(2);
                generatePaymentSchedule();
            } else {
                document.getElementById('info_pago').style.display = 'none';
                document.getElementById('campos_programacion').style.display = 'none';
                document.getElementById('btn_guardar_prog').style.display = 'none';
            }
        }

        function generatePaymentSchedule() {
            const sel = document.getElementById('programar_cuenta_id');
            const opt = sel.options[sel.selectedIndex];
            if (!opt.value) return;

            const saldo = parseFloat(opt.getAttribute('data-saldo'));
            const numCuotas = parseInt(document.getElementById('num_cuotas').value) || 1;
            const frecuencia = document.getElementById('frecuencia').value;
            const fechaInicioStr = document.getElementById('fecha_inicio').value;

            if (!fechaInicioStr) return;

            const montoCuota = (saldo / numCuotas).toFixed(2);
            const scheduleContainer = document.getElementById('scheduleItems');
            scheduleContainer.innerHTML = '';

            let diasIncremento = 30;
            if (frecuencia === 'semanal') diasIncremento = 7;
            if (frecuencia === 'quincenal') diasIncremento = 15;

            for (let i = 0; i < numCuotas; i++) {
                let fecha = new Date(fechaInicioStr + 'T12:00:00');
                fecha.setDate(fecha.getDate() + (i * diasIncremento));

                const item = document.createElement('div');
                item.style.cssText = 'padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 6px; display: flex; justify-content: space-between; font-size: 0.85rem;';
                item.innerHTML = `
                    <span><b>Cuota ${i + 1}:</b> ${fecha.toLocaleDateString('es-ES')}</span>
                    <span style="font-weight: 700;">$${montoCuota}</span>
                `;
                scheduleContainer.appendChild(item);
            }
            document.getElementById('paymentSchedule').style.display = 'block';
        }

        async function guardarProgramacion(e) {
            e.preventDefault();
            const sel = document.getElementById('programar_cuenta_id');
            const data = {
                cuenta_id: sel.value,
                num_cuotas: document.getElementById('num_cuotas').value,
                frecuencia: document.getElementById('frecuencia').value,
                fecha_inicio: document.getElementById('fecha_inicio').value,
                saldo: sel.options[sel.selectedIndex].getAttribute('data-saldo')
            };

            try {
                const res = await fetch('api_pagar.php?action=save_schedule', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const r = await res.json();
                if (r.success) {
                    Swal.fire('Éxito', r.message, 'success').then(() => closeModal('modalProgramarPagos'));
                } else {
                    Swal.fire('Error', r.error, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'No se pudo guardar la programación', 'error');
            }
        }

        async function guardarFactura(e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            try {
                const res = await fetch('api_pagar.php?action=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const r = await res.json();
                if (r.success) {
                    Swal.fire('Éxito', r.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', r.error, 'error');
                }
            } catch (error) { Swal.fire('Error', 'No se pudo guardar', 'error'); }
        }

        function verDetalle(c) {
            Swal.fire({
                title: 'Detalle de Cuenta #' + String(c.id).padStart(5, '0'),
                html: `<div style="text-align:left">
                    <p><b>Proveedor:</b> ${c.proveedor_nombre}</p>
                    <p><b>Total:</b> $${c.monto_original}</p>
                    <p><b>Pendiente:</b> $${c.monto_pendiente}</p>
                    <p><b>Vencimiento:</b> ${c.fecha_vencimiento}</p>
                </div>`,
                confirmButtonText: 'Cerrar'
            });
        }
    </script>
</body>

</html>