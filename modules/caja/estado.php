<?php
/**
 * Caja Status - Estado de Caja
 */
session_start();
require_once '../../includes/db.php';

// 1. Buscar la sesión de caja abierta
$stmt = $pdo->query("SELECT c.*, ca.nombre as caja_nombre, u.nombreCompleto as usuario_nombre 
                     FROM cierres_caja c 
                     LEFT JOIN cajas ca ON c.idCaja = ca.id 
                     LEFT JOIN usuarios u ON c.idUsuarioApertura = u.id 
                     WHERE c.estado = 'ABIERTA' 
                     ORDER BY c.fechaApertura DESC 
                     LIMIT 1");
$sesion = $stmt->fetch();

$caja_abierta = (bool)$sesion;
$resumen_ventas = ['total' => 0.00, 'cantidad' => 0];
$ventas_recientes = [];

if ($caja_abierta) {
    // 2. Obtener resumen de ventas de esta sesión
    $stmtV = $pdo->prepare("SELECT COUNT(*) as cantidad, SUM(total) as total 
                            FROM facturas_venta 
                            WHERE idCierreCaja = ? AND anulado = 0");
    $stmtV->execute([$sesion['id']]);
    $resumen_ventas = $stmtV->fetch();
    $resumen_ventas['total'] = $resumen_ventas['total'] ?? 0.00;

    // 3. Obtener ventas recientes (últimas 5)
    $stmtR = $pdo->prepare("SELECT v.*, cl.razonSocial as cliente_nombre 
                            FROM facturas_venta v 
                            LEFT JOIN clientes cl ON v.idCliente = cl.id 
                            WHERE v.idCierreCaja = ? AND v.anulado = 0 
                            ORDER BY v.fechaEmision DESC 
                            LIMIT 5");
    $stmtR->execute([$sesion['id']]);
    $ventas_recientes = $stmtR->fetchAll();
}

$current_page = 'caja_estado';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Caja | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .header-estado {
            margin-bottom: 25px;
        }

        .header-estado h1 {
            font-size: 1.5rem;
            color: #1e293b;
            font-weight: 700;
        }

        .main-grid-estado {
            display: grid;
            grid-template-columns: 350px 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .panel-estado {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .panel-estado-header {
            padding: 12px 20px;
            background: #0ea5e9;
            color: white;
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-estado-body {
            padding: 20px;
        }

        .info-row-estado {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .info-row-estado .label {
            font-weight: 700;
            color: #1e293b;
        }

        .info-row-estado .value {
            color: #475569;
        }

        .btn-cierre-caja {
            width: 100%;
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-weight: 700;
            margin-top: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-abrir-caja {
            width: 100%;
            background: #059669;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-weight: 700;
            margin-top: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-movimientos-link {
            width: 100%;
            background: white;
            color: #2563eb;
            border: 1px solid #2563eb;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .card-stat-caja {
            background: #2563eb;
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }

        .card-stat-caja .lbl {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .card-stat-caja .val {
            font-size: 2rem;
            font-weight: 800;
        }

        .card-stat-green {
            background: #059669;
        }

        .ventas-section {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .ventas-header {
            padding: 12px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ventas-empty {
            padding: 40px;
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
        }

        .movimientos-recientes {
            margin-top: 25px;
            background: white;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
        }

        .table-custom th {
            text-align: left;
            padding: 12px;
            background: #f8fafc;
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
        }

        .table-custom td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        /* RESPONSIVE STYLES */
        @media (max-width: 1024px) {
            .main-grid-estado {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-grid-estado {
                grid-template-columns: 1fr;
            }

            .header-estado h1 {
                font-size: 1.25rem;
                text-align: center;
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
                <div class="header-estado flex justify-between items-center" style="display: flex; justify-content: space-between; align-items: center;">
                    <h1>Estado de Caja</h1>
                    <button onclick="location.reload()" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>

                <div class="main-grid-estado">
                    <!-- Left Panel: Caja Info -->
                    <div class="panel-estado">
                        <div class="panel-estado-header">
                            <i class="fas fa-cash-register"></i> Información de Caja
                        </div>
                        <div class="panel-estado-body">
                            <div class="info-row-estado">
                                <span class="label">Caja:</span>
                                <span class="value"><?php echo $caja_abierta ? htmlspecialchars($sesion['caja_nombre']) : 'CERRADA'; ?></span>
                            </div>
                            <div class="info-row-estado">
                                <span class="label">Apertura:</span>
                                <span class="value"><?php echo $caja_abierta ? date('d/m/Y H:i', strtotime($sesion['fechaApertura'])) : '-'; ?></span>
                            </div>
                            <div class="info-row-estado">
                                <span class="label">Saldo Inicial:</span>
                                <span class="value">$ <?php echo $caja_abierta ? number_format($sesion['saldoInicial'], 2) : '0.00'; ?></span>
                            </div>
                            <div class="info-row-estado">
                                <span class="label">Usuario:</span>
                                <span class="value"><?php echo $caja_abierta ? htmlspecialchars($sesion['usuario_nombre']) : '-'; ?></span>
                            </div>

                            <?php if ($caja_abierta): ?>
                            <a href="cerrar_caja.php" class="btn-cierre-caja" style="text-decoration: none;">
                                <i class="fas fa-lock"></i> Cerrar Caja
                            </a>
                            <?php else: ?>
                            <a href="aperturas.php" class="btn-abrir-caja" style="text-decoration: none;">
                                <i class="fas fa-key"></i> Abrir Caja
                            </a>
                            <?php endif; ?>
                            
                            <a href="movimientos.php" class="btn-movimientos-link">
                                <i class="fas fa-exchange-alt"></i> Movimientos
                            </a>
                        </div>
                    </div>

                    <!-- Middle Stat: Ventas Realizadas -->
                    <div class="card-stat-caja">
                        <span class="val"><?php echo $resumen_ventas['cantidad']; ?></span>
                        <span class="lbl">Ventas Realizadas (Sesión)</span>
                    </div>

                    <!-- Right Stat: Total en Ventas -->
                    <div class="card-stat-caja card-stat-green">
                        <i class="fas fa-dollar-sign" style="font-size: 1.5rem; margin-bottom: 10px;"></i>
                        <span class="lbl">Total en Ventas (Sesión)</span>
                        <span class="val">$ <?php echo number_format($resumen_ventas['total'], 2); ?></span>
                    </div>
                </div>

                <!-- Ventas Section -->
                <div class="ventas-section">
                    <div class="ventas-header">
                        <i class="fas fa-receipt"></i> Últimas Ventas de la Sesión
                    </div>
                    <?php if (empty($ventas_recientes)): ?>
                    <div class="ventas-empty">
                        No se han realizado ventas en esta sesión
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Factura</th>
                                    <th>Cliente</th>
                                    <th>Fecha/Hora</th>
                                    <th>Estado</th>
                                    <th style="text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas_recientes as $v): ?>
                                <tr>
                                    <td class="font-bold"><?php echo htmlspecialchars($v['numeroFactura']); ?></td>
                                    <td><?php echo htmlspecialchars($v['cliente_nombre'] ?? 'Consumidor Final'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($v['fechaEmision'])); ?></td>
                                    <td>
                                        <span class="badge badge-success"><?php echo $v['estado']; ?></span>
                                    </td>
                                    <td style="text-align: right;" class="font-bold">$ <?php echo number_format($v['total'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Movimientos Section -->
                <div class="movimientos-recientes">
                    <div class="ventas-header">
                        <i class="fas fa-exchange-alt"></i> Salidas de Efectivo / Gastos
                    </div>
                    <div class="ventas-empty">
                        No hay egresos registrados en esta sesión
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>