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

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = " WHERE p.anulado = 0 AND p.stock <= p.stockMinimo ";
$params = [];

if ($search) {
    $where .= " AND (p.nombre LIKE :search OR p.codigoPrincipal LIKE :search) ";
    $params[':search'] = "%$search%";
}

// Stats (Totals for all matching records)
try {
    $stats_query = "SELECT COUNT(*) as total_count, SUM(CASE WHEN p.stock <= 0 THEN 1 ELSE 0 END) as agotados
                    FROM productos p 
                    $where";
    $stmtStats = $pdo->prepare($stats_query);
    foreach ($params as $key => $val) {
        $stmtStats->bindValue($key, $val);
    }
    $stmtStats->execute();
    $stats_row = $stmtStats->fetch(PDO::FETCH_ASSOC);

    $total_records = (int) ($stats_row['total_count'] ?? 0);
    $agotados = (int) ($stats_row['agotados'] ?? 0);

    // Main query with pagination
    $query = "SELECT p.* FROM productos p $where ORDER BY p.stock ASC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $total_records = 0;
    $agotados = 0;
    $productos = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Stock Bajo | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .stock-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .stock-header h1 {
            font-size: 1.4rem;
            color: #1e293b;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stock-header h1 i {
            color: #dc2626;
        }

        .summary-mini-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .s-mini-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border-left: 5px solid #e2e8f0;
        }

        .border-red {
            border-left-color: #ef4444;
        }

        .border-orange {
            border-left-color: #f97316;
        }

        .border-blue {
            border-left-color: #3b82f6;
        }

        .s-mini-card .val {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            display: block;
        }

        .s-mini-card .lbl {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 600;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1e293b;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #334155;
        }

        .stock-warning {
            color: #dc2626;
            font-weight: 700;
        }

        .badge-critical {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .summary-mini-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stock-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .stock-header div {
                width: 100%;
            }

            .stock-header .btn {
                width: 100%;
            }

            .pos-panel form {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 576px) {
            .summary-mini-grid {
                grid-template-columns: 1fr;
            }

            .table-container {
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
                <div class="stock-header">
                    <h1><i class="fas fa-arrow-down"></i> Productos con Stock Bajo</h1>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline" style="color: #198754; border-color: #198754;"><i
                                class="fas fa-file-excel"></i> Orden de Compra</button>
                    </div>
                </div>

                <div class="summary-mini-grid">
                    <div class="s-mini-card border-red">
                        <span class="val"><?php echo $agotados; ?></span>
                        <span class="lbl">Agotados (Stock 0 o menos)</span>
                    </div>
                    <div class="s-mini-card border-orange">
                        <span class="val"><?php echo $total_records - $agotados; ?></span>
                        <span class="lbl">Por Agotarse (Bajo Mínimo)</span>
                    </div>
                    <div class="s-mini-card border-blue">
                        <span class="val"><?php echo $total_records; ?></span>
                        <span class="lbl">Total Artículos en Alerta</span>
                    </div>
                </div>

                <div class="pos-panel" style="margin-bottom: 25px; padding: 20px;">
                    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label
                                style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Buscar
                                Producto</label>
                            <input type="text" name="search" class="form-control" placeholder="Nombre o código..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="reporte_stock_bajo.php" class="btn btn-outline"><i class="fas fa-times"></i></a>
                    </form>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th style="text-align: right;">Stock Actual</th>
                                <th style="text-align: right;">Stock Mínimo</th>
                                <th style="text-align: right;">Faltante</th>
                                <th style="text-align: center;">Estado</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 50px; color: #64748b;">
                                        <i class="fas fa-check-circle"
                                            style="font-size: 2.5rem; color: #10b981; margin-bottom: 15px; display: block;"></i>
                                        ¡Excelente! Todos los productos tienen stock suficiente.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($productos as $p):
                                    $faltante = max(0, $p['stockMinimo'] - $p['stock']);
                                    ?>
                                    <tr>
                                        <td style="font-weight: 700; color: #3b82f6;">
                                            <?php echo $p['codigoPrincipal']; ?>
                                        </td>
                                        <td style="font-weight: 600;">
                                            <?php echo $p['nombre']; ?>
                                        </td>
                                        <td style="text-align: right;" class="stock-warning">
                                            <?php echo number_format($p['stock'], 2); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php echo number_format($p['stockMinimo'], 2); ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #991b1b;">
                                            <?php echo number_format($faltante, 2); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($p['stock'] <= 0): ?>
                                                <span class="badge-critical">AGOTADO</span>
                                            <?php else: ?>
                                                <span class="badge"
                                                    style="background: #fff7ed; color: #9a3412; font-size: 0.7rem; font-weight: 700;">BAJO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="nueva_orden.php?producto_id=<?php echo $p['id']; ?>" class="btn btn-text"
                                                style="color: #3b82f6;" title="Pedir a proveedor">
                                                <i class="fas fa-shopping-cart"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
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