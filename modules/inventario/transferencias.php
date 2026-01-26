<?php
/**
 * Stock Transfers List - Transferencias de Stock
 */
session_start();
require_once '../../includes/db.php';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$origen = isset($_GET['origen']) ? $_GET['origen'] : '';
$destino = isset($_GET['destino']) ? $_GET['destino'] : '';

$where = " WHERE 1=1 ";
$params = [];

if ($estado) {
    $where .= " AND t.estado = :estado ";
    $params[':estado'] = $estado;
}
if ($origen) {
    $where .= " AND t.ubicacion_origen_id = :origen ";
    $params[':origen'] = $origen;
}
if ($destino) {
    $where .= " AND t.ubicacion_destino_id = :destino ";
    $params[':destino'] = $destino;
}

// Data fetching
try {
    // Total records for pagination stats
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM inventario_transferenciastock t $where");
    foreach ($params as $key => $val) {
        $total_stmt->bindValue($key, $val);
    }
    $total_stmt->execute();
    $total_all = $total_stmt->fetchColumn();

    $query = "SELECT t.*, 
                uo.nombre as origen_nombre, 
                ud.nombre as destino_nombre,
                u.nombreCompleto as usuario_nombre
              FROM inventario_transferenciastock t
              LEFT JOIN inventario_ubicacion uo ON t.ubicacion_origen_id = uo.id
              LEFT JOIN inventario_ubicacion ud ON t.ubicacion_destino_id = ud.id
              LEFT JOIN usuarios u ON t.usuario_creacion_id = u.id
              $where
              ORDER BY t.creadoDate DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $transferencias = $stmt->fetchAll();

    // Stats for cards
    $stats_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'COMPLETADA' THEN 1 ELSE 0 END) as completadas
                    FROM inventario_transferenciastock";
    $stats = $pdo->query($stats_query)->fetch();

    // Locations for filters
    $ubicaciones = $pdo->query("SELECT id, nombre FROM inventario_ubicacion WHERE activo = 1")->fetchAll();

} catch (PDOException $e) {
    $transferencias = [];
    $total_all = 0;
    $stats = ['total' => 0, 'pendientes' => 0, 'completadas' => 0];
    $ubicaciones = [];
    $error = $e->getMessage();
}

