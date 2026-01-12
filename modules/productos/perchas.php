<?php
/**
 * Perchas Management - Detailed view of shelves in a section
 */
session_start();
require_once '../../includes/db.php';

// Mock data
$section_name = "Cosméticos";
$total_perchas = 2;
$productos_ubicados = 0;
$capacidad_total = 100;

$perchas = [
    ['nombre' => 'Percha A1', 'filas' => 5, 'columnas' => 10, 'ocupado' => 0],
    ['nombre' => 'Percha A2', 'filas' => 5, 'columnas' => 10, 'ocupado' => 0],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perchas -
        <?php echo $section_name; ?> | Warehouse POS
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/../../assets/css/ubicaciones.css">
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
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb" style="font-size: 0.75rem; margin-bottom: 5px;">
                                <li class="breadcrumb-item"><a href="ubicaciones.php">Ubicaciones</a></li>
                                <li class="breadcrumb-item"><a href="secciones.php">Secciones</a></li>
                                <li class="breadcrumb-item active">
                                    <?php echo $section_name; ?>
                                </li>
                            </ol>
                        </nav>
                        <h1><i class="fas fa-th-large"></i> Perchas -
                            <?php echo $section_name; ?>
                        </h1>
                    </div>
                    <div class="management-actions">
                        <a href="secciones.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
                        <button class="btn btn-primary" id="btn-nueva-percha"><i class="fas fa-plus"></i> Nueva
                            Percha</button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $total_perchas; ?>
                            </span>
                            <span class="lbl">Total Perchas</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-warehouse"></i></div>
                    </div>
                    <div class="stat-box green">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $productos_ubicados; ?>
                            </span>
                            <span class="lbl">Productos Ubicados</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <div class="stat-box cyan">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $capacidad_total; ?>
                            </span>
                            <span class="lbl">Capacidad Total</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-building"></i></div>
                    </div>
                    <div class="stat-box orange">
                        <div class="stat-info">
                            <span class="val">0%</span>
                            <span class="lbl">Ocupación</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-chart-pie"></i></div>
                    </div>
                </div>

                <!-- Perchas List -->
                <div class="card-title-row">
                    <i class="fas fa-list"></i>
                    <h3>Perchas en
                        <?php echo $section_name; ?>
                    </h3>
                </div>

                <div class="sections-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <?php foreach ($perchas as $p): ?>
                        <div class="section-config-card" style="border-left: 4px solid #10b981;">
                            <div class="card-head">
                                <strong>
                                    <?php echo $p['nombre']; ?>
                                </strong>
                                <button class="card-options-btn"><i class="fas fa-ellipsis-v"></i></button>
                            </div>
                            <div style="padding: 15px 20px;">
                                <label style="font-size: 0.75rem; color: #64748b;">Ocupación</label>
                                <div class="progress-container"
                                    style="height: 6px; background: #f1f5f9; border-radius: 3px; margin: 8px 0;">
                                    <div class="progress-bar"
                                        style="width: <?php echo $p['ocupado']; ?>%; height: 100%; background: #10b981; border-radius: 3px;">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" style="padding-top: 5px;">
                                <div class="config-stat">
                                    <label>Filas</label>
                                    <b>
                                        <?php echo $p['filas']; ?>
                                    </b>
                                </div>
                                <div class="config-stat">
                                    <label>Columnas</label>
                                    <b>
                                        <?php echo $p['columnas']; ?>
                                    </b>
                                </div>
                                <div class="config-stat">
                                    <label>Total</label>
                                    <b>
                                        <?php echo $p['filas'] * $p['columnas']; ?>
                                    </b>
                                </div>
                            </div>
                            <div style="padding: 10px 20px 20px;">
                                <button class="btn btn-outline btn-block btn-sm" style="width: 100%;"><i
                                        class="fas fa-th"></i> Ver Mapa de Percha</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal: Nueva Percha -->
    <div class="modal-overlay" id="modal-percha">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Nueva Percha</h2>
                <button class="btn-text close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="form-percha">
                    <div class="form-group">
                        <label>Nombre de la Percha *</label>
                        <input type="text" class="form-control"
                            placeholder="Ej: Percha A1, Estante Central, Vitrina Principal">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" rows="2"
                            placeholder="Describe qué tipo de productos van en esta percha"></textarea>
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Número de Filas *</label>
                            <input type="number" value="5" id="input-filas" class="form-control">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Número de Columnas *</label>
                            <input type="number" value="10" id="input-cols" class="form-control">
                        </div>
                    </div>
                    <div
                        style="background: #e0f2fe; padding: 15px; border-radius: 8px; color: #0369a1; display: flex; align-items: center; gap: 10px; font-size: 0.9rem; margin-top: 10px;">
                        <i class="fas fa-info-circle"></i>
                        <span><b>Capacidad total:</b> <span id="total-cap">50</span> posiciones</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cancelar</button>
                <button class="btn btn-primary" type="submit" form="form-percha">Guardar Percha</button>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        const modal = document.getElementById('modal-percha');
        const btnOpen = document.getElementById('btn-nueva-percha');
        const btnsClose = document.querySelectorAll('.close-modal');

        btnOpen.onclick = () => modal.style.display = 'flex';
        btnsClose.forEach(btn => btn.onclick = () => modal.style.display = 'none');

        // Dynamic capacity calculation
        const inputF = document.getElementById('input-filas');
        const inputC = document.getElementById('input-cols');
        const spanCap = document.getElementById('total-cap');

        [inputF, inputC].forEach(input => {
            input.oninput = () => {
                const total = (inputF.value || 0) * (inputC.value || 0);
                spanCap.textContent = total;
            }
        });
    </script>
</body>

</html>