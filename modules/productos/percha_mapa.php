<?php
/**
 * Shelf Map Viewer - Visual grid for product placement
 */
session_start();
require_once '../../includes/db.php';

$percha_id = $_GET['id'] ?? null;
$product_to_locate = $_GET['product_id'] ?? null;

if (!$percha_id) {
    header('Location: ubicaciones.php');
    exit;
}

// Fetch shelf details
$stmt = $pdo->prepare("SELECT p.*, s.nombre as seccion_nombre FROM perchas p JOIN secciones s ON p.seccion_id = s.id WHERE p.id = ?");
$stmt->execute([$percha_id]);
$percha = $stmt->fetch();

if (!$percha) {
    header('Location: ubicaciones.php');
    exit;
}

// Fetch products currently in this shelf to mark occupied cells
$stmt = $pdo->prepare("SELECT nombre, percha_fila, percha_columna FROM productos WHERE percha_id = ? AND estado = 1");
$stmt->execute([$percha_id]);
$occupied_cells = [];
while ($row = $stmt->fetch()) {
    $occupied_cells[$row['percha_fila']][$row['percha_columna']] = $row['nombre'];
}

$product_name = "";
if ($product_to_locate) {
    $stmt = $pdo->prepare("SELECT nombre FROM productos WHERE id = ?");
    $stmt->execute([$product_to_locate]);
    $product_name = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Percha:
        <?php echo $percha['nombre']; ?> | Warehouse POS
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/../../assets/css/ubicaciones.css">
    <link rel="stylesheet" href="../../assets/css/ubicaciones.css">
    <style>
        .map-header {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .shelf-visual-grid {
            display: grid;
            gap: 8px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: auto;
            max-height: 70vh;
        }

        .cell {
            aspect-ratio: 1;
            min-width: 60px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.7rem;
            color: #94a3b8;
            position: relative;
        }

        .cell:hover:not(.occupied) {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #3b82f6;
            transform: scale(1.05);
            z-index: 10;
        }

        .cell.occupied {
            background: #ecfdf5;
            border-color: #10b981;
            color: #10b981;
            cursor: default;
        }

        .cell.occupied i {
            font-size: 1.2rem;
            margin-bottom: 4px;
        }

        .cell-pos {
            position: absolute;
            top: 2px;
            left: 4px;
            font-size: 0.6rem;
            opacity: 0.5;
        }

        .legend {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #64748b;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        .locating-banner {
            background: #fffbef;
            border: 1px solid #fde68a;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(245, 158, 11, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
            }
        }
    </style>
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
                <div class="page-header">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb" style="font-size: 0.75rem; margin-bottom: 5px;">
                            <li class="breadcrumb-item"><a href="ubicaciones.php">Ubicaciones</a></li>
                            <li class="breadcrumb-item"><a
                                    href="perchas.php?id=<?php echo $percha['seccion_id']; ?>">Perchas</a></li>
                            <li class="breadcrumb-item active">Mapa</li>
                        </ol>
                    </nav>
                    <h1><i class="fas fa-th"></i> Mapa Visual:
                        <?php echo $percha['nombre']; ?>
                    </h1>
                </div>

                <?php if ($product_to_locate): ?>
                    <div class="locating-banner">
                        <div>
                            <i class="fas fa-thumbtack" style="color: #f59e0b; margin-right: 10px;"></i>
                            <span>Ubicando producto: <strong>
                                    <?php echo $product_name; ?>
                                </strong></span>
                        </div>
                        <span style="font-size: 0.8rem; color: #b45309;">Haz clic en una celda vacía para posicionar</span>
                    </div>
                <?php endif; ?>

                <div class="map-header">
                    <div>
                        <span style="color: #64748b; font-size: 0.9rem;">Sección:</span>
                        <strong style="margin-left: 5px;">
                            <?php echo $percha['seccion_nombre']; ?>
                        </strong>
                    </div>
                    <div style="display: flex; gap: 15px; color: #64748b; font-size: 0.9rem;">
                        <span>Filas: <strong>
                                <?php echo $percha['filas']; ?>
                            </strong></span>
                        <span>Columnas: <strong>
                                <?php echo $percha['columnas']; ?>
                            </strong></span>
                        <span>Capacidad: <strong>
                                <?php echo $percha['filas'] * $percha['columnas']; ?>
                            </strong></span>
                    </div>
                    <a href="perchas.php?id=<?php echo $percha['seccion_id']; ?>"
                        class="btn btn-outline btn-sm">Volver</a>
                </div>

                <div class="shelf-visual-grid"
                    style="grid-template-columns: repeat(<?php echo $percha['columnas']; ?>, 1fr);">
                    <?php
                    for ($f = 1; $f <= $percha['filas']; $f++) {
                        for ($c = 1; $c <= $percha['columnas']; $c++) {
                            $is_occupied = isset($occupied_cells[$f][$c]);
                            $occu_name = $is_occupied ? $occupied_cells[$f][$c] : "";
                            ?>
                            <div class="cell <?php echo $is_occupied ? 'occupied' : ''; ?>"
                                title="<?php echo $is_occupied ? 'Ocupado por: ' . $occu_name : 'Fila ' . $f . ', Columna ' . $c; ?>"
                                data-fila="<?php echo $f; ?>" data-columna="<?php echo $c; ?>" onclick="selectCell(this)">
                                <span class="cell-pos">
                                    <?php echo $f . '-' . $c; ?>
                                </span>
                                <?php if ($is_occupied): ?>
                                    <i class="fas fa-pills"></i>
                                    <span style="font-size: 0.55rem; text-align: center; max-height: 20px; overflow: hidden;">
                                        <?php echo strlen($occu_name) > 10 ? substr($occu_name, 0, 8) . '..' : $occu_name; ?>
                                    </span>
                                <?php else: ?>
                                    <i class="fas fa-plus" style="opacity: 0.2;"></i>
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>

                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #fff; border: 1px solid #e2e8f0;"></div>
                        <span>Disponible</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #ecfdf5; border: 1px solid #10b981;"></div>
                        <span>Ocupado</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #eff6ff; border: 1px solid #3b82f6;"></div>
                        <span>Seleccionado</span>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal Confirmación Ubicación -->
    <div class="modal-overlay" id="modal-confirm">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2>Confirmar Ubicación</h2>
                <button class="btn-text close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="text-align: center; padding: 30px;">
                <div
                    style="width: 60px; height: 60px; background: #eff6ff; color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.5rem;">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <p>¿Ubicar <strong>
                        <?php echo $product_name; ?>
                    </strong> en la <strong>Fila <span id="conf-fila"></span>, Columna <span
                            id="conf-col"></span></strong> de la percha <strong>
                        <?php echo $percha['nombre']; ?>
                    </strong>?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cancelar</button>
                <form action="save_location.php" method="POST">
                    <input type="hidden" name="producto_id" value="<?php echo $product_to_locate; ?>">
                    <input type="hidden" name="percha_id" value="<?php echo $percha_id; ?>">
                    <input type="hidden" name="fila" id="form-fila">
                    <input type="hidden" name="columna" id="form-col">
                    <button type="submit" class="btn btn-primary">Confirmar Ubicación</button>
                </form>
            </div>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        const isLocating = <?php echo $product_to_locate ? 'true' : 'false'; ?>;

        function selectCell(el) {
            if (el.classList.contains('occupied')) return;
            if (!isLocating) {
                // Just viewing, maybe show details if we implement that
                return;
            }

            const fila = el.dataset.fila;
            const col = el.dataset.columna;

            document.getElementById('conf-fila').textContent = fila;
            document.getElementById('conf-col').textContent = col;
            document.getElementById('form-fila').value = fila;
            document.getElementById('form-col').value = col;

            document.getElementById('modal-confirm').style.display = 'flex';
        }

        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.onclick = () => document.getElementById('modal-confirm').style.display = 'none';
        });
    </script>
</body>

</html>