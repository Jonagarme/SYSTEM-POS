<?php
/**
 * Cuentas por Cobrar - Accounting Module
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'contabilidad_cobrar';

// Cargar clientes desde la base de datos
$clientes = [];
try {
    $stmt = $pdo->query("SELECT id, CONCAT(nombres, ' ', apellidos) As nombre, cedula_ruc as identificacion FROM clientes WHERE estado = 1 AND anulado = 0 ORDER BY nombre");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar clientes: " . $e->getMessage());
}

// Dummy data for initial UI demonstration
$cuentas = [
    [
        'id' => 1,
        'cliente' => 'Juan Pérez',
        'fecha' => '2026-01-15',
        'vencimiento' => '2026-02-15',
        'total' => 150.50,
        'saldo' => 50.50,
        'estado' => 'Pendiente',
        'status_class' => 'status-pending'
    ],
    [
        'id' => 2,
        'cliente' => 'María García',
        'fecha' => '2026-01-20',
        'vencimiento' => '2026-02-20',
        'total' => 300.00,
        'saldo' => 300.00,
        'estado' => 'Vencida',
        'status_class' => 'status-overdue'
    ],
    [
        'id' => 3,
        'cliente' => 'Empresa ABC S.A.',
        'fecha' => '2026-01-10',
        'vencimiento' => '2026-02-10',
        'total' => 1200.00,
        'saldo' => 0.00,
        'estado' => 'Pagada',
        'status_class' => 'status-paid'
    ]
];

$totales = [
    'total_cartera' => 1650.50,
    'por_cobrar' => 350.50,
    'vencido' => 300.00,
    'cobrado' => 1300.00
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas por Cobrar | Warehouse POS</title>
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

        .btn-info {
            background: #0ea5e9;
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

        .icon-orange {
            background: #fff7ed;
            color: #f59e0b;
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
            color: #2563eb;
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

        /* Autocomplete Styles */
        .autocomplete-container {
            position: relative;
        }

        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .autocomplete-results.active {
            display: block;
        }

        .autocomplete-item {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s;
        }

        .autocomplete-item:hover,
        .autocomplete-item.selected {
            background: #f8fafc;
        }

        .autocomplete-item .item-name {
            font-weight: 600;
            color: #1e293b;
            display: block;
        }

        .autocomplete-item .item-id {
            font-size: 0.8rem;
            color: #64748b;
            display: block;
        }

        .autocomplete-no-results {
            padding: 10px 14px;
            color: #64748b;
            text-align: center;
            font-size: 0.9rem;
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
                    <h1><i class="fas fa-hand-holding-usd"></i> Cuentas por Cobrar</h1>
                    <div class="header-actions">
                        <button class="btn-accounting btn-primary" onclick="openNuevaCuentaModal()"><i class="fas fa-plus"></i> Nueva Cuenta</button>
                        <button class="btn-accounting btn-success" onclick="exportarExcel()"><i class="fas fa-file-excel"></i> Exportar</button>
                        <button class="btn-accounting btn-info" onclick="generarReporte()"><i class="fas fa-print"></i> Reportes</button>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Cartera</span>
                            <span class="stat-value">$
                                <?php echo number_format($totales['total_cartera'], 2); ?>
                            </span>
                        </div>
                        <div class="stat-icon icon-blue">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Por Cobrar</span>
                            <span class="stat-value">$
                                <?php echo number_format($totales['por_cobrar'], 2); ?>
                            </span>
                        </div>
                        <div class="stat-icon icon-orange">
                            <i class="fas fa-clock"></i>
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
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Cobrado</span>
                            <span class="stat-value">$
                                <?php echo number_format($totales['cobrado'], 2); ?>
                            </span>
                        </div>
                        <div class="stat-icon icon-green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Buscar por cliente o factura...">
                        </div>
                        <div class="filter-options">
                            <select class="form-select"
                                style="padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <option value="">Todos los Estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagada">Pagada</option>
                                <option value="vencida">Vencida</option>
                            </select>
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="acc-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
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
                                            <?php echo $c['cliente']; ?>
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
                                            <button class="btn-icon" title="Registrar Pago" style="color: #059669;" onclick='registrarPago(<?php echo json_encode($c); ?>)'><i
                                                    class="fas fa-money-bill-wave"></i></button>
                                            <button class="btn-icon" title="Enviar Recordatorio" style="color: #0ea5e9;" onclick='enviarRecordatorio(<?php echo $c["id"]; ?>, "<?php echo addslashes($c["cliente"]); ?>")'><i
                                                    class="fas fa-paper-plane"></i></button>
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

    <!-- Modal Nueva Cuenta -->
    <div class="modal-overlay" id="modalNuevaCuenta">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Nueva Cuenta por Cobrar</h2>
                <button class="modal-close" onclick="closeModal('modalNuevaCuenta')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formNuevaCuenta" onsubmit="guardarNuevaCuenta(event)">
                    <div class="form-group">
                        <label>Cliente *</label>
                        <div class="autocomplete-container">
                            <input type="text" 
                                   id="cliente_search" 
                                   placeholder="Buscar por nombre o cédula/RUC..." 
                                   autocomplete="off"
                                   oninput="searchCliente(this.value)"
                                   onfocus="searchCliente(this.value)"
                                   required>
                            <input type="hidden" name="cliente_id" id="cliente_id" required>
                            <div class="autocomplete-results" id="cliente_results"></div>
                        </div>
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
                        <label>Observaciones</label>
                        <textarea name="observaciones" placeholder="Notas adicionales..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('modalNuevaCuenta')">Cancelar</button>
                <button type="submit" form="formNuevaCuenta" class="btn-modal btn-submit">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Pago -->
    <div class="modal-overlay" id="modalRegistrarPago">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-money-bill-wave"></i> Registrar Pago</h2>
                <button class="modal-close" onclick="closeModal('modalRegistrarPago')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarPago" onsubmit="guardarPago(event)">
                    <input type="hidden" name="cuenta_id" id="pago_cuenta_id">
                    <div class="detail-row">
                        <span class="detail-label">Cliente:</span>
                        <span class="detail-value" id="pago_cliente"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Saldo Pendiente:</span>
                        <span class="detail-value" style="color: #dc2626;" id="pago_saldo"></span>
                    </div>
                    <hr style="margin: 20px 0; border: none; border-top: 1px solid #f1f5f9;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Monto del Pago *</label>
                            <input type="number" name="monto" id="pago_monto" step="0.01" placeholder="0.00" required>
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
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="cheque">Cheque</option>
                            <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Referencia/Comprobante</label>
                        <input type="text" name="referencia" placeholder="Número de comprobante o referencia">
                    </div>
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea name="notas" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('modalRegistrarPago')">Cancelar</button>
                <button type="submit" form="formRegistrarPago" class="btn-modal btn-submit">Registrar Pago</button>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalle -->
    <div class="modal-overlay" id="modalVerDetalle">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-file-invoice"></i> Detalle de Cuenta</h2>
                <button class="modal-close" onclick="closeModal('modalVerDetalle')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detail-row">
                    <span class="detail-label">ID Cuenta:</span>
                    <span class="detail-value" id="detalle_id"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cliente:</span>
                    <span class="detail-value" id="detalle_cliente"></span>
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
        // Array de clientes desde PHP
        const clientes = <?php echo json_encode($clientes); ?>;
        let selectedClienteIndex = -1;

        // Funciones de Autocompletado
        function searchCliente(query) {
            const resultsContainer = document.getElementById('cliente_results');
            
            if (!query || query.length < 2) {
                resultsContainer.classList.remove('active');
                return;
            }

            const filtered = clientes.filter(c => {
                const nombre = (c.nombre || '').toLowerCase();
                const identificacion = (c.identificacion || '').toLowerCase();
                const searchTerm = query.toLowerCase();
                return nombre.includes(searchTerm) || identificacion.includes(searchTerm);
            });

            if (filtered.length === 0) {
                resultsContainer.innerHTML = '<div class="autocomplete-no-results">No se encontraron clientes</div>';
                resultsContainer.classList.add('active');
                return;
            }

            resultsContainer.innerHTML = filtered.map((cliente, index) => `
                <div class="autocomplete-item" onclick="selectCliente(${cliente.id}, '${escapeHtml(cliente.nombre)}', '${escapeHtml(cliente.identificacion || '')}')">
                    <span class="item-name">${escapeHtml(cliente.nombre)}</span>
                    <span class="item-id">${escapeHtml(cliente.identificacion || 'Sin identificación')}</span>
                </div>
            `).join('');
            
            resultsContainer.classList.add('active');
        }

        function selectCliente(id, nombre, identificacion) {
            document.getElementById('cliente_id').value = id;
            document.getElementById('cliente_search').value = nombre + (identificacion ? ' - ' + identificacion : '');
            document.getElementById('cliente_results').classList.remove('active');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Cerrar autocompletado al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.autocomplete-container')) {
                document.getElementById('cliente_results').classList.remove('active');
            }
        });

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

        // Nueva Cuenta
        function openNuevaCuentaModal() {
            openModal('modalNuevaCuenta');
        }

        function guardarNuevaCuenta(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            // Aquí se enviaría la información al servidor
            console.log('Guardando nueva cuenta:', Object.fromEntries(formData));
            
            alert('✓ Cuenta por cobrar creada exitosamente');
            closeModal('modalNuevaCuenta');
            event.target.reset();
            // Recargar la página o actualizar la tabla
            setTimeout(() => location.reload(), 500);
        }

        // Registrar Pago
        function registrarPago(cuenta) {
            document.getElementById('pago_cuenta_id').value = cuenta.id;
            document.getElementById('pago_cliente').textContent = cuenta.cliente;
            document.getElementById('pago_saldo').textContent = '$' + parseFloat(cuenta.saldo).toFixed(2);
            document.getElementById('pago_monto').max = cuenta.saldo;
            openModal('modalRegistrarPago');
        }

        function guardarPago(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const monto = parseFloat(formData.get('monto'));
            const saldo = parseFloat(document.getElementById('pago_saldo').textContent.replace('$', ''));
            
            if (monto > saldo) {
                alert('⚠ El monto no puede ser mayor al saldo pendiente');
                return;
            }
            
            // Aquí se enviaría la información al servidor
            console.log('Registrando pago:', Object.fromEntries(formData));
            
            alert('✓ Pago registrado exitosamente');
            closeModal('modalRegistrarPago');
            event.target.reset();
            setTimeout(() => location.reload(), 500);
        }

        // Ver Detalle
        function verDetalle(cuenta) {
            document.getElementById('detalle_id').textContent = '#' + String(cuenta.id).padStart(5, '0');
            document.getElementById('detalle_cliente').textContent = cuenta.cliente;
            document.getElementById('detalle_fecha').textContent = new Date(cuenta.fecha).toLocaleDateString('es-ES');
            document.getElementById('detalle_vencimiento').textContent = new Date(cuenta.vencimiento).toLocaleDateString('es-ES');
            document.getElementById('detalle_total').textContent = '$' + parseFloat(cuenta.total).toFixed(2);
            document.getElementById('detalle_saldo').textContent = '$' + parseFloat(cuenta.saldo).toFixed(2);
            document.getElementById('detalle_estado').innerHTML = `<span class="status-badge ${cuenta.status_class}">${cuenta.estado}</span>`;
            openModal('modalVerDetalle');
        }

        // Enviar Recordatorio
        function enviarRecordatorio(id, cliente) {
            if (confirm(`¿Desea enviar recordatorio de pago a ${cliente}?`)) {
                // Aquí se enviaría la notificación
                console.log('Enviando recordatorio a cuenta:', id);
                alert('✓ Recordatorio enviado exitosamente');
            }
        }

        // Exportar a Excel
        function exportarExcel() {
            alert('Exportando a Excel...');
            // Implementar lógica de exportación
        }

        // Generar Reporte
        function generarReporte() {
            alert('Generando reporte...');
            // Implementar lógica de reportes
        }
    </script>
</body>

</html>