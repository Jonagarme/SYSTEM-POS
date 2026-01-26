<?php
/**
 * Location Management - Ubicaciones
 */
session_start();
require_once '../../includes/db.php';

// Fetch from database
try {
    $stmt = $pdo->query("SELECT * FROM inventario_ubicacion WHERE anulado = 0 ORDER BY es_principal DESC, nombre ASC");
    $ubicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $total_ubicaciones = count($ubicaciones);
    $activas = 0;
    foreach ($ubicaciones as $u) {
        if ($u['activo'])
            $activas++;
    }
} catch (PDOException $e) {
    $ubicaciones = [];
    $total_ubicaciones = 0;
    $activas = 0;
    $error = $e->getMessage();
}

$current_page = 'inventario_ubicaciones';

// Deletion Logic
if (isset($_GET['delete'])) {
    try {
        $id_delete = (int) $_GET['delete'];
        $stmt = $pdo->prepare("UPDATE inventario_ubicacion SET anulado = 1 WHERE id = ?");
        $stmt->execute([$id_delete]);
        header("Location: ubicaciones.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

$msg_success = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $msg_success = "Ubicación eliminada correctamente.";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubicaciones | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .ubicaciones-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .ubicaciones-title h1 {
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

        .summary-grid-ubic {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            max-width: 800px;
        }

        .u-card {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--card-shadow);
        }

        .u-card.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .u-card .info h3 {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
        }

        .u-card .info .label {
            font-size: 0.8rem;
            font-weight: 500;
            opacity: 0.9;
        }

        .u-card .icon {
            font-size: 2rem;
            opacity: 0.3;
        }

        .locations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .loc-item-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .loc-item-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
        }

        .loc-type-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .type-bodega {
            background: #e0f2fe;
            color: #0369a1;
        }

        .type-sucursal {
            background: #fef3c7;
            color: #92400e;
        }

        .type-almacen {
            background: #dcfce7;
            color: #166534;
        }

        .loc-name {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .loc-name h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
        }

        .principal-star {
            color: #f59e0b;
            font-size: 0.9rem;
        }

        .loc-info-list {
            margin-bottom: 20px;
        }

        .loc-info-item {
            display: flex;
            gap: 10px;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .loc-info-item i {
            width: 16px;
            color: #94a3b8;
        }

        .loc-actions {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
        }

        @media (max-width: 768px) {
            .ubicaciones-header {
                flex-direction: column;
                align-items: stretch;
            }

            .locations-grid {
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
                <div class="ubicaciones-header">
                    <div class="ubicaciones-title">
                        <h1>Gestión de Ubicaciones</h1>
                        <div class="breadcrumb">
                            <a href="../../index.php">Dashboard</a> / <span>Inventario</span> / <span>Ubicaciones</span>
                        </div>
                    </div>
                    <a href="nueva_ubicacion.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Ubicación
                    </a>
                </div>

                <?php if ($msg_success): ?>
                    <div class="alert alert-success"
                        style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                        <i class="fas fa-check-circle"></i> <?php echo $msg_success; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-danger"
                        style="padding: 15px; border-radius: 8px; margin-bottom: 20px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="summary-grid-ubic">
                    <div class="u-card">
                        <div class="info">
                            <div class="label">Total Ubicaciones</div>
                            <h3><?php echo $total_ubicaciones; ?></h3>
                        </div>
                        <i class="fas fa-map-marker-alt icon"></i>
                    </div>
                    <div class="u-card success">
                        <div class="info">
                            <div class="label">Activas</div>
                            <h3><?php echo $activas; ?></h3>
                        </div>
                        <i class="fas fa-check-circle icon"></i>
                    </div>
                </div>

                <div class="locations-grid">
                    <?php if (empty($ubicaciones)): ?>
                        <div
                            style="grid-column: 1/-1; padding: 60px; text-align: center; background: white; border-radius: 12px;">
                            <i class="fas fa-map-marked-alt"
                                style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px; display: block;"></i>
                            <h3 style="color: #475569;">No hay ubicaciones registradas</h3>
                            <p style="color: #64748b;">Comienza agregando una bodega o sucursal.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ubicaciones as $u):
                            $badge_class = 'type-' . strtolower($u['tipo']);
                            ?>
                            <div class="loc-item-card">
                                <span class="loc-type-badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($u['tipo']); ?>
                                </span>
                                <div class="loc-name">
                                    <h2><?php echo htmlspecialchars($u['nombre']); ?></h2>
                                    <?php if ($u['es_principal']): ?>
                                        <i class="fas fa-star principal-star" title="Ubicación Principal"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="loc-info-list">
                                    <div class="loc-info-item">
                                        <i class="fas fa-tag"></i>
                                        <span>Código: <?php echo htmlspecialchars($u['codigo']); ?></span>
                                    </div>
                                    <div class="loc-info-item">
                                        <i class="fas fa-map-pin"></i>
                                        <span><?php echo htmlspecialchars($u['direccion']); ?></span>
                                    </div>
                                    <div class="loc-info-item">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo htmlspecialchars($u['telefono']); ?></span>
                                    </div>
                                    <div class="loc-info-item">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Resp: <?php echo htmlspecialchars($u['responsable']); ?></span>
                                    </div>
                                </div>
                                <div class="loc-actions">
                                    <button class="btn btn-outline" style="flex: 1;"
                                        onclick="editUbicacion(<?php echo $u['id']; ?>)">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-outline" style="color: #ef4444; border-color: #fecaca;"
                                        onclick="deleteUbicacion(<?php echo $u['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals and script placeholders -->
    <script>
        function openCreateModal() { window.location.href = 'nueva_ubicacion.php'; }

        function editUbicacion(id) {
            window.location.href = 'editar_ubicacion.php?id=' + id;
        }

        function deleteUbicacion(id) {
            if (confirm('¿Está seguro de eliminar esta bodega/ubicación? Esta acción no se puede deshacer.')) {
                window.location.href = 'ubicaciones.php?delete=' + id;
            }
        }
    </script>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>