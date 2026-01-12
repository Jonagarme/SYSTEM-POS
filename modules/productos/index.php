<?php
/**
 * Product Management - Advanced Grid View
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Stats queries
$total_productos = $pdo->query("SELECT COUNT(*) FROM productos WHERE anulado = 0")->fetchColumn();
$en_stock = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock > 0 AND anulado = 0")->fetchColumn();
$stock_bajo = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock <= stockMinimo AND stock > 0 AND anulado = 0")->fetchColumn();
$total_categorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();

// Fetch products for the grid (limited to 50 for performance in this view)
$stmt = $pdo->query("
    SELECT p.*, l.nombre as laboratorio_nombre, c.nombre as categoria_nombre 
    FROM productos p
    LEFT JOIN laboratorios l ON p.idLaboratorio = l.id
    LEFT JOIN categorias c ON p.idCategoria = c.id
    WHERE p.anulado = 0
    ORDER BY p.creadoDate DESC
    LIMIT 50
");
$productos = $stmt->fetchAll();
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
</head>

<body>
    <div class="app-container">
        <?php
        $root = '../../';
        $current_page = 'productos';
        include $root . 'includes/sidebar.php';
        ?>

        <main class="main-content">
            <?php include $root . 'includes/navbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <div>
                        <h1>Gestión de Productos</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../../index.php">Inicio</a></li>
                                <li class="breadcrumb-item active">Productos</li>
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
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Buscar Producto</label>
                            <div class="input-with-icon">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Nombre, código, descripción...">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Categoría</label>
                            <select>
                                <option>Todas</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Marca</label>
                            <select>
                                <option>Todas</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Laboratorio</label>
                            <select>
                                <option>Todos</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Vista</label>
                            <select>
                                <option>Tarjetas</option>
                                <option>Lista</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
                        <button class="btn btn-light btn-sm"><i class="fas fa-sync"></i> Limpiar</button>
                    </div>
                </div>

                <div class="results-header">
                    <span>Mostrando <?php echo count($productos); ?> productos de <?php echo $total_productos; ?></span>
                    <div class="view-toggle">
                        <button class="active"><i class="fas fa-th-large"></i></button>
                        <button><i class="fas fa-list"></i></button>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="product-cards-container">
                    <?php if (empty($productos)): ?>
                        <div
                            style="grid-column: 1/-1; text-align: center; padding: 50px; background: white; border-radius: 12px; color: #64748b;">
                            <i class="fas fa-search"
                                style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                            <p>No se encontraron productos para mostrar.</p>
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
                                <div class="card-img">
                                    <i class="fas fa-pills"></i>
                                </div>
                                <div class="card-body">
                                    <h3 class="product-title" title="<?php echo htmlspecialchars($p['nombre']); ?>">
                                        <?php echo htmlspecialchars($p['nombre']); ?>
                                    </h3>
                                    <span class="product-sku"><?php echo htmlspecialchars($p['codigoPrincipal']); ?></span>
                                    <div class="product-price-row">
                                        <span class="price">$
                                            <?php echo number_format($p['precioVenta'], 2, ',', '.'); ?></span>
                                    </div>
                                    <div class="product-tags">
                                        <span
                                            class="tag blue"><?php echo htmlspecialchars($p['categoria_nombre'] ?? 'GENERAL'); ?></span>
                                        <span
                                            class="tag gray"><?php echo $p['esPsicotropico'] ? 'PSICOTRÓPICO' : 'VENTA LIBRE'; ?></span>
                                    </div>
                                    <div class="product-extra-info">
                                        <span><i class="fas fa-warehouse"></i> Stock:
                                            <?php echo number_format($p['stock'], 0); ?></span>
                                        <span title="<?php echo htmlspecialchars($p['laboratorio_nombre'] ?? 'N/A'); ?>">
                                            <i class="fas fa-flask"></i>
                                            <?php echo htmlspecialchars($p['laboratorio_nombre'] ?? 'SIN LABORATORIO'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer-actions">
                                    <button class="btn-action blue" title="Historial"><i class="fas fa-history"></i></button>
                                    <button class="btn-action green" title="Kardex"><i
                                            class="fas fa-shopping-cart"></i></button>
                                    <button class="btn-action cyan" title="Editar"><i class="fas fa-edit"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>