<?php
/**
 * Sales Consolidation Report - Consolidado de Ventas
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'ventas_consolidado';

// Date range filters
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');

// Summary Stats for the period
$stmtStats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_ventas,
        SUM(subtotal) as sum_subtotal,
        SUM(iva) as sum_iva,
        SUM(total) as sum_total
    FROM facturas_venta 
    WHERE DATE(fechaEmision) BETWEEN ? AND ? AND anulado = 0
");
$stmtStats->execute([$fecha_inicio, $fecha_fin]);
$stats = $stmtStats->fetch();

// Consolidation by Day
$stmtCons = $pdo->prepare("
    SELECT 
        DATE(fechaEmision) as fecha,
        COUNT(*) as ventas,
        SUM(subtotal) as subtotal,
        SUM(iva) as iva,
        SUM(total) as total
    FROM facturas_venta
    WHERE DATE(fechaEmision) BETWEEN ? AND ? AND anulado = 0
    GROUP BY DATE(fechaEmision)
    ORDER BY fecha DESC
");
$stmtCons->execute([$fecha_inicio, $fecha_fin]);
$consolidado = $stmtCons->fetchAll();

// Top Products Sold in the period
$stmtTop = $pdo->prepare("
    SELECT 
        productoNombre,
        SUM(cantidad) as cantidad
    FROM facturas_venta_detalle fd
    JOIN facturas_venta f ON f.id = fd.idFacturaVenta
    WHERE DATE(f.fechaEmision) BETWEEN ? AND ? AND f.anulado = 0
    GROUP BY productoNombre
    ORDER BY cantidad DESC
    LIMIT 5
");
$stmtTop->execute([$fecha_inicio, $fecha_fin]);
$top_productos = $stmtTop->fetchAll();

// Note: Payment methods would normally come from a separate payments table. 
// For now, if not available, we can mock or show a simplified split if 'estado' implies something.
$metodos_pago = [
    ['metodo' => 'Total Liquido', 'monto' => $stats['sum_total'] ?? 0, 'porc' => 100],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consolidado de Ventas | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .report-header-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .report-header-banner::after {
            content: '';
            position: absolute;
            right: -20px;
            bottom: -20px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .report-header-banner h1 {
            margin: 0;
            font-size: 1.7rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .report-header-banner p {
            margin: 8px 0 0 45px;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid #4f46e5;
        }

        .stat-card h3 {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
        }

        .stat-card .trend {
            font-size: 0.75rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .trend.up {
            color: #10b981;
        }

        .trend.down {
            color: #ef4444;
        }

        .report-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .panel-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .consolidated-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .consolidated-table th {
            text-align: left;
            padding: 15px 20px;
            background: #f8fafc;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 2px solid #f1f5f9;
        }

        .consolidated-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #1e293b;
        }

        .consolidated-table tr:last-child {
            background: #f1f5f9;
            font-weight: 700;
        }

        .payment-method-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .method-info {
            flex: 1;
        }

        .method-name {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }

        .progress-bar {
            height: 8px;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #4f46e5;
            border-radius: 4px;
        }

        .method-value {
            font-size: 0.85rem;
            font-weight: 700;
            margin-left: 20px;
            text-align: right;
            min-width: 80px;
        }

        .date-filter-row {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn-period {
            background: #f1f5f9;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-period.active {
            background: #4f46e5;
            color: white;
        }

        /* RESPONSIVE STYLES */
        @media (max-width: 1024px) {
            .summary-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .report-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .report-header-banner {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                padding: 20px;
            }

            .report-header-banner p {
                margin-left: 0;
            }

            .date-filter-row {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .filter-group {
                flex-wrap: wrap;
                width: 100%;
            }

            .filter-group input[type="date"] {
                flex: 1;
                min-width: 120px;
            }

            .summary-stats {
                grid-template-columns: 1fr;
            }

            .table-responsive-container {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 12px;
            }

            .consolidated-table {
                min-width: 600px;
            }

            .report-grid {
                gap: 15px;
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
                <div class="report-header-banner">
                    <div>
                        <h1><i class="fas fa-chart-pie"></i> Consolidado de Ventas</h1>
                        <p>Resumen detallado de ingresos y movimientos comerciales</p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline"
                            style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); color: white;">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </button>
                        <button class="btn btn-outline"
                            style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); color: white;">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                    </div>
                </div>

                <div class="date-filter-row">
                    <div class="filter-group">
                        <a href="?fecha_inicio=<?php echo date('Y-m-d'); ?>&fecha_fin=<?php echo date('Y-m-d'); ?>"
                            class="btn-period <?php echo ($_GET['fecha_inicio'] ?? '') == date('Y-m-d') ? 'active' : ''; ?>">Hoy</a>
                        <a href="?fecha_inicio=<?php echo date('Y-m-d', strtotime('-1 day')); ?>&fecha_fin=<?php echo date('Y-m-d', strtotime('-1 day')); ?>"
                            class="btn-period">Ayer</a>
                        <a href="?fecha_inicio=<?php echo date('Y-m-01'); ?>&fecha_fin=<?php echo date('Y-m-t'); ?>"
                            class="btn-period <?php echo ($_GET['fecha_inicio'] ?? '') == date('Y-m-01') ? 'active' : ''; ?>">Este
                            Mes</a>
                    </div>
                    <form class="filter-group" method="GET">
                        <label style="font-size: 0.8rem; font-weight: 600; color: #64748b;">Rango personalizado:</label>
                        <input type="date" name="fecha_inicio" class="form-control"
                            style="width: 150px; padding: 6px 10px;" value="<?php echo $fecha_inicio; ?>">
                        <span style="color: #cbd5e1;">-</span>
                        <input type="date" name="fecha_fin" class="form-control"
                            style="width: 150px; padding: 6px 10px;" value="<?php echo $fecha_fin; ?>">
                        <button type="submit" class="btn btn-primary" style="padding: 6px 15px;"><i
                                class="fas fa-sync-alt"></i></button>
                    </form>
                </div>

                <div class="summary-stats">
                    <div class="stat-card">
                        <h3>Total Ventas</h3>
                        <div class="value"><?php echo $stats['total_ventas']; ?> Ventas</div>
                        <div class="trend up"><i class="fas fa-calendar"></i> Periodo Seleccionado</div>
                    </div>
                    <div class="stat-card" style="border-left-color: #10b981;">
                        <h3>Ventas Netas</h3>
                        <div class="value">$ <?php echo number_format($stats['sum_subtotal'] ?? 0, 2); ?></div>
                        <div class="trend up"><i class="fas fa-chart-line"></i> Subtotal sin IVA</div>
                    </div>
                    <div class="stat-card" style="border-left-color: #f59e0b;">
                        <h3>Impuesto (IVA)</h3>
                        <div class="value">$ <?php echo number_format($stats['sum_iva'] ?? 0, 2); ?></div>
                        <div class="trend"><i class="fas fa-info-circle"></i> IVA Aplicado</div>
                    </div>
                    <div class="stat-card" style="border-left-color: #6366f1;">
                        <h3>Total Consolidado</h3>
                        <div class="value">$ <?php echo number_format($stats['sum_total'] ?? 0, 2); ?></div>
                        <div class="trend up"><i class="fas fa-check-circle"></i> Monto Liquido</div>
                    </div>
                </div>

                <div class="report-grid">
                    <div class="pos-panel" style="padding: 25px;">
                        <h3 class="panel-title"><i class="fas fa-calendar-alt"></i> Movimientos por Día</h3>
                        <div class="table-responsive-container">
                            <table class="consolidated-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Transacciones</th>
                                        <th>Subtotal</th>
                                        <th>IVA</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($consolidado as $c): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($c['fecha'])); ?></td>
                                            <td><?php echo $c['ventas']; ?></td>
                                            <td>$ <?php echo number_format($c['subtotal'], 2); ?></td>
                                            <td>$ <?php echo number_format($c['iva'], 2); ?></td>
                                            <td style="font-weight: 700;">$ <?php echo number_format($c['total'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td>TOTAL PERIODO</td>
                                        <td><?php echo $stats['total_ventas']; ?></td>
                                        <td>$ <?php echo number_format($stats['sum_subtotal'] ?? 0, 2); ?></td>
                                        <td>$ <?php echo number_format($stats['sum_iva'] ?? 0, 2); ?></td>
                                        <td>$ <?php echo number_format($stats['sum_total'] ?? 0, 2); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="pos-panel" style="padding: 25px;">
                        <h3 class="panel-title"><i class="fas fa-wallet"></i> Resumen de Ingresos</h3>
                        <div style="margin-top: 20px;">
                            <?php foreach ($metodos_pago as $m): ?>
                                <div class="payment-method-item">
                                    <div class="method-info">
                                        <span class="method-name"><?php echo $m['metodo']; ?></span>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $m['porc']; ?>%;"></div>
                                        </div>
                                    </div>
                                    <div class="method-value">
                                        $ <?php echo number_format($m['monto'], 2); ?>
                                        <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 400;">
                                            <?php echo $m['porc']; ?>% del total</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="margin-top: 35px; padding-top: 20px; border-top: 1px dashed #e2e8f0;">
                            <h4 style="font-size: 0.75rem; color: #64748b; margin-bottom: 15px;">TOP PRODUCTOS VENDIDOS
                            </h4>
                            <?php if (empty($top_productos)): ?>
                                <p style="font-size: 0.8rem; color: #94a3b8;">No hay datos para este periodo.</p>
                            <?php else: ?>
                                <?php foreach ($top_productos as $tp): ?>
                                    <div
                                        style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 10px;">
                                        <span><?php echo htmlspecialchars($tp['productoNombre']); ?></span>
                                        <span style="font-weight: 700;"><?php echo number_format($tp['cantidad'], 0); ?>
                                            u.</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>