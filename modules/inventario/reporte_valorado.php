<?php
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario';

// Pagination settings
$limit = 20;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Fetch products with stock > 0 or all products? Usually valued repo is based on current stock.
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = " WHERE p.anulado = 0 AND p.stock > 0 ";
$params = [];

if ($search) {
    $where .= " AND (p.nombre LIKE :search OR p.codigoPrincipal LIKE :search) ";
    $params[':search'] = "%$search%";
}

// Grand totals for all products matching filters
try {
    $totals_query = "SELECT 
        COUNT(*) as total_count,
        SUM(p.stock * p.costoUnidad) as total_costo,
        SUM(p.stock * p.precioVenta) as total_venta
        FROM productos p 
        $where";
    $stmtTotals = $pdo->prepare($totals_query);
    foreach ($params as $key => $val) {
        $stmtTotals->bindValue($key, $val);
    }
    $stmtTotals->execute();
    $totals_row = $stmtTotals->fetch(PDO::FETCH_ASSOC);

    $total_records = (int) ($totals_row['total_count'] ?? 0);
    $total_costo = (float) ($totals_row['total_costo'] ?? 0);
    $total_venta = (float) ($totals_row['total_venta'] ?? 0);
    $total_utilidad = $total_venta - $total_costo;

    // Fetch products with pagination
    $query = "SELECT p.* FROM productos p $where ORDER BY p.nombre ASC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $productos_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $total_records = 0;
    $total_costo = 0;
    $total_venta = 0;
    $total_utilidad = 0;
    $productos_raw = [];
}

