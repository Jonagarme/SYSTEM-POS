<?php
/**
 * General Kardex - Kardex General
 */
session_start();
require_once '../../includes/db.php';

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where = " WHERE 1=1 ";
$params = [];

if ($tipo) {
    $where .= " AND km.tipoMovimiento = :tipo ";
    $params[':tipo'] = $tipo;
}
if ($search) {
    $where .= " AND (p.nombre LIKE :search OR p.codigoPrincipal LIKE :search OR km.detalle LIKE :search) ";
    $params[':search'] = "%$search%";
}

try {
    // Stats
    $stats_query = "SELECT 
                        COUNT(*) as total,
                        SUM(ingreso) as total_entradas,
                        SUM(egreso) as total_salidas
                    FROM kardex_movimientos";
    $stats = $pdo->query($stats_query)->fetch();

    $productos_activos_query = "SELECT COUNT(*) FROM productos WHERE anulado = 0";
    $productos_activos = $pdo->query($productos_activos_query)->fetchColumn();

    // Main query
    $query = "SELECT km.*, p.nombre as producto_nombre, p.codigoPrincipal as barcode
              FROM kardex_movimientos km
              JOIN productos p ON km.idProducto = p.id
              $where
              ORDER BY km.fecha DESC, km.id DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total for pagination
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM kardex_movimientos km JOIN productos p ON km.idProducto = p.id $where");
    foreach ($params as $key => $val) {
        $total_stmt->bindValue($key, $val);
    }
    $total_stmt->execute();
    $total_records = $total_stmt->fetchColumn();

} catch (PDOException $e) {
    $movimientos = [];
    $stats = ['total' => 0, 'total_entradas' => 0, 'total_salidas' => 0];
    $total_records = 0;
    $productos_activos = 0;
    $error = $e->getMessage();
}

$current_page = 'inventario_kardex';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex General | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .kardex-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
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

        .summary-cards-kardex {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .k-card .info .value {
            font-size: 1.6rem;
            font-weight: 800;
        }

        .k-card .icon {
            font-size: 2.2rem;
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

        .k-orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .filters-panel-kardex {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: flex-end;
        }

        .filters-panel-kardex label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .table-responsive {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 12px 15px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 1px solid #f1f5f9;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            vertical-align: top;
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

        @media (max-width: 768px) {
            .kardex-header {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-panel-kardex {
                grid-template-columns: 1fr;
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
                <div class="kardex-header">
                    <div class="kardex-title">
                        <h1><i class="fas fa-book"></i> Kardex General de Inventario</h1>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline">
                            <i class="fas fa-print"></i> Reporte PDF
                        </button>
                        <button class="btn btn-outline">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>

                <div class="summary-cards-kardex">
                    <div class="k-card k-blue">
                        <div class="info">
                            <h3>Total Movimientos</h3>
                            <div class="value"><?php echo number_format($stats['total'] ?? 0); ?></div>
                        </div>
                        <i class="fas fa-exchange-alt icon"></i>
                    </div>
                    <div class="k-card k-green">
                        <div class="info">
                            <h3>Total Entradas</h3>
                            <div class="value"><?php echo number_format($stats['total_entradas'] ?? 0, 2); ?></div>
                        </div>
                        <i class="fas fa-arrow-alt-circle-up icon"></i>
                    </div>
                    <div class="k-card k-red">
                        <div class="info">
                            <h3>Total Salidas</h3>
                            <div class="value"><?php echo number_format($stats['total_salidas'] ?? 0, 2); ?></div>
                        </div>
                        <i class="fas fa-arrow-alt-circle-down icon"></i>
                    </div>
                    <div class="k-card k-orange">
                        <div class="info">
                            <h3>Productos Activos</h3>
                            <div class="value"><?php echo number_format($productos_activos ?? 0); ?></div>
                        </div>
                        <i class="fas fa-boxes icon"></i>
                    </div>
                </div>

                <div class="filters-panel-kardex">
                    <form method="GET" style="display: contents;">
                        <div style="flex: 2;">
                            <label>Buscar Producto o Documento</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Nombre, código, N° factura..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div>
                            <label>Tipo Movimiento</label>
                            <select name="tipo" class="form-control">
                                <option value="">Todos</option>
                                <option value="VENTA" <?php echo $tipo == 'VENTA' ? 'selected' : ''; ?>>Venta</option>
                                <option value="COMPRA" <?php echo $tipo == 'COMPRA' ? 'selected' : ''; ?>>Compra</option>
                                <option value="AJUSTE INGRESO" <?php echo $tipo == 'AJUSTE INGRESO' ? 'selected' : ''; ?>>
                                    Ajuste Ingreso</option>
                                <option value="AJUSTE EGRESO" <?php echo $tipo == 'AJUSTE EGRESO' ? 'selected' : ''; ?>>
                                    Ajuste Egreso</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: 42px;">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="kardex.php" class="btn btn-outline"
                            style="height: 42px; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i class="fas fa-times"></i>
                        </a>
                    </form>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha / Hora</th>
                                <th>Producto</th>
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
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                                        No se encontraron movimientos en el kardex.
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
                                            <?php echo date('d/m/Y H:i', strtotime($m['fecha'])); ?></td>
                                        <td>
                                            <div style="font-weight: 600; color: #1e293b;">
                                                <?php echo htmlspecialchars($m['producto_nombre']); ?></div>
                                            <div style="font-size: 0.75rem; color: #64748b;">
                                                <?php echo htmlspecialchars($m['barcode']); ?></div>
                                        </td>
                                        <td><span
                                                class="type-pill <?php echo $bg_class; ?>"><?php echo $m['tipoMovimiento']; ?></span>
                                        </td>
                                        <td style="max-width: 250px; font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($m['detalle']); ?></td>
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

                <?php if ($total_records > $limit): ?>
                    <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                        <?php for ($i = 1; $i <= ceil($total_records / $limit); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&tipo=<?php echo $tipo; ?>&search=<?php echo urlencode($search); ?>"
                                class="btn <?php echo $page == $i ? 'btn-primary' : 'btn-outline'; ?>"
                                style="padding: 5px 12px; height: auto;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>