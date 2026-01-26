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

// Search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';

$where = " WHERE l.activo = 1 ";
$params = [];

if ($search) {
    $where .= " AND (p.nombre LIKE :search OR p.codigoPrincipal LIKE :search OR l.numero_lote LIKE :search) ";
    $params[':search'] = "%$search%";
}

if ($estado == 'Vencidos') {
    $where .= " AND l.fecha_caducidad < CURDATE() ";
} elseif ($estado == 'Próximos a vencer') {
    $where .= " AND l.fecha_caducidad BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ";
} elseif ($estado == 'Vigentes') {
    $where .= " AND l.fecha_caducidad > DATE_ADD(CURDATE(), INTERVAL 30 DAY) ";
}

if ($fecha_desde) {
    $where .= " AND l.fecha_caducidad >= :fecha_desde ";
    $params[':fecha_desde'] = $fecha_desde;
}

// Stats and total count
try {
    $stats_query = "SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN l.fecha_caducidad < CURDATE() THEN 1 ELSE 0 END) as vencidos,
        SUM(CASE WHEN l.fecha_caducidad BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as proximos,
        SUM(CASE WHEN l.fecha_caducidad > DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as vigentes
        FROM inventario_loteproducto l
        JOIN productos p ON l.producto_id = p.id
        $where";
    $stmtStats = $pdo->prepare($stats_query);
    foreach ($params as $key => $val) {
        $stmtStats->bindValue($key, $val);
    }
    $stmtStats->execute();
    $stats_row = $stmtStats->fetch(PDO::FETCH_ASSOC);

    $total_records = (int) ($stats_row['total_count'] ?? 0);
    $stats = [
        'vencidos' => $stats_row['vencidos'] ?? 0,
        'proximos' => $stats_row['proximos'] ?? 0,
        'vigentes' => $stats_row['vigentes'] ?? 0,
        'total' => $total_records
    ];

    // Fetch products with lots and pagination
    $query = "SELECT l.*, p.nombre as nombre, p.codigoPrincipal as codigo
              FROM inventario_loteproducto l
              JOIN productos p ON l.producto_id = p.id
              $where
              ORDER BY l.fecha_caducidad ASC
              LIMIT :limit OFFSET :offset";

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
    $productos_raw = [];
    $stats = ['vencidos' => 0, 'proximos' => 0, 'vigentes' => 0, 'total' => 0];
}

$productos = [];
foreach ($productos_raw as $p) {
    $vencimiento = new DateTime($p['fecha_caducidad']);
    $hoy = new DateTime();
    $diff = $hoy->diff($vencimiento);
    $days = (int) $diff->format("%r%a");

    if ($days < 0) {
        $status = 'Vencido';
        $status_class = 'st-expired';
    } elseif ($days <= 30) {
        $status = 'Próximo';
        $status_class = 'st-warning';
    } else {
        $status = 'Vigente';
        $status_class = 'st-ok';
    }

    $productos[] = array_merge($p, [
        'status' => $status,
        'status_class' => $status_class,
        'stock' => $p['cantidad_disponible']
    ]);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Caducados | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .cad-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .cad-header h1 {
            font-size: 1.4rem;
            color: #1e293b;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cad-header h1 i {
            color: #f97316;
        }

        .summary-mini-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .s-mini-card {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid #e2e8f0;
        }

        .s-mini-card .val {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            display: block;
        }

        .s-mini-card .lbl {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
        }

        .border-red {
            border-left-color: #ef4444;
        }

        .border-orange {
            border-left-color: #f97316;
        }

        .border-green {
            border-left-color: #10b981;
        }

        .border-blue {
            border-left-color: #3b82f6;
        }

        .filters-report {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 150px;
            gap: 15px;
            align-items: flex-end;
        }

        .filters-report label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .cad-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .cad-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cad-table th {
            background: #1e293b;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .cad-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #334155;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .st-expired {
            background: #fee2e2;
            color: #dc2626;
        }

        .st-warning {
            background: #fff7ed;
            color: #f97316;
        }

        .st-ok {
            background: #f0fdf4;
            color: #166534;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .summary-mini-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-report {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .cad-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .cad-header div {
                width: 100%;
                display: flex;
            }

            .cad-header .btn {
                flex: 1;
            }
        }

        @media (max-width: 576px) {
            .summary-mini-grid {
                grid-template-columns: 1fr;
            }

            .filters-report {
                grid-template-columns: 1fr;
            }

            .cad-table-container {
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
                <div class="cad-header">
                    <h1><i class="fas fa-exclamation-triangle"></i> Control de Caducidades</h1>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline" style="color: #198754; border-color: #198754;">
                            <i class="fas fa-file-excel"></i> Exportar
                        </button>
                        <button class="btn btn-outline" style="color: #dc3545; border-color: #dc3545;">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>

                <div class="summary-mini-grid">
                    <div class="s-mini-card border-red">
                        <span class="val"><?php echo $stats['vencidos']; ?></span>
                        <span class="lbl">Productos Vencidos</span>
                    </div>
                    <div class="s-mini-card border-orange">
                        <span class="val"><?php echo $stats['proximos']; ?></span>
                        <span class="lbl">Vencimiento Próximo (30 días)</span>
                    </div>
                    <div class="s-mini-card border-green">
                        <span class="val"><?php echo $stats['vigentes']; ?></span>
                        <span class="lbl">Productos Vigentes</span>
                    </div>
                    <div class="s-mini-card border-blue">
                        <span class="val"><?php echo $stats['total']; ?></span>
                        <span class="lbl">Total Lotes Revisados</span>
                    </div>
                </div>

                <form method="GET" class="filters-report">
                    <div>
                        <label>Buscar Producto / Lote</label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Nombre, código o número de lote..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div>
                        <label>Rango de Vencimiento (Desde)</label>
                        <input type="date" name="fecha_desde" class="form-control" value="<?php echo $fecha_desde; ?>">
                    </div>
                    <div>
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">Todos los estados</option>
                            <option <?php echo $estado == 'Vencidos' ? 'selected' : ''; ?>>Vencidos</option>
                            <option <?php echo $estado == 'Próximos a vencer' ? 'selected' : ''; ?>>Próximos a vencer
                            </option>
                            <option <?php echo $estado == 'Vigentes' ? 'selected' : ''; ?>>Vigentes</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                </form>

                <div class="cad-table-container">
                    <table class="cad-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción del Producto</th>
                                <th>Lote</th>
                                <th>Fecha Vencimiento</th>
                                <th style="text-align: right;">Stock Lote</th>
                                <th style="text-align: center;">Estado</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td style="font-weight: 700; color: #2563eb;">
                                        <?php echo $p['codigo']; ?>
                                    </td>
                                    <td style="font-weight: 600;">
                                        <?php echo $p['nombre']; ?>
                                    </td>
                                    <td style="font-family: monospace;">
                                        <?php echo $p['numero_lote']; ?>
                                    </td>
                                    <td style="font-weight: 700; color: #1e293b;">
                                        <?php echo date('d/m/Y', strtotime($p['fecha_caducidad'])); ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 700;">
                                        <?php echo $p['stock']; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge-status <?php echo $p['status_class']; ?>">
                                            <?php echo $p['status']; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button class="btn btn-text" style="color: #6366f1;"><i
                                                class="fas fa-eye"></i></button>
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
                        $range = 2; // Páginas a mostrar alrededor de la actual
                    
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