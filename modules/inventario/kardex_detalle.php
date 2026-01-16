<?php
/**
 * Product Specific Kardex - Kardex por Producto
 */
session_start();
require_once '../../includes/db.php';

$idProducto = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$idProducto) {
    if (isset($_GET['ajax'])) {
        echo "ID de producto no proporcionado.";
        exit;
    }
    header('Location: kardex.php');
    exit;
}

try {
    // Get product info
    $stmt = $pdo->prepare("SELECT nombre, codigoPrincipal FROM productos WHERE id = ?");
    $stmt->execute([$idProducto]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        if (isset($_GET['ajax'])) {
            echo "Producto no encontrado (ID: $idProducto).";
            exit;
        }
        header('Location: kardex.php');
        exit;
    }

    // Get stats for this product
    $stats_query = "SELECT 
                        COUNT(*) as total,
                        SUM(ingreso) as total_entradas,
                        SUM(egreso) as total_salidas,
                        (SELECT saldo FROM kardex_movimientos WHERE idProducto = :id_sub ORDER BY fecha DESC, id DESC LIMIT 1) as saldo_actual
                    FROM kardex_movimientos 
                    WHERE idProducto = :id_main";
    $stmt_stats = $pdo->prepare($stats_query);
    $stmt_stats->execute([':id_sub' => $idProducto, ':id_main' => $idProducto]);
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

    // Get movements
    $query = "SELECT * FROM kardex_movimientos 
              WHERE idProducto = :id 
              ORDER BY fecha DESC, id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $idProducto]);
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = $e->getMessage();
    $movimientos = [];
    $stats = ['total' => 0, 'total_entradas' => 0, 'total_salidas' => 0, 'saldo_actual' => 0];
    if (isset($_GET['ajax'])) {
        echo "Error en la base de datos: " . htmlspecialchars($error);
        exit;
    }
}

$current_page = 'inventario_kardex';

