<?php
/**
 * Audit Log - Registro de Auditoría
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'usuarios_auditoria';

// Pagination logic
$limit = 50;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Filter logic
$modulo_filter = $_GET['modulo'] ?? '';
$usuario_filter = $_GET['usuario'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$where = " WHERE 1=1";
$params = [];

if ($modulo_filter) {
    $where .= " AND modulo = ?";
    $params[] = $modulo_filter;
}
if ($usuario_filter) {
    $where .= " AND usuario LIKE ?";
    $params[] = "%$usuario_filter%";
}
if ($desde) {
    $where .= " AND DATE(fecha) >= ?";
    $params[] = $desde;
}
if ($hasta) {
    $where .= " AND DATE(fecha) <= ?";
    $params[] = $hasta;
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM auditoria" . $where;
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$total_records = $stmtCount->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch logs with pagination
$query = "SELECT * FROM auditoria" . $where . " ORDER BY fecha DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Fetch unique modules for the filter
$stmtModules = $pdo->query("SELECT DISTINCT modulo FROM auditoria ORDER BY modulo ASC");
$modulos = $stmtModules->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Auditoría | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .au-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .au-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .au-filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: flex-end;
            border: 1px solid #f1f5f9;
        }

        .au-filters label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .au-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .au-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .au-table th {
            background: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #f1f5f9;
            white-space: nowrap;
        }

        .au-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .au-table tr:hover {
            background-color: #f1f5f9;
            transition: background 0.2s;
        }

        .badge-action {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .action-login {
            background: #dcfce7;
            color: #15803d;
        }

        .action-visualizar {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .action-logout {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-crear {
            background: #fef9c3;
            color: #854d0e;
        }

        .user-tag {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .user-avatar-small {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .ip-text {
            font-family: monospace;
            color: #64748b;
            font-size: 0.75rem;
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 25px;
            padding-bottom: 20px;
        }

        .pg-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .pg-link:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #f1f5f9;
        }

        .pg-link.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .pg-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pg-text {
            color: #64748b;
            font-size: 0.85rem;
            margin: 0 10px;
        }

        @media (max-width: 1024px) {
            .au-table-container {
                border: none;
                box-shadow: none;
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
                <div class="au-header">
                    <h1><i class="fas fa-clipboard-list"></i> Registro de Auditoría</h1>
                    <div style="color: #64748b; font-size: 0.85rem;">
                        <i class="fas fa-info-circle"></i> Mostrando <?php echo count($logs); ?> de <?php echo number_format($total_records); ?> registros
                    </div>
                </div>

                <form method="GET" class="au-filters">
                    <div>
                        <label>Módulo</label>
                        <select name="modulo" class="form-control">
                            <option value="">Todos los módulos</option>
                            <?php foreach ($modulos as $mod): ?>
                                <option value="<?php echo $mod; ?>" <?php echo $modulo_filter == $mod ? 'selected' : ''; ?>>
                                    <?php echo $mod; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Usuario</label>
                        <input type="text" name="usuario" class="form-control" placeholder="Buscar por usuario..."
                            value="<?php echo htmlspecialchars($usuario_filter); ?>">
                    </div>
                    <div>
                        <label>Desde</label>
                        <input type="date" name="desde" class="form-control" value="<?php echo $desde; ?>">
                    </div>
                    <div>
                        <label>Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="<?php echo $hasta; ?>">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1; height: 42px;">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="auditoria.php" class="btn btn-secondary"
                            style="height: 42px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </div>
                </form>

                <div class="au-table-container">
                    <table class="au-table">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Usuario</th>
                                <th>Módulo</th>
                                <th>Acción</th>
                                <th>Entidad</th>
                                <th>Descripción</th>
                                <th>IP / Origen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        <i class="fas fa-search fa-3x" style="margin-bottom: 15px; opacity: 0.5;"></i>
                                        <p>No se encontraron registros que coincidan con los filtros.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log):
                                    $action_class = 'action-' . strtolower($log['accion']);
                                    // Handle cases where class might not exist
                                    if (!in_array($log['accion'], ['LOGIN', 'VISUALIZAR', 'LOGOUT', 'CREAR'])) {
                                        $action_class = '';
                                    }
                                    ?>
                                    <tr>
                                        <td style="font-weight: 500; font-size: 0.8rem; color: #1e293b; white-space: nowrap;">
                                            <?php echo date('d/m/Y H:i:s', strtotime($log['fecha'])); ?>
                                        </td>
                                        <td>
                                            <div class="user-tag">
                                                <div class="user-avatar-small">
                                                    <?php echo strtoupper(substr($log['usuario'], 0, 1)); ?>
                                                </div>
                                                <span>
                                                    <?php echo htmlspecialchars($log['usuario']); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-weight: 700; color: #475569;">
                                                <?php echo htmlspecialchars($log['modulo']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-action <?php echo $action_class; ?>">
                                                <?php echo htmlspecialchars($log['accion']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="color: #64748b; font-size: 0.75rem;">
                                                <i class="fas fa-database" style="font-size: 0.65rem; margin-right: 4px;"></i>
                                                <?php echo htmlspecialchars($log['entidad']); ?>
                                                <?php echo $log['idEntidad'] ? " <span style='color: #94a3b8;'>(ID: {$log['idEntidad']})</span>" : ""; ?>
                                            </span>
                                        </td>
                                        <td style="max-width: 300px;">
                                            <?php echo htmlspecialchars($log['descripcion']); ?>
                                        </td>
                                        <td>
                                            <span class="ip-text">
                                                <?php echo $log['ip'] ?: '127.0.0.1'; ?>
                                            </span>
                                            <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700;">
                                                ORIGEN:
                                                <?php echo htmlspecialchars($log['origen']); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination UI -->
                <?php if ($total_pages > 1): ?>
                    <?php 
                        $query_params = $_GET;
                        unset($query_params['page']);
                        $base_url = "auditoria.php?" . http_build_query($query_params) . "&page=";
                    ?>
                    <div class="pagination">
                        <a href="<?php echo $base_url . ($page - 1); ?>" class="pg-link <?php echo $page <= 1 ? 'disabled' : ''; ?>" title="Anterior">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        if ($start > 1) {
                            echo '<a href="'.$base_url.'1" class="pg-link">1</a>';
                            if ($start > 2) echo '<span class="pg-text">...</span>';
                        }
                        
                        for ($i = $start; $i <= $end; $i++): ?>
                            <a href="<?php echo $base_url . $i; ?>" class="pg-link <?php echo $page == $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($end < $total_pages): ?>
                            <?php if ($end < $total_pages - 1) echo '<span class="pg-text">...</span>'; ?>
                            <a href="<?php echo $base_url . $total_pages; ?>" class="pg-link"><?php echo $total_pages; ?></a>
                        <?php endif; ?>

                        <a href="<?php echo $base_url . ($page + 1); ?>" class="pg-link <?php echo $page >= $total_pages ? 'disabled' : ''; ?>" title="Siguiente">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>