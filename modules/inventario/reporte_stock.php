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

$ubicacion_id = isset($_GET['ubicacion_id']) ? $_GET['ubicacion_id'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where = " WHERE p.anulado = 0 ";
$params = [];

if ($search) {
    $where .= " AND (p.nombre LIKE :search OR p.codigoPrincipal LIKE :search) ";
    $params[':search'] = "%$search%";
}
if ($ubicacion_id) {
    $where .= " AND su.ubicacion_id = :ubicacion_id ";
    $params[':ubicacion_id'] = $ubicacion_id;
}

// Stats (Totals for all matching records)
try {
    $stats_query = "SELECT COUNT(*) as total_items, SUM(su.cantidad) as total_stock
                    FROM inventario_stockubicacion su
                    JOIN productos p ON su.producto_id = p.id
                    $where";
    $stmtStats = $pdo->prepare($stats_query);
    foreach ($params as $key => $val) {
        $stmtStats->bindValue($key, $val);
    }
    $stmtStats->execute();
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    $total_records = (int) ($stats['total_items'] ?? 0);
    $total_stock_all = (float) ($stats['total_stock'] ?? 0);

    // Main query with pagination
    $query = "SELECT su.cantidad, su.ultima_actualizacion, p.nombre as producto_nombre, p.codigoPrincipal as barcode, u.nombre as ubicacion_nombre, u.codigo as ubicacion_codigo
              FROM inventario_stockubicacion su
              JOIN productos p ON su.producto_id = p.id
              JOIN inventario_ubicacion u ON su.ubicacion_id = u.id
              $where
              ORDER BY u.nombre, p.nombre ASC
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $total_records = 0;
    $total_stock_all = 0;
    $items = [];
}

// Fetch locations for filter
$ubicaciones = $pdo->query("SELECT * FROM inventario_ubicacion ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock por Ubicación | Warehouse POS</title>
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
            color: #10b981;
        }

        .summary-mini-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .s-mini-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .s-mini-card .val {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1e293b;
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

        .badge-loc {
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Responsive */
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

            .summary-mini-grid {
                grid-template-columns: 1fr;
            }

            .pos-panel form {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 576px) {
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
                    <h1><i class="fas fa-warehouse"></i> Existencias por Ubicación</h1>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline" style="color: #6366f1; border-color: #6366f1;"><i
                                class="fas fa-print"></i> PDF</button>
                    </div>
                </div>

                <div class="summary-mini-grid">
                    <div class="s-mini-card">
                        <div>
                            <span class="lbl">Total de Ítems Listados</span>
                            <div class="val"><?php echo $total_records; ?></div>
                        </div>
                        <i class="fas fa-list-ul" style="font-size: 2rem; color: #cbd5e1;"></i>
                    </div>
                    <div class="s-mini-card">
                        <div>
                            <span class="lbl">Suma Total de Stock</span>
                            <div class="val"><?php echo number_format($total_stock_all, 2); ?></div>
                        </div>
                        <i class="fas fa-boxes" style="font-size: 2rem; color: #10b981;"></i>
                    </div>
                </div>

                <div class="pos-panel" style="margin-bottom: 25px; padding: 20px;">
                    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
                        <div style="flex: 2;">
                            <label
                                style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Buscar
                                Producto</label>
                            <input type="text" name="search" class="form-control" placeholder="Nombre o código..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div style="flex: 1;">
                            <label
                                style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Ubicación</label>
                            <select name="ubicacion_id" class="form-control">
                                <option value="">Todas las ubicaciones</option>
                                <?php foreach ($ubicaciones as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo $ubicacion_id == $u['id'] ? 'selected' : ''; ?>>
                                        <?php echo $u['nombre']; ?> (
                                        <?php echo $u['codigo']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="reporte_stock.php" class="btn btn-outline"><i class="fas fa-times"></i></a>
                    </form>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Ubicación</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th style="text-align: right;">Existencia</th>
                                <th>Última Actualización</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 50px; color: #64748b;">
                                        No hay registros de stock para los filtros seleccionados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><span class="badge-loc">
                                                <?php echo $item['ubicacion_nombre']; ?>
                                            </span></td>
                                        <td style="font-family: monospace;">
                                            <?php echo $item['barcode']; ?>
                                        </td>
                                        <td style="font-weight: 600;">
                                            <?php echo $item['producto_nombre']; ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #1e293b;">
                                            <?php echo number_format($item['cantidad'], 2); ?>
                                        </td>
                                        <td style="color: #64748b; font-size: 0.8rem;">
                                            <?php echo date('d/m/Y H:i', strtotime($item['ultima_actualizacion'])); ?>
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