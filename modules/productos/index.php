<?php
/**
 * Product Management - Advanced Grid View
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Pagination and Filters logic
$limit = 50;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$q = isset($_GET['q']) ? $_GET['q'] : '';
$cat_filter = isset($_GET['categoria']) ? (int) $_GET['categoria'] : 0;
$lab_filter = isset($_GET['laboratorio']) ? (int) $_GET['laboratorio'] : 0;

$where = " WHERE p.anulado = 0 ";
$params = [];

if ($q) {
    $where .= " AND (p.nombre LIKE :q OR p.codigoPrincipal LIKE :q OR p.descripcion LIKE :q) ";
    $params[':q'] = "%$q%";
}
if ($cat_filter) {
    $where .= " AND p.idCategoria = :cat ";
    $params[':cat'] = $cat_filter;
}
if ($lab_filter) {
    $where .= " AND p.idLaboratorio = :lab ";
    $params[':lab'] = $lab_filter;
}

try {
    // Stats queries
    $total_productos = $pdo->query("SELECT COUNT(*) FROM productos WHERE anulado = 0")->fetchColumn();
    $en_stock = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock > 0 AND anulado = 0")->fetchColumn();
    $stock_bajo = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock <= stockMinimo AND anulado = 0")->fetchColumn();
    $total_categorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();

    // Fetch filters data
    $categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
    $laboratorios = $pdo->query("SELECT id, nombre FROM laboratorios WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();

    // Fetch products
    $query = "SELECT p.*, l.nombre as laboratorio_nombre, c.nombre as categoria_nombre 
              FROM productos p
              LEFT JOIN laboratorios l ON p.idLaboratorio = l.id
              LEFT JOIN categorias c ON p.idCategoria = c.id
              $where
              ORDER BY p.creadoDate DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $productos = $stmt->fetchAll();

} catch (PDOException $e) {
    $productos = [];
    $categorias = [];
    $laboratorios = [];
    $error = $e->getMessage();
}

$current_page = 'productos';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos | Gestión Profesional</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/productos_grid.css">
    <style>
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .stats-mini-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-grid {
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
                <div class="page-header"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <div>
                        <h1 style="font-weight: 700; color: #1e293b; font-size: 1.5rem;">Gestión de Productos</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb"
                                style="display: flex; gap: 5px; font-size: 0.8rem; color: #64748b; list-style: none; padding: 0;">
                                <li><a href="../../index.php" style="color: #4f46e5; text-decoration: none;">Inicio</a>
                                </li>
                                <li> / Productos</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="nuevo.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Producto</a>
                </div>

                <!-- Top Stats -->
                <div class="stats-mini-grid">
                    <div class="stat-mini blue">
                        <div class="stat-mini-info">
                            <span class="val"><?php echo number_format($total_productos); ?></span>
                            <span class="lbl">Total Productos</span>
                        </div>
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="stat-mini green">
                        <div class="stat-mini-info">
                            <span class="val"><?php echo number_format($en_stock); ?></span>
                            <span class="lbl">En Stock</span>
                        </div>
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-mini orange">
                        <div class="stat-mini-info">
                            <span class="val"><?php echo number_format($stock_bajo); ?></span>
                            <span class="lbl">Stock Bajo</span>
                        </div>
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-mini cyan">
                        <div class="stat-mini-info">
                            <span class="val"><?php echo number_format($total_categorias); ?></span>
                            <span class="lbl">Categorías</span>
                        </div>
                        <i class="fas fa-tags"></i>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-card">
                    <form method="GET">
                        <div class="filter-grid"
                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                            <div class="filter-group">
                                <label
                                    style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Buscar
                                    Producto</label>
                                <div class="input-with-icon" style="position: relative;">
                                    <i class="fas fa-search"
                                        style="position: absolute; left: 12px; top: 12px; color: #94a3b8;"></i>
                                    <input type="text" name="q" class="form-control" style="padding-left: 35px;"
                                        placeholder="Nombre, código..." value="<?php echo htmlspecialchars($q); ?>">
                                </div>
                            </div>
                            <div class="filter-group">
                                <label
                                    style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Categoría</label>
                                <select name="categoria" class="form-control">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo $cat_filter == $c['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label
                                    style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">Laboratorio</label>
                                <select name="laboratorio" class="form-control">
                                    <option value="">Todos</option>
                                    <?php foreach ($laboratorios as $l): ?>
                                        <option value="<?php echo $l['id']; ?>" <?php echo $lab_filter == $l['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($l['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="filter-actions" style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                            <a href="index.php" class="btn btn-light" style="text-decoration: none;"><i
                                    class="fas fa-sync"></i> Limpiar</a>
                        </div>
                    </form>
                </div>

                <div class="results-header"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 0.9rem; color: #64748b;">
                    <span>Mostrando <?php echo count($productos); ?> productos encontrados</span>
                </div>

                <!-- Product Grid -->
                <div class="product-cards-container">
                    <?php if (empty($productos)): ?>
                        <div
                            style="grid-column: 1/-1; text-align: center; padding: 50px; background: white; border-radius: 12px; border: 1px dashed #e2e8f0; color: #64748b;">
                            <i class="fas fa-search"
                                style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.2;"></i>
                            <p>No se encontraron productos con los criterios seleccionados.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($productos as $p): ?>
                            <?php
                            $status_class = 'in-stock';
                            $status_text = 'Disponible';
                            if ($p['stock'] <= 0) {
                                $status_class = 'no-stock';
                                $status_text = 'Sin Stock';
                            } elseif ($p['stock'] <= $p['stockMinimo']) {
                                $status_class = 'low-stock';
                                $status_text = 'Poco Stock';
                            }
                            ?>
                            <div class="product-item-card">
                                <div class="card-status-badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </div>
                                <div class="card-img"
                                    style="height: 120px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #cbd5e1;">
                                    <i class="fas fa-pills"></i>
                                </div>
                                <div class="card-body" style="padding: 15px;">
                                    <h3 class="product-title"
                                        style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 5px; height: 2.4em; overflow: hidden;"
                                        title="<?php echo htmlspecialchars($p['nombre']); ?>">
                                        <?php echo htmlspecialchars($p['nombre']); ?>
                                    </h3>
                                    <span class="product-sku"
                                        style="display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 10px;">
                                        <i class="fas fa-barcode"></i> <?php echo htmlspecialchars($p['codigoPrincipal']); ?>
                                    </span>
                                    <div class="product-price-row" style="margin-bottom: 12px;">
                                        <span class="price"
                                            style="font-size: 1.25rem; font-weight: 800; color: #4f46e5;">$<?php echo number_format($p['precioVenta'], 2); ?></span>
                                    </div>
                                    <div class="product-tags"
                                        style="display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 15px;">
                                        <span class="tag blue"
                                            style="background: #eef2ff; color: #4f46e5; padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 700;">
                                            <?php echo htmlspecialchars($p['categoria_nombre'] ?? 'GENERAL'); ?>
                                        </span>
                                    </div>
                                    <div class="product-extra-info"
                                        style="font-size: 0.8rem; color: #64748b; display: flex; flex-direction: column; gap: 5px;">
                                        <span><i class="fas fa-warehouse"></i> Stock: <b
                                                style="color: #1e293b;"><?php echo number_format($p['stock'], 0); ?></b></span>
                                        <span title="<?php echo htmlspecialchars($p['laboratorio_nombre'] ?? 'N/A'); ?>"
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <i class="fas fa-flask"></i>
                                            <?php echo htmlspecialchars($p['laboratorio_nombre'] ?? 'SIN LABORATORIO'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer-actions"
                                    style="padding: 10px 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-around; background: #fcfdfe;">
                                    <a href="../inventario/kardex.php?search=<?php echo urlencode($p['codigoPrincipal']); ?>"
                                        class="btn-action blue" title="Ver Kardex"><i class="fas fa-history"></i></a>
                                    <button class="btn-action green" title="Vender"><i
                                            class="fas fa-shopping-cart"></i></button>
                                    <a href="nuevo.php?id=<?php echo $p['id']; ?>" class="btn-action cyan" title="Editar"><i
                                            class="fas fa-edit"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($total_productos > $limit): ?>
                    <div class="pagination-container">
                        <!-- Simplified pagination -->
                        <?php echo "Página $page"; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>