$current_page = 'inventario_transferencias';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferencias de Stock | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .transfer-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .transfer-title h1 {
            font-size: 1.4rem;
            color: #1e293b;
            margin: 0 0 5px 0;
            font-weight: 700;
        }

        .breadcrumb {
            display: flex;
            gap: 5px;
            font-size: 0.75rem;
            color: #64748b;
        }

        .breadcrumb a {
            color: #2563eb;
            text-decoration: none;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn-locations {
            background: white;
            color: #0ea5e9;
            border: 1px solid #0ea5e9;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-locations:hover {
            background: #f0f9ff;
        }

        .summary-grid-transfer {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .trans-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .trans-card.primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .trans-card.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .trans-card.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .trans-card .info h3 {
            font-size: 0.8rem;
            font-weight: 500;
            margin: 0 0 5px 0;
            opacity: 0.9;
        }

        .trans-card .info .value {
            font-size: 1.8rem;
            font-weight: 800;
        }

        .trans-card .icon {
            font-size: 2.2rem;
            opacity: 0.3;
        }

        .filters-panel-transfer {
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

        .filters-panel-transfer label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
            border: 1px solid #f1f5f9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        th {
            background: #f8fafc;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-completed {
            background: #dcfce7;
            color: #166534;
        }

        .badge-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .empty-state-transfer {
            background: white;
            border-radius: 12px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .empty-state-transfer .icon-container {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .btn-create-center {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-create-center:hover {
            background: #4338ca;
        }

        .actions-cell {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-icon:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        @media (max-width: 768px) {
            .transfer-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-actions {
                flex-direction: column;
            }

            .header-actions .btn,
            .header-actions .btn-locations {
                width: 100%;
                justify-content: center;
            }

            .filters-panel-transfer {
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
                <div class="transfer-header">
                    <div class="transfer-title">
                        <h1>Transferencias de Stock</h1>
                        <div class="breadcrumb">
                            <a href="../../index.php">Dashboard</a> /
                            <span>Inventario</span> /
                            <span>Transferencias de Stock</span>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="nueva_transferencia.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Transferencia
                        </a>
                        <a href="ubicaciones.php" class="btn-locations">
                            <i class="fas fa-map-marker-alt"></i> Gestionar Ubicaciones
                        </a>
                    </div>
                </div>

                <div class="summary-grid-transfer">
                    <div class="trans-card primary">
                        <div class="info">
                            <h3>Total Transferencias</h3>
                            <div class="value"><?php echo number_format($stats['total'] ?? 0); ?></div>
                        </div>
                        <i class="fas fa-exchange-alt icon"></i>
                    </div>
                    <div class="trans-card warning">
                        <div class="info">
                            <h3>Pendientes</h3>
                            <div class="value"><?php echo number_format($stats['pendientes'] ?? 0); ?></div>
                        </div>
                        <i class="fas fa-clock icon"></i>
                    </div>
                    <div class="trans-card success">
                        <div class="info">
                            <h3>Completadas</h3>
                            <div class="value"><?php echo number_format($stats['completadas'] ?? 0); ?></div>
                        </div>
                        <i class="fas fa-check-circle icon"></i>
                    </div>
                </div>

                <div class="filters-panel-transfer">
                    <form method="GET" style="display: contents;">
                        <div>
                            <label>Estado</label>
                            <select name="estado" class="form-control">
                                <option value="">Todos los estados</option>
                                <option value="PENDIENTE" <?php echo $estado == 'PENDIENTE' ? 'selected' : ''; ?>>
                                    Pendiente</option>
                                <option value="COMPLETADA" <?php echo $estado == 'COMPLETADA' ? 'selected' : ''; ?>>
                                    Completada</option>
                                <option value="CANCELADA" <?php echo $estado == 'CANCELADA' ? 'selected' : ''; ?>>
                                    Cancelado</option>
                            </select>
                        </div>
                        <div>
                            <label>Ubicación Origen</label>
                            <select name="origen" class="form-control">
                                <option value="">Todas</option>
                                <?php foreach ($ubicaciones as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo $origen == $u['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Ubicación Destino</label>
                            <select name="destino" class="form-control">
                                <option value="">Todas</option>
                                <?php foreach ($ubicaciones as $u): ?>
                                    <option value="<?php echo $u['id']; ?>" <?php echo $destino == $u['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($u['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: 42px;">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="transferencias.php" class="btn btn-outline"
                            style="height: 42px; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </form>
                </div>

                <?php if (empty($transferencias)): ?>
                    <div class="empty-state-transfer">
                        <div class="icon-container">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h3>No hay transferencias de stock</h3>
                        <p>Comienza creando tu primera transferencia entre ubicaciones</p>
                        <a href="nueva_transferencia.php" class="btn-create-center">
                            <i class="fas fa-plus"></i> Crear Primera Transferencia
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>N° Documento</th>
                                    <th>Fecha</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transferencias as $t):
                                    $status_class = '';
                                    if ($t['estado'] == 'PENDIENTE')
                                        $status_class = 'badge-pending';
                                    elseif ($t['estado'] == 'COMPLETADA')
                                        $status_class = 'badge-completed';
                                    elseif ($t['estado'] == 'CANCELADA')
                                        $status_class = 'badge-cancelled';
                                    ?>
                                    <tr>
                                        <td style="font-weight: 600;">
                                            <?php echo htmlspecialchars($t['numero_transferencia']); ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($t['creadoDate'])); ?></td>
                                        <td><?php echo htmlspecialchars($t['origen_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($t['destino_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($t['usuario_nombre']); ?></td>
                                        <td><span class="badge <?php echo $status_class; ?>"><?php echo $t['estado']; ?></span>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="detalle_transferencia.php?id=<?php echo $t['id']; ?>" class="btn-icon"
                                                    title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($t['estado'] == 'PENDIENTE'): ?>
                                                    <a href="cancelar_transferencia.php?id=<?php echo $t['id']; ?>" class="btn-icon"
                                                        title="Cancelar"
                                                        onclick="return confirm('¿Seguro que desea cancelar esta transferencia?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Simplified Pagination -->
                    <?php if ($total_all > $limit): ?>
                        <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                            <?php for ($i = 1; $i <= ceil($total_all / $limit); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&estado=<?php echo $estado; ?>&origen=<?php echo $origen; ?>&destino=<?php echo $destino; ?>"
                                    class="btn <?php echo $page == $i ? 'btn-primary' : 'btn-outline'; ?>"
                                    style="padding: 5px 12px; height: auto;">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>