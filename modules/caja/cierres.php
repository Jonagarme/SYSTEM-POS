<?php
/**
 * Financial Closures Report - Cierres Diarios, Mensuales y Anuales
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'caja_cierres';

$periodo = $_GET['periodo'] ?? 'diario'; // diario, mensual, anual

// Definir la query base según el periodo
if ($periodo == 'mensual') {
    $selectLabel = "DATE_FORMAT(fechaEmision, '%M %Y') as etiqueta";

    $stmt = $pdo->prepare("
        SELECT 
            $selectLabel,
            COUNT(id) as total_transacciones,
            SUM(subtotal) as subtotal,
            SUM(iva) as iva,
            SUM(total) as total,
            MAX(fechaEmision) as fecha_referencia
        FROM facturas_venta
        WHERE anulado = 0
        GROUP BY etiqueta
        ORDER BY fecha_referencia DESC
        LIMIT 50");
    $stmt->execute();
    $cierres = $stmt->fetchAll();
} elseif ($periodo == 'anual') {
    $selectLabel = "YEAR(fechaEmision) as etiqueta";

    $stmt = $pdo->prepare("
        SELECT 
            $selectLabel,
            COUNT(id) as total_transacciones,
            SUM(subtotal) as subtotal,
            SUM(iva) as iva,
            SUM(total) as total,
            MAX(fechaEmision) as fecha_referencia
        FROM facturas_venta
        WHERE anulado = 0
        GROUP BY etiqueta
        ORDER BY fecha_referencia DESC
        LIMIT 50");
    $stmt->execute();
    $cierres = $stmt->fetchAll();
} else {
    // diario por defecto - Usamos la tabla cierres_caja para los turnos
    $stmt = $pdo->query("
        SELECT 
            c.id,
            CONCAT(ca.nombre, ' (', DATE_FORMAT(c.fechaApertura, '%d/%m %H:%i'), ')') as etiqueta,
            DATE_FORMAT(c.fechaApertura, '%d/%m/%Y') as fecha_fmt,
            u.nombreCompleto as usuario,
            c.saldoInicial,
            c.totalContadoFisico,
            c.estado,
            c.fechaApertura as fecha_referencia,
            (SELECT COUNT(*) FROM facturas_venta WHERE idCierreCaja = c.id AND anulado = 0) as total_transacciones,
            (SELECT SUM(total) FROM facturas_venta WHERE idCierreCaja = c.id AND anulado = 0) as total
        FROM cierres_caja c
        LEFT JOIN cajas ca ON c.idCaja = ca.id
        LEFT JOIN usuarios u ON c.idUsuarioApertura = u.id
        ORDER BY c.fechaApertura DESC
        LIMIT 100");
    $cierres = $stmt->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cierres Financieros | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .cierres-header-banner {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 30px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
        }

        .cierres-header-banner h1 {
            margin: 0;
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cierres-tabs {
            display: flex;
            gap: 10px;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 10px;
            margin-bottom: 25px;
            width: fit-content;
        }

        .tab-item {
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            color: #64748b;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .tab-item.active {
            background: white;
            color: #2563eb;
            box-shadow: var(--shadow-sm);
        }

        .tab-item:hover:not(.active) {
            background: rgba(255, 255, 255, 0.5);
            color: #1e293b;
        }

        .cierre-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border-collapse: collapse;
        }

        .cierre-table th {
            text-align: left;
            padding: 15px 20px;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #f1f5f9;
        }

        .cierre-table td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .cierre-table tr:hover {
            background: #fbfcfe;
        }

        .val-money {
            font-weight: 700;
            font-family: 'Courier New', Courier, monospace;
        }

        .badge-count {
            background: #eff6ff;
            color: #2563eb;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .btn-action-report {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .btn-action-report:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #f0f7ff;
        }

        .cierres-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card-cierre {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-card-cierre .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card-cierre .info {
            display: flex;
            flex-direction: column;
        }

        .stat-card-cierre .info .lbl {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
        }

        .stat-card-cierre .info .val {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
        }

        /* RESPONSIVE STYLES */
        @media (max-width: 1024px) {
            .cierres-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .cierres-header-banner {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
                padding: 20px;
            }

            .cierres-header-banner h1 {
                font-size: 1.3rem;
            }

            .cierres-tabs {
                width: 100%;
                justify-content: space-between;
                overflow-x: auto;
                padding: 4px;
            }

            .tab-item {
                flex: 1;
                text-align: center;
                padding: 10px 5px;
                font-size: 0.75rem;
                white-space: nowrap;
            }

            .table-responsive-container {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-radius: 12px;
                box-shadow: var(--shadow-sm);
            }

            .cierre-table {
                min-width: 800px;
            }

            .cierres-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-card-cierre {
                padding: 15px;
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
                <div class="cierres-header-banner">
                    <div>
                        <h1><i class="fas fa-file-invoice-dollar"></i> Reporte de Cierres Financieros</h1>
                        <p style="opacity: 0.8; margin-top: 5px;">Visualice el rendimiento de su negocio por periodos
                            específicos.</p>
                    </div>
                </div>

                <?php if (isset($_GET['success']) && $_GET['success'] == 'cierre' && isset($_GET['id_print'])): ?>
                    <div
                        style="background-color: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                            <span>¡Caja cerrada correctamente! ¿Desea imprimir el ticket de cierre?</span>
                        </div>
                        <a href="imprimir_cierre.php?id=<?php echo $_GET['id_print']; ?>" target="_blank"
                            style="background-color: #059669; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-print"></i> Imprimir Ticket
                        </a>
                    </div>
                <?php endif; ?>

                <div class="cierres-tabs">
                    <a href="?periodo=diario" class="tab-item <?php echo $periodo == 'diario' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-day"></i> Diarios / Turnos
                    </a>
                    <a href="?periodo=mensual" class="tab-item <?php echo $periodo == 'mensual' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> Mensuales
                    </a>
                    <a href="?periodo=anual" class="tab-item <?php echo $periodo == 'anual' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar"></i> Anuales
                    </a>
                </div>

                <div class="cierres-stats">
                    <?php
                    $stats = [
                        'transacciones' => 0,
                        'total' => 0
                    ];
                    foreach ($cierres as $c) {
                        $stats['transacciones'] += $c['total_transacciones'];
                        $stats['total'] += ($c['total'] ?? 0);
                    }
                    ?>
                    <div class="stat-card-cierre">
                        <div class="icon-box" style="background: #eff6ff; color: #2563eb;">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="info">
                            <span class="lbl">Transacciones Total</span>
                            <span class="val"><?php echo number_format($stats['transacciones']); ?></span>
                        </div>
                    </div>
                    <div class="stat-card-cierre">
                        <div class="icon-box" style="background: #f0fdf4; color: #059669;">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="info">
                            <span class="lbl">Recaudado (Sesiones)</span>
                            <span class="val">$ <?php echo number_format($stats['total'], 2); ?></span>
                        </div>
                    </div>
                    <div class="stat-card-cierre"
                        style="background: #fff7ed; border: 1px solid #ffedd5; border-left: 5px solid #c2410c;">
                        <div class="icon-box" style="background: #ffedd5; color: #c2410c;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="info">
                            <span class="lbl" style="color: #9a3412;">Eficiencia Promedio</span>
                            <span class="val" style="color: #c2410c; font-size: 1.5rem;">
                                <?php echo $stats['transacciones'] > 0 ? '$ ' . number_format($stats['total'] / $stats['transacciones'], 2) : '$ 0.00'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive-container">
                    <table class="cierre-table">
                        <thead>
                            <tr>
                                <th>Periodo / Etiqueta</th>
                                <?php if ($periodo == 'diario'): ?>
                                    <th>Usuario</th>
                                    <th style="text-align: right;">S. Inicial</th>
                                    <th style="text-align: right;">S. Final</th>
                                <?php endif; ?>
                                <th style="text-align: center;">Transacciones</th>
                                <th style="text-align: right;">Total Recaudado</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cierres)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 50px; color: #64748b;">
                                        <i class="fas fa-folder-open"
                                            style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                        No se encontraron datos para este periodo.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cierres as $c):
                                    $ref = new DateTime($c['fecha_referencia']);
                                    if ($periodo == 'mensual') {
                                        $f_inicio = $ref->format('Y-m-01');
                                        $f_fin = $ref->format('Y-m-t');
                                    } elseif ($periodo == 'anual') {
                                        $f_inicio = $ref->format('Y-01-01');
                                        $f_fin = $ref->format('Y-12-31');
                                    } else {
                                        $f_inicio = $ref->format('Y-m-d');
                                        $f_fin = $ref->format('Y-m-d');
                                    }
                                    ?>
                                    <tr>
                                        <td style="font-weight: 600; color: #1e293b;">
                                            <?php echo $c['etiqueta']; ?>
                                            <?php if ($periodo == 'diario'): ?>
                                                <br><small
                                                    style="color:<?php echo $c['estado'] == 'ABIERTA' ? '#059669' : '#64748b'; ?>"><?php echo $c['estado']; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($periodo == 'diario'): ?>
                                            <td><?php echo htmlspecialchars($c['usuario'] ?? '-'); ?></td>
                                            <td style="text-align: right;">$ <?php echo number_format($c['saldoInicial'], 2); ?>
                                            </td>
                                            <td style="text-align: right;">$
                                                <?php echo number_format($c['totalContadoFisico'] ?? 0, 2); ?>
                                            </td>
                                        <?php endif; ?>
                                        <td style="text-align: center;">
                                            <span class="badge-count"><?php echo $c['total_transacciones']; ?></span>
                                        </td>
                                        <td style="text-align: right; color: #059669; font-weight: 800;" class="val-money">
                                            $ <?php echo number_format($c['total'] ?? 0, 2); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($periodo == 'diario'): ?>
                                                <div style="display: flex; gap: 5px; justify-content: center;">
                                                    <a href="../ventas/index.php?idCierreCaja=<?php echo $c['id']; ?>"
                                                        class="btn-action-report"
                                                        style="text-decoration: none; display: inline-block;">
                                                        <i class="fas fa-search-plus"></i> Detalle
                                                    </a>
                                                    <a href="imprimir_cierre.php?id=<?php echo $c['id']; ?>" target="_blank"
                                                        class="btn-action-report"
                                                        style="text-decoration: none; display: inline-block; background-color: #2563eb;">
                                                        <i class="fas fa-print"></i> Ticket
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <a href="../ventas/index.php?fecha_inicio=<?php echo $f_inicio; ?>&fecha_fin=<?php echo $f_fin; ?>"
                                                    class="btn-action-report" style="text-decoration: none; display: inline-block;">
                                                    <i class="fas fa-search-plus"></i> Detalle
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>