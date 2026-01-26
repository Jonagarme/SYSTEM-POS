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
    $stmt = $pdo->query("SELECT id, razonSocial as nombre, ruc FROM proveedores WHERE estado = 1 AND anulado = 0 ORDER BY razonSocial");
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar proveedores: " . $e->getMessage());
}

// Dummy data for initial UI demonstration
$cuentas = [
    [
        'id' => 1,
        'proveedor' => 'Distribuidora Farmacéutica S.A.',
        'fecha' => '2026-01-05',
        'vencimiento' => '2026-02-05',
        'total' => 2500.00,
        'saldo' => 1250.00,
        'estado' => 'Parcial',
        'status_class' => 'status-pending'
    ],
    [
        'id' => 2,
        'proveedor' => 'Laboratorios Roche',
        'fecha' => '2026-01-12',
        'vencimiento' => '2026-02-12',
        'total' => 850.00,
        'saldo' => 850.00,
        'estado' => 'Pendiente',
        'status_class' => 'status-pending'
    ],
    [
        'id' => 3,
        'proveedor' => 'Suministros Médicos Global',
        'fecha' => '2025-12-15',
        'vencimiento' => '2026-01-15',
        'total' => 450.00,
        'saldo' => 450.00,
        'estado' => 'Vencida',
        'status_class' => 'status-overdue'
    ]
];

