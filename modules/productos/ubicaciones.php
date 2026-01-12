<?php
/**
 * Product Locations - Main Warehouse View
 */
session_start();
require_once '../../includes/db.php';

// Real data queries
$total_productos = $pdo->query("SELECT COUNT(*) FROM productos WHERE anulado = 0")->fetchColumn();
$productos_ubicados = $pdo->query("SELECT COUNT(DISTINCT producto_id) FROM productos_ubicacionproducto")->fetchColumn();
$sin_ubicar = $total_productos - $productos_ubicados;
$total_perchas = $pdo->query("SELECT COUNT(*) FROM productos_percha WHERE activo = 1")->fetchColumn();

// Sections with real stats
$stmtSec = $pdo->query("
    SELECT s.*, 
    (SELECT COUNT(*) FROM productos_percha WHERE seccion_id = s.id) as num_perchas,
    (SELECT COUNT(DISTINCT pu.producto_id) FROM productos_ubicacionproducto pu 
     JOIN productos_percha pp ON pu.percha_id = pp.id 
     WHERE pp.seccion_id = s.id) as num_productos
    FROM productos_seccion s
    WHERE s.activo = 1
    ORDER BY s.orden
");
$secciones_raw = $stmtSec->fetchAll();
$secciones = [];
foreach ($secciones_raw as $s) {
    $secciones[] = [
        'nombre' => $s['nombre'],
        'color' => $s['color'] ?? '#3b82f6',
        'perchas' => $s['num_perchas'],
        'productos' => $s['num_productos']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubicaciones de Productos | Warehouse POS</title>
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
                <!-- Page Breadcrumb -->
                <div class="page-header" style="margin-bottom: 24px;">
                    <h1>Ubicaciones de Productos</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../../index.php">Inicio</a></li>
                            <li class="breadcrumb-item active">Ubicaciones</li>
                        </ol>
                    </nav>
                </div>

                <!-- Main Banner -->
                <div class="locations-header">
                    <div class="locations-title-area">
                        <h2><i class="fas fa-map-marked-alt"></i> Ubicaciones de Productos</h2>
                        <p>Gestiona la ubicación de productos en perchas del establecimiento</p>
                    </div>
                    <a href="secciones.php" class="btn btn-light"><i class="fas fa-cog"></i> Gestionar Secciones</a>
                </div>

                <!-- Stats Cards -->
                <div class="stats-row">
                    <div class="stat-box blue">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $total_productos; ?>
                            </span>
                            <span class="lbl">Total Productos</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-boxes"></i></div>
                    </div>
                    <div class="stat-box green">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $productos_ubicados; ?>
                            </span>
                            <span class="lbl">Productos Ubicados</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-map-pin"></i></div>
                    </div>
                    <div class="stat-box orange">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $sin_ubicar; ?>
                            </span>
                            <span class="lbl">Sin Ubicar</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-question-circle"></i></div>
                    </div>
                    <div class="stat-box cyan">
                        <div class="stat-info">
                            <span class="val">
                                <?php echo $total_perchas; ?>
                            </span>
                            <span class="lbl">Total Perchas</span>
                        </div>
                        <div class="stat-icon-circle"><i class="fas fa-th"></i></div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="sections-container">
                    <!-- Sections List -->
                    <div class="sections-main">
                        <div class="card-title-row">
                            <i class="fas fa-warehouse"></i>
                            <h3>Secciones del Establecimiento</h3>
                        </div>
                        <div class="sections-grid">
                            <?php foreach ($secciones as $sec): ?>
                                <div class="section-item-card">
                                    <div class="section-head">
                                        <div class="color-dot" style="background: <?php echo $sec['color']; ?>"></div>
                                        <strong>
                                            <?php echo $sec['nombre']; ?>
                                        </strong>
                                    </div>
                                    <div class="section-stats">
                                        <div class="stat-group">
                                            <span>Perchas</span>
                                            <b>
                                                <?php echo $sec['perchas']; ?>
                                            </b>
                                        </div>
                                        <div class="stat-group">
                                            <span>Productos</span>
                                            <b>
                                                <?php echo $sec['productos']; ?>
                                            </b>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Sidebar: Unlocated & Search -->
                    <div class="sidebar-locations">
                        <!-- Sin Ubicar List -->
                        <div class="sidebar-unlocated">
                            <div class="sidebar-head">
                                <h3><i class="fas fa-exclamation-triangle"></i> Sin Ubicar</h3>
                                <?php
                                $stmtCount = $pdo->query("SELECT COUNT(*) FROM productos p LEFT JOIN productos_ubicacionproducto u ON p.id = u.producto_id WHERE u.id IS NULL AND p.anulado = 0");
                                $sinUbicarCount = $stmtCount->fetchColumn();
                                ?>
                                <span class="count-badge"><?php echo $sinUbicarCount; ?></span>
                            </div>
                            <div class="unlocated-list shadow-inner">
                                <?php
                                $stmt = $pdo->query("SELECT p.id, p.nombre, p.codigoPrincipal as codigo, p.stock as stock_actual FROM productos p LEFT JOIN productos_ubicacionproducto u ON p.id = u.producto_id WHERE u.id IS NULL AND p.anulado = 0 LIMIT 20");
                                $unlocated = $stmt->fetchAll();
                                if (empty($unlocated)): ?>
                                    <div style="text-align: center; padding: 20px; color: #94a3b8;">
                                        <i class="fas fa-check-circle"
                                            style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                        <p>Todos ubicados</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($unlocated as $prod): ?>
                                        <div class="unlocated-item">
                                            <h4><?php echo htmlspecialchars($prod['nombre']); ?></h4>
                                            <span class="sku"><?php echo htmlspecialchars($prod['codigo']); ?></span>
                                            <span class="stock-pill">Stock:
                                                <?php echo number_format($prod['stock_actual'], 2); ?></span>
                                            <button class="btn-locate-mini" title="Ubicar"
                                                onclick="openLocationSelector(<?php echo $prod['id']; ?>, '<?php echo addslashes($prod['nombre']); ?>')">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Search -->
                        <div class="sidebar-unlocated" style="margin-top: 20px;">
                            <div class="sidebar-head">
                                <h3><i class="fas fa-search"></i> Búsqueda Rápida</h3>
                            </div>
                            <div class="modal-body" style="padding: 15px;">
                                <div class="form-group">
                                    <div class="input-with-icon" style="position: relative;">
                                        <i class="fas fa-search"
                                            style="position: absolute; left: 10px; top: 12px; color: #94a3b8;"></i>
                                        <input type="text" class="form-control" placeholder="Buscar producto..."
                                            style="padding-left: 35px; width: 100%; height: 38px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal: Seleccionar Destino -->
    <div class="modal-overlay" id="modal-selector">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h2>Ubicar Producto</h2>
                <button class="btn-text close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 15px;">Selecciona el destino para: <strong id="loc-prod-name"></strong></p>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>1. Selecciona la Sección</label>
                    <select id="sel-seccion" class="form-control"
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <option value="">-- Elige una sección --</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, nombre FROM secciones ORDER BY orden, nombre");
                        while ($s = $stmt->fetch())
                            echo "<option value='{$s['id']}'>{$s['nombre']}</option>";
                        ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 15px; display: none;" id="div-percha">
                    <label>2. Selecciona la Percha</label>
                    <select id="sel-percha" class="form-control"
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <option value="">-- Elige una percha --</option>
                    </select>
                </div>
                <div id="div-action" style="display: none; margin-top: 20px;">
                    <button class="btn btn-primary btn-block" id="btn-ir-mapa" style="width:100%">
                        Ir al Mapa Visual <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        let currentProductId = null;
        function openLocationSelector(id, name) {
            currentProductId = id;
            document.getElementById('loc-prod-name').textContent = name;
            document.getElementById('modal-selector').style.display = 'flex';
        }
        document.getElementById('sel-seccion').onchange = function () {
            const secId = this.value;
            const divP = document.getElementById('div-percha');
            const selP = document.getElementById('sel-percha');
            if (!secId) { divP.style.display = 'none'; return; }
            fetch(`get_perchas.php?seccion_id=${secId}`)
                .then(r => r.json())
                .then(data => {
                    selP.innerHTML = '<option value="">-- Elige una percha --</option>';
                    data.forEach(p => { selP.innerHTML += `<option value="${p.id}">${p.nombre} (${p.filas}x${p.columnas})</option>`; });
                    divP.style.display = 'block';
                });
        };
        document.getElementById('sel-percha').onchange = function () {
            document.getElementById('div-action').style.display = this.value ? 'block' : 'none';
        };
        document.getElementById('btn-ir-mapa').onclick = function () {
            const perchaId = document.getElementById('sel-percha').value;
            window.location.href = `percha_mapa.php?id=${perchaId}&product_id=${currentProductId}`;
        };
        document.querySelectorAll('.close-modal').forEach(btn => { btn.onclick = () => document.getElementById('modal-selector').style.display = 'none'; });
    </script>
</body>

</html>