// If it's an AJAX request, we only want the content part
if (isset($_GET['ajax'])) {
    ?>
    <div class="kardex-title" style="margin-bottom: 20px;">
        <h1 style="font-size: 1.1rem;"><i class="fas fa-chart-line"></i> Kardex:
            <?php echo htmlspecialchars($producto['nombre']); ?></h1>
        <p style="font-size: 0.8rem; color: #64748b; margin: 5px 0 0 0;">Código:
            <?php echo htmlspecialchars($producto['codigoPrincipal']); ?></p>
    </div>

    <div class="summary-cards-kardex"
        style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px;">
        <div class="k-card k-blue" style="padding: 12px; height: 70px;">
            <div class="info">
                <h3 style="font-size: 0.7rem; margin: 0;">Saldo</h3>
                <div class="value" style="font-size: 1.1rem;"><?php echo number_format($stats['saldo_actual'] ?? 0, 2); ?>
                </div>
            </div>
        </div>
        <div class="k-card k-green" style="padding: 12px; height: 70px;">
            <div class="info">
                <h3 style="font-size: 0.7rem; margin: 0;">Ingresos</h3>
                <div class="value" style="font-size: 1.1rem;"><?php echo number_format($stats['total_entradas'] ?? 0, 2); ?>
                </div>
            </div>
        </div>
        <div class="k-card k-red" style="padding: 12px; height: 70px;">
            <div class="info">
                <h3 style="font-size: 0.7rem; margin: 0;">Egresos</h3>
                <div class="value" style="font-size: 1.1rem;"><?php echo number_format($stats['total_salidas'] ?? 0, 2); ?>
                </div>
            </div>
        </div>
        <div class="k-card k-cyan" style="padding: 12px; height: 70px;">
            <div class="info">
                <h3 style="font-size: 0.7rem; margin: 0;">Movs</h3>
                <div class="value" style="font-size: 1.1rem;"><?php echo number_format($stats['total'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <div class="table-responsive"
        style="max-height: 400px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 8px;">
        <table class="k-table" style="width: 100%;">
            <thead style="position: sticky; top: 0; z-index: 10; background: #f8fafc;">
                <tr>
                    <th style="padding: 8px; font-size: 0.7rem;">Fecha</th>
                    <th style="padding: 8px; font-size: 0.7rem;">Tipo</th>
                    <th style="padding: 8px; font-size: 0.7rem; text-align: right;">Ing</th>
                    <th style="padding: 8px; font-size: 0.7rem; text-align: right;">Egr</th>
                    <th style="padding: 8px; font-size: 0.7rem; text-align: right;">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: #64748b; font-size: 0.8rem;">
                            No hay movimientos para este producto (ID: <?php echo $idProducto; ?>)
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m):
                        $bg_class = 'bg-default';
                        if (strpos($m['tipoMovimiento'], 'VENTA') !== false)
                            $bg_class = 'bg-venta';
                        elseif (strpos($m['tipoMovimiento'], 'COMPRA') !== false)
                            $bg_class = 'bg-compra';
                        elseif (strpos($m['tipoMovimiento'], 'AJUSTE') !== false)
                            $bg_class = 'bg-ajuste';
                        ?>
                        <tr>
                            <td style="padding: 8px; font-size: 0.75rem; white-space: nowrap;">
                                <?php echo date('d/m/Y H:i', strtotime($m['fecha'])); ?>
                            </td>
                            <td style="padding: 8px;">
                                <span class="type-pill <?php echo $bg_class; ?>"
                                    style="font-size: 0.6rem; padding: 1px 4px; border-radius: 4px; color: white;">
                                    <?php echo $m['tipoMovimiento']; ?>
                                </span>
                            </td>
                            <td style="padding: 8px; font-size: 0.75rem; text-align: right; color: #10b981;">
                                <?php echo $m['ingreso'] > 0 ? number_format($m['ingreso'], 2) : '-'; ?>
                            </td>
                            <td style="padding: 8px; font-size: 0.75rem; text-align: right; color: #ef4444;">
                                <?php echo $m['egreso'] > 0 ? number_format($m['egreso'], 2) : '-'; ?>
                            </td>
                            <td style="padding: 8px; font-size: 0.75rem; text-align: right; font-weight: 700;">
                                <?php echo number_format($m['saldo'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex del Producto | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .kardex-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }

        .kardex-title h1 {
            font-size: 1.25rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .kardex-title p {
            margin: 5px 0 0 35px;
            color: #64748b;
            font-size: 0.85rem;
        }

        .summary-cards-kardex {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .k-card {
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
        }

        .k-card .info h3 {
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .k-card .info .value {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .k-card .icon {
            font-size: 2rem;
            opacity: 0.3;
        }

        .k-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .k-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .k-red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .k-cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .k-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .k-table th {
            background: #f8fafc;
            color: #64748b;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #f1f5f9;
        }

        .k-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #1e293b;
        }

        .type-pill {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
        }

        .bg-venta {
            background-color: #6366f1;
        }

        .bg-compra {
            background-color: #10b981;
        }

        .bg-ajuste {
            background-color: #f59e0b;
        }

        .bg-default {
            background-color: #94a3b8;
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
                <div class="kardex-header">
                    <div class="kardex-title">
                        <h1><i class="fas fa-chart-line"></i> Kardex Detallado</h1>
                        <p><?php echo htmlspecialchars($producto['nombre']); ?>
                            (<?php echo htmlspecialchars($producto['codigoPrincipal']); ?>)</p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="kardex.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button class="btn btn-primary">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>

                <div class="summary-cards-kardex">
                    <div class="k-card k-blue">
                        <div class="info">
                            <h3>Saldo Actual</h3>
                            <div class="value"><?php echo number_format($stats['saldo_actual'] ?? 0, 2); ?></div>
                        </div>
                        <i class="fas fa-boxes icon"></i>
                    </div>
                    <div class="k-card k-green">
                        <div class="info">
                            <h3>Total Ingresos</h3>
                            <div class="value"><?php echo number_format($stats['total_entradas'] ?? 0, 2); ?></div>
                        </div>
                        <i class="fas fa-arrow-up icon"></i>
                    </div>
                    <div class="k-card k-red">
                        <div class="info">
                            <h3>Total Egresos</h3>
                            <div class="value"><?php echo number_format($stats['total_salidas'] ?? 0, 2); ?></div>
                        </div>
                        <i class="fas fa-arrow-down icon"></i>
                    </div>
                    <div class="k-card k-cyan">
                        <div class="info">
                            <h3>Movimientos</h3>
                            <div class="value"><?php echo number_format($stats['total'] ?? 0); ?></div>
                        </div>
                        <i class="fas fa-exchange-alt icon"></i>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="k-table">
                        <thead>
                            <tr>
                                <th>Fecha / Hora</th>
                                <th>Tipo</th>
                                <th>Detalle / Referencia</th>
                                <th style="text-align: right;">Ingreso</th>
                                <th style="text-align: right;">Egreso</th>
                                <th style="text-align: right;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimientos)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                                        No hay movimientos registrados para este producto.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($movimientos as $m):
                                    $bg_class = 'bg-default';
                                    if (strpos($m['tipoMovimiento'], 'VENTA') !== false)
                                        $bg_class = 'bg-venta';
                                    elseif (strpos($m['tipoMovimiento'], 'COMPRA') !== false)
                                        $bg_class = 'bg-compra';
                                    elseif (strpos($m['tipoMovimiento'], 'AJUSTE') !== false)
                                        $bg_class = 'bg-ajuste';
                                    ?>
                                    <tr>
                                        <td style="white-space: nowrap;">
                                            <?php echo date('d/m/Y H:i', strtotime($m['fecha'])); ?>
                                        </td>
                                        <td>
                                            <span class="type-pill <?php echo $bg_class; ?>">
                                                <?php echo $m['tipoMovimiento']; ?>
                                            </span>
                                        </td>
                                        <td style="color: #64748b; font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($m['detalle']); ?>
                                        </td>
                                        <td style="text-align: right; color: #10b981; font-weight: 600;">
                                            <?php echo $m['ingreso'] > 0 ? '+' . number_format($m['ingreso'], 2) : '-'; ?>
                                        </td>
                                        <td style="text-align: right; color: #ef4444; font-weight: 600;">
                                            <?php echo $m['egreso'] > 0 ? '-' . number_format($m['egreso'], 2) : '-'; ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #1e293b;">
                                            <?php echo number_format($m['saldo'], 2); ?>
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