$totales = [
    'total_por_pagar' => 3800.00,
    'pendiente' => 2550.00,
    'vencido' => 450.00,
    'pagado_mes' => 5200.00
];
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

        /* Responsive Improvements */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .accounting-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header-actions {
                width: 100%;
            }

            .btn-accounting {
                flex: 1;
                justify-content: center;
            }
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

        .modal-close:hover {
            background: #f1f5f9;
            color: #475569;
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

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
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

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        .btn-submit {
            background: #2563eb;
            color: white;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        .detail-value {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 600;
        }

        .payment-schedule {
            margin-top: 15px;
        }

        .payment-schedule-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .payment-schedule-item button {
            background: #ef4444;
            color: white;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
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
                        <button class="btn-accounting btn-primary" onclick="openRegistrarFacturaModal()"><i class="fas fa-plus"></i> Registrar
                            Factura</button>
                        <button class="btn-accounting btn-warning" onclick="openProgramarPagosModal()"><i class="fas fa-calendar-check"></i> Programar
                            Pagos</button>
                        <button class="btn-accounting btn-info" onclick="generarReporte()"><i class="fas fa-print"></i> Reportes</button>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total a Pagar</span>
                            <span class="stat-value">$
                                <?php echo number_format($totales['total_por_pagar'], 2); ?>
                            </span>
                        </div>
                        <div class="stat-icon icon-blue">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Pendiente</span>
                            <span class="stat-value">$
                                <?php echo number_format($totales['pendiente'], 2); ?>
                            </span>
                        </div>
                        <div class="stat-icon icon-yellow">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Vencido</span>
                            <span class="stat-value">$
                                <?php echo number_format($totales['vencido'], 2); ?>
                            </span>
                        </div>
                        <div class="stat-icon icon-red">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Saldado este Mes</span>
                            <span class="stat-value">$
                                <?php echo number_format($totales['pagado_mes'], 2); ?>
                            </span>
                        </div>
                        <div class="stat-icon icon-green">
                            <i class="fas fa-check-double"></i>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Buscar por proveedor o documento...">
                        </div>
                        <div class="filter-options">
                            <select class="form-select"
                                style="padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <option value="">Todos los Proveedores</option>
                                <option value="Roche">Laboratorios Roche</option>
                                <option value="Roche">Distribuidora Farmacéutica</option>
                            </select>
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
                                            <?php echo $c['proveedor']; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($c['fecha'])); ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($c['vencimiento'])); ?>
                                        </td>
                                        <td class="amount-cell">$
                                            <?php echo number_format($c['total'], 2); ?>
                                        </td>
                                        <td class="balance-cell">$
                                            <?php echo number_format($c['saldo'], 2); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $c['status_class']; ?>">
                                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                                <?php echo $c['estado']; ?>
                                            </span>
                                        </td>
                                        <td class="actions-cell">
                                            <button class="btn-icon" title="Ver Detalle" onclick='verDetalle(<?php echo json_encode($c); ?>)'><i class="fas fa-eye"></i></button>
                                            <button class="btn-icon" title="Emitir Pago" style="color: #2563eb;" onclick='emitirPago(<?php echo json_encode($c); ?>)'><i
                                                    class="fas fa-check-circle"></i></button>
                                            <button class="btn-icon" title="Contactar Proveedor" style="color: #0ea5e9;" onclick='contactarProveedor(<?php echo $c["id"]; ?>, "<?php echo addslashes($c["proveedor"]); ?>")'><i
                                                    class="fas fa-envelope"></i></button>
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
                <h2><i class="fas fa-file-invoice"></i> Registrar Factura de Proveedor</h2>
                <button class="modal-close" onclick="closeModal('modalRegistrarFactura')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarFactura" onsubmit="guardarFactura(event)">
                    <div class="form-group">
                        <label>Proveedor *</label>
                        <select name="proveedor_id" required>
                            <option value="">Seleccione un proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?php echo $proveedor['id']; ?>">
                                    <?php echo htmlspecialchars($proveedor['nombre']); ?>
                                    <?php if (!empty($proveedor['ruc'])): ?>
                                        - RUC: <?php echo htmlspecialchars($proveedor['ruc']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Número de Factura *</label>
                            <input type="text" name="factura" placeholder="001-001-000001234" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Emisión *</label>
                            <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Monto Total *</label>
                            <input type="number" name="total" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label>Días de Crédito *</label>
                            <input type="number" name="dias_credito" value="30" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tipo de Compra *</label>
                        <select name="tipo_compra" required>
                            <option value="">Seleccione tipo</option>
                            <option value="mercaderia">Mercadería</option>
                            <option value="servicios">Servicios</option>
                            <option value="insumos">Insumos</option>
                            <option value="activos">Activos Fijos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observaciones" placeholder="Notas adicionales..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('modalRegistrarFactura')">Cancelar</button>
                <button type="submit" form="formRegistrarFactura" class="btn-modal btn-submit">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Programar Pagos -->
    <div class="modal-overlay" id="modalProgramarPagos">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-check"></i> Programar Pagos</h2>
                <button class="modal-close" onclick="closeModal('modalProgramarPagos')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formProgramarPagos" onsubmit="guardarProgramacion(event)">
                    <div class="form-group">
                        <label>Cuenta a Pagar *</label>
                        <select name="cuenta_id" id="programar_cuenta_id" onchange="updateDeudaInfo()" required>
                            <option value="">Seleccione una cuenta</option>
                            <?php foreach ($cuentas as $c): ?>
                                <option value="<?php echo $c['id']; ?>" data-proveedor="<?php echo $c['proveedor']; ?>" data-saldo="<?php echo $c['saldo']; ?>">
                                    #<?php echo str_pad($c['id'], 5, '0', STR_PAD_LEFT); ?> - <?php echo $c['proveedor']; ?> ($<?php echo number_format($c['saldo'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="detail-row" style="background: #f8fafc; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                        <span class="detail-label">Monto Adeudado:</span>
                        <span class="detail-value" style="color: #dc2626; font-size: 1.1rem;" id="programar_saldo">$0.00</span>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Número de Cuotas *</label>
                            <input type="number" name="num_cuotas" id="num_cuotas" min="1" max="12" value="1" onchange="generatePaymentSchedule()" required>
                        </div>
                        <div class="form-group">
                            <label>Frecuencia *</label>
                            <select name="frecuencia" id="frecuencia" onchange="generatePaymentSchedule()" required>
                                <option value="semanal">Semanal</option>
                                <option value="quincenal">Quincenal</option>
                                <option value="mensual" selected>Mensual</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Fecha del Primer Pago *</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo date('Y-m-d', strtotime('+1 week')); ?>" onchange="generatePaymentSchedule()" required>
                    </div>
                    <div class="payment-schedule" id="paymentSchedule" style="display: none;">
                        <label style="font-size: 0.875rem; font-weight: 600; color: #475569; margin-bottom: 10px; display: block;">Cronograma de Pagos:</label>
                        <div id="scheduleItems"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('modalProgramarPagos')">Cancelar</button>
                <button type="submit" form="formProgramarPagos" class="btn-modal btn-submit">Guardar Programación</button>
            </div>
        </div>
    </div>

    <!-- Modal Emitir Pago -->
    <div class="modal-overlay" id="modalEmitirPago">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-check-circle"></i> Emitir Pago</h2>
                <button class="modal-close" onclick="closeModal('modalEmitirPago')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEmitirPago" onsubmit="guardarEmisionPago(event)">
                    <input type="hidden" name="cuenta_id" id="emitir_cuenta_id">
                    <div class="detail-row">
                        <span class="detail-label">Proveedor:</span>
                        <span class="detail-value" id="emitir_proveedor"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Monto Adeudado:</span>
                        <span class="detail-value" style="color: #dc2626;" id="emitir_saldo"></span>
                    </div>
                    <hr style="margin: 20px 0; border: none; border-top: 1px solid #f1f5f9;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Monto a Pagar *</label>
                            <input type="number" name="monto" id="emitir_monto" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Pago *</label>
                            <input type="date" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Método de Pago *</label>
                        <select name="metodo_pago" required>
                            <option value="">Seleccione método</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="cheque">Cheque</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta Corporativa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Número de Comprobante *</label>
                        <input type="text" name="comprobante" placeholder="Número de transferencia, cheque, etc." required>
                    </div>
                    <div class="form-group">
                        <label>Cuenta Bancaria</label>
                        <select name="cuenta_bancaria">
                            <option value="">Seleccione cuenta</option>
                            <option value="1">Banco Pichincha - *****1234</option>
                            <option value="2">Banco Guayaquil - *****5678</option>
                            <option value="3">Produbanco - *****9012</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea name="notas" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('modalEmitirPago')">Cancelar</button>
                <button type="submit" form="formEmitirPago" class="btn-modal btn-submit">Emitir Pago</button>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalle -->
    <div class="modal-overlay" id="modalVerDetalle">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-file-invoice"></i> Detalle de Cuenta por Pagar</h2>
                <button class="modal-close" onclick="closeModal('modalVerDetalle')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detail-row">
                    <span class="detail-label">ID Cuenta:</span>
                    <span class="detail-value" id="detalle_id"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Proveedor:</span>
                    <span class="detail-value" id="detalle_proveedor"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha de Emisión:</span>
                    <span class="detail-value" id="detalle_fecha"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha de Vencimiento:</span>
                    <span class="detail-value" id="detalle_vencimiento"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Monto Total:</span>
                    <span class="detail-value" id="detalle_total"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Saldo Pendiente:</span>
                    <span class="detail-value" style="color: #dc2626; font-size: 1.1rem;" id="detalle_saldo"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Estado:</span>
                    <span class="detail-value" id="detalle_estado"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('modalVerDetalle')">Cerrar</button>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        // Funciones de Modal
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Cerrar modal al hacer clic fuera
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Registrar Factura
        function openRegistrarFacturaModal() {
            openModal('modalRegistrarFactura');
        }

        function guardarFactura(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            // Aquí se enviaría la información al servidor
            console.log('Guardando factura:', Object.fromEntries(formData));
            
            alert('✓ Factura registrada exitosamente');
            closeModal('modalRegistrarFactura');
            event.target.reset();
            setTimeout(() => location.reload(), 500);
        }

        // Programar Pagos
        function openProgramarPagosModal() {
            openModal('modalProgramarPagos');
        }

        function updateDeudaInfo() {
            const select = document.getElementById('programar_cuenta_id');
            const option = select.options[select.selectedIndex];
            if (option.value) {
                const saldo = parseFloat(option.getAttribute('data-saldo'));
                document.getElementById('programar_saldo').textContent = '$' + saldo.toFixed(2);
                generatePaymentSchedule();
            } else {
                document.getElementById('programar_saldo').textContent = '$0.00';
                document.getElementById('paymentSchedule').style.display = 'none';
            }
        }

        function generatePaymentSchedule() {
            const select = document.getElementById('programar_cuenta_id');
            const option = select.options[select.selectedIndex];
            if (!option.value) return;

            const saldo = parseFloat(option.getAttribute('data-saldo'));
            const numCuotas = parseInt(document.getElementById('num_cuotas').value);
            const frecuencia = document.getElementById('frecuencia').value;
            const fechaInicio = new Date(document.getElementById('fecha_inicio').value);

            if (!numCuotas || !fechaInicio || isNaN(fechaInicio)) return;

            const montoCuota = (saldo / numCuotas).toFixed(2);
            const scheduleContainer = document.getElementById('scheduleItems');
            scheduleContainer.innerHTML = '';

            let diasIncremento;
            switch(frecuencia) {
                case 'semanal': diasIncremento = 7; break;
                case 'quincenal': diasIncremento = 15; break;
                case 'mensual': diasIncremento = 30; break;
            }

            for (let i = 0; i < numCuotas; i++) {
                const fechaPago = new Date(fechaInicio);
                fechaPago.setDate(fechaPago.getDate() + (diasIncremento * i));
                
                const item = document.createElement('div');
                item.className = 'payment-schedule-item';
                item.innerHTML = `
                    <div>
                        <strong>Cuota ${i + 1}</strong><br>
                        <small style="color: #64748b;">${fechaPago.toLocaleDateString('es-ES')} - $${montoCuota}</small>
                    </div>
                `;
                scheduleContainer.appendChild(item);
            }

            document.getElementById('paymentSchedule').style.display = 'block';
        }

        function guardarProgramacion(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            // Aquí se enviaría la información al servidor
            console.log('Guardando programación:', Object.fromEntries(formData));
            
            alert('✓ Programación de pagos guardada exitosamente');
            closeModal('modalProgramarPagos');
            event.target.reset();
            document.getElementById('paymentSchedule').style.display = 'none';
        }

        // Emitir Pago
        function emitirPago(cuenta) {
            document.getElementById('emitir_cuenta_id').value = cuenta.id;
            document.getElementById('emitir_proveedor').textContent = cuenta.proveedor;
            document.getElementById('emitir_saldo').textContent = '$' + parseFloat(cuenta.saldo).toFixed(2);
            document.getElementById('emitir_monto').max = cuenta.saldo;
            openModal('modalEmitirPago');
        }

        function guardarEmisionPago(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const monto = parseFloat(formData.get('monto'));
            const saldo = parseFloat(document.getElementById('emitir_saldo').textContent.replace('$', ''));
            
            if (monto > saldo) {
                alert('⚠ El monto no puede ser mayor al saldo adeudado');
                return;
            }
            
            // Aquí se enviaría la información al servidor
            console.log('Emitiendo pago:', Object.fromEntries(formData));
            
            alert('✓ Pago emitido exitosamente');
            closeModal('modalEmitirPago');
            event.target.reset();
            setTimeout(() => location.reload(), 500);
        }

        // Ver Detalle
        function verDetalle(cuenta) {
            document.getElementById('detalle_id').textContent = '#' + String(cuenta.id).padStart(5, '0');
            document.getElementById('detalle_proveedor').textContent = cuenta.proveedor;
            document.getElementById('detalle_fecha').textContent = new Date(cuenta.fecha).toLocaleDateString('es-ES');
            document.getElementById('detalle_vencimiento').textContent = new Date(cuenta.vencimiento).toLocaleDateString('es-ES');
            document.getElementById('detalle_total').textContent = '$' + parseFloat(cuenta.total).toFixed(2);
            document.getElementById('detalle_saldo').textContent = '$' + parseFloat(cuenta.saldo).toFixed(2);
            document.getElementById('detalle_estado').innerHTML = `<span class="status-badge ${cuenta.status_class}">${cuenta.estado}</span>`;
            openModal('modalVerDetalle');
        }

        // Contactar Proveedor
        function contactarProveedor(id, proveedor) {
            if (confirm(`¿Desea contactar a ${proveedor}?`)) {
                // Aquí se enviaría la notificación
                console.log('Contactando proveedor:', id);
                alert('✓ Mensaje enviado al proveedor exitosamente');
            }
        }

        // Generar Reporte
        function generarReporte() {
            alert('Generando reporte...');
            // Implementar lógica de reportes
        }
    </script>
</body>

</html>