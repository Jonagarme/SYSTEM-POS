<?php
/**
 * Inventory Adjustments List - Ajustes de Inventario
 */
session_start();
require_once '../../includes/db.php';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$motivo = isset($_GET['motivo']) ? $_GET['motivo'] : '';
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';

$where = " WHERE 1=1 ";
$params = [];

if ($tipo) {
    $where .= " AND a.tipo_ajuste = :tipo ";
    $params[':tipo'] = $tipo;
}
if ($motivo) {
    $where .= " AND a.motivo = :motivo ";
    $params[':motivo'] = $motivo;
}

try {
    // Stats
    $stats_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN tipo_ajuste = 'INGRESO' THEN 1 ELSE 0 END) as entradas,
                        SUM(CASE WHEN tipo_ajuste = 'EGRESO' THEN 1 ELSE 0 END) as salidas
                    FROM inventario_ajusteinventario";
    $stats = $pdo->query($stats_query)->fetch();

    // Main query (Detail-level view as requested by mock UI)
    $query = "SELECT d.*, a.fecha, a.tipo_ajuste, a.motivo, a.numero_ajuste,
                     p.nombre as producto_nombre, p.codigoPrincipal as barcode,
                     u.nombre as usuario_nombre
              FROM inventario_detalleajuste d
              JOIN inventario_ajusteinventario a ON d.ajuste_id = a.id
              JOIN productos p ON d.producto_id = p.id
              LEFT JOIN usuarios u ON a.usuario_id = u.id
              $where
              ORDER BY a.fecha DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $ajustes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total for pagination
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM inventario_detalleajuste d JOIN inventario_ajusteinventario a ON d.ajuste_id = a.id $where");
    foreach ($params as $key => $val) {
        $total_stmt->bindValue($key, $val);
    }
    $total_stmt->execute();
    $total_records = $total_stmt->fetchColumn();

} catch (PDOException $e) {
    $ajustes = [];
    $stats = ['total' => 0, 'entradas' => 0, 'salidas' => 0];
    $total_records = 0;
    $error = $e->getMessage();
}

$current_page = 'inventario_ajustes';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustes de Inventario | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .ajustes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .ajustes-title h1 {
            font-size: 1.25rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .summary-cards-ajustes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .aj-card {
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
        }

        .aj-card .info h3 {
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .aj-card .info .value {
            font-size: 1.6rem;
            font-weight: 800;
        }

        .aj-card .icon {
            font-size: 2rem;
            opacity: 0.3;
        }

        .aj-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .aj-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .aj-red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .aj-orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .filters-panel {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            align-items: flex-end;
        }

        .filters-panel label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .badge-aj {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .badge-entrance {
            background: #dcfce7;
            color: #166534;
        }

        .badge-exit {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-correction {
            background: #e0f2fe;
            color: #0369a1;
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
        }

        .dif-pos {
            color: #10b981;
            font-weight: 700;
        }

        .dif-neg {
            color: #ef4444;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .ajustes-header {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-panel {
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
                <div class="ajustes-header">
                    <div class="ajustes-title">
                        <h1><i class="fas fa-sliders-h"></i> Ajustes de Inventario</h1>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline">
                            <i class="fas fa-file-export"></i> Exportar
                        </button>
                        <button class="btn btn-primary" onclick="location.href='nuevo_ajuste.php'">
                            <i class="fas fa-plus"></i> Nuevo Ajuste
                        </button>
                    </div>
                </div>

                <div class="summary-cards-ajustes">
                    <div class="aj-card aj-blue">
                        <div class="info">
                            <h3>Total Ajustes</h3>
                            <div class="value"><?php echo number_format($stats['total'] ?? 0); ?></div>
                        </div>
                        <i class="fas fa-history icon"></i>
                    </div>
                    <div class="aj-card aj-green">
                        <div class="info">
                            <h3>Entradas</h3>
                            <div class="value"><?php echo number_format($stats['entradas'] ?? 0); ?></div>
                        </div>
                        <i class="fas fa-arrow-up icon"></i>
                    </div>
                    <div class="aj-card aj-red">
                        <div class="info">
                            <h3>Salidas</h3>
                            <div class="value"><?php echo number_format($stats['salidas'] ?? 0); ?></div>
                        </div>
                        <i class="fas fa-arrow-down icon"></i>
                    </div>
                    <div class="aj-card aj-orange">
                        <div class="info">
                            <h3>Correcciones</h3>
                            <div class="value">0</div>
                        </div>
                        <i class="fas fa-sync icon"></i>
                    </div>
                </div>

                <div class="filters-panel">
                    <form method="GET" style="display: contents;">
                        <div>
                            <label>Tipo de Ajuste</label>
                            <select name="tipo" class="form-control">
                                <option value="">Todos</option>
                                <option value="INGRESO" <?php echo $tipo == 'INGRESO' ? 'selected' : ''; ?>>Entrada
                                </option>
                                <option value="EGRESO" <?php echo $tipo == 'EGRESO' ? 'selected' : ''; ?>>Salida</option>
                            </select>
                        </div>
                        <div>
                            <label>Motivo</label>
                            <select name="motivo" class="form-control">
                                <option value="">Todos</option>
                                <option value="inventario_fisico">Inventario Físico</option>
                                <option value="correccion">Corrección</option>
                                <option value="caducidad">Caducidad</option>
                                <option value="daño">Daño / Avería</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: 42px;">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="ajustes.php" class="btn btn-outline"
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
                                <th>Cant. Ant.</th>
                                <th>Cant. Nueva</th>
                                <th>Diferencia</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ajustes)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                                        No se encontraron ajustes de inventario.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ajustes as $a):
                                    $dif = $a['cantidad_nueva'] - $a['cantidad_anterior'];
                                    $dif_style = $dif > 0 ? 'dif-pos' : ($dif < 0 ? 'dif-neg' : '');
                                    $dif_text = ($dif > 0 ? '+' : '') . $dif;

                                    $badge_class = 'badge-correction';
                                    if ($a['tipo_ajuste'] == 'INGRESO')
                                        $badge_class = 'badge-entrance';
                                    if ($a['tipo_ajuste'] == 'EGRESO')
                                        $badge_class = 'badge-exit';
                                    ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($a['fecha'])); ?></td>
                                        <td>
                                            <div style="font-weight: 600; color: #1e293b;">
                                                <?php echo htmlspecialchars($a['producto_nombre']); ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #64748b;">
                                                <?php echo htmlspecialchars($a['barcode']); ?>
                                            </div>
                                        </td>
                                        <td><span
                                                class="badge-aj <?php echo $badge_class; ?>"><?php echo $a['tipo_ajuste']; ?></span>
                                        </td>
                                        <td><?php echo number_format($a['cantidad_anterior'], 2); ?></td>
                                        <td><?php echo number_format($a['cantidad_nueva'], 2); ?></td>
                                        <td class="<?php echo $dif_style; ?>"><?php echo $dif_text; ?></td>
                                        <td><?php echo htmlspecialchars($a['usuario_nombre'] ?? 'Sistema'); ?></td>
                                        <td>
                                            <button class="btn-text" title="Ver Detalle"><i class="fas fa-eye"></i></button>
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
                            <a href="?page=<?php echo $i; ?>&tipo=<?php echo $tipo; ?>&motivo=<?php echo $motivo; ?>"
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