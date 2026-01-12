<?php
/**
 * Manage Sections - Establishments Sections Detailed View
 */
session_start();
require_once '../../includes/db.php';

// Mock data
$total_secciones = 8;
$total_perchas = 16;
$productos_ubicados = 1;
$sin_ubicar = 1801;

$config_secciones = [
    ['nombre' => 'Medicamentos', 'desc' => 'Medicamentos recetados y de venta libre', 'color' => '#ef4444', 'perchas' => 2, 'ubicados' => 1, 'orden' => 1],
    ['nombre' => 'Medicamentos 2', 'desc' => 'Medicamentos recetados y de venta libre', 'color' => '#ef4444', 'perchas' => 2, 'ubicados' => 0, 'orden' => 1],
    ['nombre' => 'Cosméticos', 'desc' => 'Productos de belleza y cuidado personal', 'color' => '#10b981', 'perchas' => 2, 'ubicados' => 0, 'orden' => 2],
    ['nombre' => 'Cosméticos 2', 'desc' => 'Productos de belleza y cuidado personal', 'color' => '#10b981', 'perchas' => 2, 'ubicados' => 0, 'orden' => 2],
    ['nombre' => 'Higiene', 'desc' => 'Productos de higiene personal', 'color' => '#3b82f6', 'perchas' => 2, 'ubicados' => 0, 'orden' => 3],
    ['nombre' => 'Higiene 2', 'desc' => 'Productos de higiene personal', 'color' => '#3b82f6', 'perchas' => 2, 'ubicados' => 0, 'orden' => 3],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Secciones | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/ubicaciones.css">
</head>

<body>
    <div class="app-container">
        <?php
        $root = '../../';
        $current_page = 'ubicaciones';
        include $root . 'includes/sidebar.php';
        ?>

        <main class="main-content">
            <?php include $root . 'includes/navbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header"
                    style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h1><i class="fas fa-folder-open"></i> Gestionar Secciones</h1>
                        <p style="color: #64748b; margin-top: 5px;">Organiza tu establecimiento por secciones
                            principales</p>
                    </div>
                    <div class="management-actions">
                        <a href="ubicaciones.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
                        <button class="btn btn-primary" id="btn-nueva-seccion"><i class="fas fa-plus"></i> Nueva
                            Sección</button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-row">
                    <div class="stat-box blue">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $total_secciones; ?>
                            </span>
                            <span class="lbl">Total Secciones</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-folder"></i></div>
                    </div>
                    <div class="stat-box green">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $total_perchas; ?>
                            </span>
                            <span class="lbl">Total Perchas</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-th"></i></div>
                    </div>
                    <div class="stat-box cyan">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $productos_ubicados; ?>
                            </span>
                            <span class="lbl">Productos Ubicados</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-map-marker-alt"></i></div>
                    </div>
                    <div class="stat-box orange">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $sin_ubicar; ?>
                            </span>
                            <span class="lbl">Sin Ubicar</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-question"></i></div>
                    </div>
                </div>

                <!-- Configured Sections Grid -->
                <div class="card-title-row">
                    <i class="fas fa-list-ul"></i>
                    <h3>Secciones Configuradas</h3>
                </div>

                <div class="sections-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <?php foreach ($config_secciones as $sec): ?>
                        <div class="section-config-card">
                            <div class="card-head">
                                <div class="section-head">
                                    <div class="color-dot" style="background: <?php echo $sec['color']; ?>;"></div>
                                    <strong>
                                        <?php echo $sec['nombre']; ?>
                                    </strong>
                                </div>
                                <button class="card-options-btn"><i class="fas fa-ellipsis-v"></i></button>
                            </div>
                            <p style="padding: 0 20px; font-size: 0.8rem; color: #64748b; margin-top: 10px;">
                                <?php echo $sec['desc']; ?>
                            </p>
                            <div class="card-body">
                                <div class="config-stat">
                                    <label>Perchas</label>
                                    <b>
                                        <?php echo $sec['perchas']; ?>
                                    </b>
                                </div>
                                <div class="config-stat">
                                    <label>Ubicados</label>
                                    <b>
                                        <?php echo $sec['ubicados']; ?>
                                    </b>
                                </div>
                                <div class="config-stat">
                                    <label>Orden</label>
                                    <b>
                                        <?php echo $sec['orden']; ?>
                                    </b>
                                </div>
                            </div>
                            <div class="card-foot-btns">
                                <a href="perchas.php?id=1" class="btn btn-primary btn-sm"><i class="fas fa-th"></i>
                                    Gestionar Perchas</a>
                                <button class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Editar</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal: Nueva Sección -->
    <div class="modal-overlay" id="modal-seccion">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Nueva Sección</h2>
                <button class="btn-text close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="form-seccion">
                    <div class="form-group">
                        <label>Nombre de la Sección *</label>
                        <input type="text" class="form-control" placeholder="Ej: Productos A, Accesorios, Servicios">
                        <span class="input-hint">Ej: Productos A, Accesorios, Servicios</span>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" rows="3"
                            placeholder="Descripción opcional de la sección"></textarea>
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Color Identificativo</label>
                            <input type="color" class="form-control" value="#007bff"
                                style="height: 45px; padding: 5px; cursor: pointer;">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Orden</label>
                            <input type="number" value="0" class="form-control">
                            <span class="input-hint">Orden de visualización</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cancelar</button>
                <button class="btn btn-primary" type="submit" form="form-seccion">Guardar Sección</button>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        const modal = document.getElementById('modal-seccion');
        const btnOpen = document.getElementById('btn-nueva-seccion');
        const btnsClose = document.querySelectorAll('.close-modal');

        btnOpen.onclick = () => modal.style.display = 'flex';
        btnsClose.forEach(btn => btn.onclick = () => modal.style.display = 'none');
        window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; }
    </script>
</body>

</html>