$productos = [];
foreach ($productos_raw as $p) {
    $costo = (float) $p['costoUnidad'];
    $precio = (float) $p['precioVenta'];
    $stock = (float) $p['stock'];

    $valor_costo = $stock * $costo;
    $valor_venta = $stock * $precio;
    $margen = $precio > 0 ? (($precio - $costo) / $precio) * 100 : 0;

    $productos[] = [
        'codigo' => $p['codigoPrincipal'],
        'nombre' => $p['nombre'],
        'stock' => number_format($stock, 2),
        'costo' => number_format($costo, 4),
        'precio' => number_format($precio, 4),
        'valor_costo' => number_format($valor_costo, 2),
        'valor_venta' => number_format($valor_venta, 2),
        'margen' => number_format($margen, 1) . '%'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Valorado | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .val-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .val-header h1 {
            font-size: 1.4rem;
            color: #1e293b;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .val-header h1 i {
            color: #059669;
        }

        .valuation-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .v-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .v-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .v-cost::after {
            background: #6366f1;
        }

        .v-sale::after {
            background: #10b981;
        }

        .v-profit::after {
            background: #f59e0b;
        }

        .v-card .info h3 {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 10px;
        }

        .v-card .info .value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e293b;
        }

        .v-card .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .v-cost .icon-box {
            background: #eef2ff;
            color: #6366f1;
        }

        .v-sale .icon-box {
            background: #ecfdf5;
            color: #10b981;
        }

        .v-profit .icon-box {
            background: #fffbeb;
            color: #f59e0b;
        }

        .val-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .val-table {
            width: 100%;
            border-collapse: collapse;
        }

        .val-table th {
            background: #212529;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .val-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #334155;
        }

        .num-col {
            text-align: right;
            font-family: 'Inter', sans-serif;
        }

        .filters-report {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .valuation-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .val-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .val-header div {
                width: 100%;
                display: flex;
            }

            .val-header .btn {
                flex: 1;
            }

            .filters-report {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 576px) {
            .valuation-grid {
                grid-template-columns: 1fr;
            }

            .val-table-container {
                overflow-x: auto;
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
                <div class="val-header">
                    <h1><i class="fas fa-hand-holding-usd"></i> Inventario Valorado</h1>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline" style="color: #6366f1; border-color: #6366f1;"><i
                                class="fas fa-file-pdf"></i> PDF</button>
                        <button class="btn btn-outline" style="color: #198754; border-color: #198754;"><i
                                class="fas fa-file-excel"></i> Excel</button>
                    </div>
                </div>

                <div class="valuation-grid">
                    <div class="v-card v-cost">
                        <div class="info">
                            <h3>Valor Total (Costo)</h3>
                            <div class="value">$ <?php echo number_format($total_costo, 2); ?></div>
                        </div>
                        <div class="icon-box"><i class="fas fa-receipt"></i></div>
                    </div>
                    <div class="v-card v-sale">
                        <div class="info">
                            <h3>Valor Total (Venta)</h3>
                            <div class="value">$ <?php echo number_format($total_venta, 2); ?></div>
                        </div>
                        <div class="icon-box"><i class="fas fa-tags"></i></div>
                    </div>
                    <div class="v-card v-profit">
                        <div class="info">
                            <h3>Utilidad Estimada</h3>
                            <div class="value">$ <?php echo number_format($total_utilidad, 2); ?></div>
                        </div>
                        <div class="icon-box"><i class="fas fa-chart-line"></i></div>
                    </div>
                </div>

                <form method="GET" class="filters-report">
                    <div style="flex: 2;">
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Buscar
                            Producto</label>
                        <input type="text" name="search" class="form-control" placeholder="Nombre o código..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div style="flex: 1;">
                        <label
                            style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Ubicación
                            (Demo)</label>
                        <select class="form-control">
                            <option>Todas las ubicaciones</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 30px;"><i
                            class="fas fa-search"></i>
                        Calcular</button>
                    <a href="reporte_valorado.php" class="btn btn-outline"
                        style="height: 42px; padding: 0 15px; display: flex; align-items: center;"><i
                            class="fas fa-times"></i></a>
                </form>

                <div class="val-table-container">
                    <table class="val-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="text-align: right;">Existencia</th>
                                <th style="text-align: right;">Costo Prom.</th>
                                <th style="text-align: right;">Precio Vta.</th>
                                <th style="text-align: right;">Total Costo</th>
                                <th style="text-align: right;">Total Venta</th>
                                <th style="text-align: right;">Margen %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #1e293b;">
                                            <?php echo $p['nombre']; ?>
                                        </div>
                                        <div style="font-size: 0.7rem; color: #64748b;">
                                            <?php echo $p['codigo']; ?>
                                        </div>
                                    </td>
                                    <td class="num-col" style="font-weight: 800;">
                                        <?php echo $p['stock']; ?>
                                    </td>
                                    <td class="num-col">$
                                        <?php echo $p['costo']; ?>
                                    </td>
                                    <td class="num-col">$
                                        <?php echo $p['precio']; ?>
                                    </td>
                                    <td class="num-col" style="color: #6366f1; font-weight: 700;">$
                                        <?php echo $p['valor_costo']; ?>
                                    </td>
                                    <td class="num-col" style="color: #10b981; font-weight: 700;">$
                                        <?php echo $p['valor_venta']; ?>
                                    </td>
                                    <td class="num-col">
                                        <span
                                            style="background: #f0fdf4; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                                            <?php echo $p['margen']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_records > $limit): ?>
                    <div
                        style="margin-top: 25px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; align-items: center;">
                        <?php
                        $total_pages = ceil($total_records / $limit);
                        $query_params = $_GET;
                        $range = 2;

                        if ($page > 1):
                            $query_params['page'] = 1; ?>
                            <a href="?<?php echo http_build_query($query_params); ?>" class="btn btn-outline"
                                style="padding: 5px 12px; height: auto; text-decoration: none;" title="Primera"><i
                                    class="fas fa-angle-double-left"></i></a>
                        <?php endif;

                        for ($i = 1; $i <= $total_pages; $i++):
                            if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)):
                                $query_params['page'] = $i;
                                ?>
                                <a href="?<?php echo http_build_query($query_params); ?>"
                                    class="btn <?php echo $page == $i ? 'btn-primary' : 'btn-outline'; ?>"
                                    style="padding: 5px 12px; height: auto; min-width: 35px; text-align: center; text-decoration: none;">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif ($i == $page - $range - 1 || $i == $page + $range + 1): ?>
                                <span style="color: #64748b; padding: 0 5px;">...</span>
                            <?php endif;
                        endfor;

                        if ($page < $total_pages):
                            $query_params['page'] = $total_pages; ?>
                            <a href="?<?php echo http_build_query($query_params); ?>" class="btn btn-outline"
                                style="padding: 5px 12px; height: auto; text-decoration: none;" title="Última"><i
                                    class="fas fa-angle-double-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>