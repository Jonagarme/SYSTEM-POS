<?php
/**
 * Product Form - Create & Edit
 */
session_start();
require_once '../../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;
$title = "Nuevo Producto";

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        $title = "Editar Producto: " . $product['nombre'];
    }
}

// Fetch categories, labs, and brands for selects
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
$marcas = $pdo->query("SELECT id, nombre FROM marcas WHERE anulado = 0 ORDER BY nombre ASC")->fetchAll();
$laboratorios = $pdo->query("SELECT id, nombre FROM laboratorios WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> | Registro Profesional</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/producto_form.css">
</head>

<body>
    <div class="app-container">
        <?php
        $root = '../../';
        $current_page = 'nuevo_producto';
        include $root . 'includes/sidebar.php';
        ?>

        <main class="main-content">
            <?php include $root . 'includes/navbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header" style="margin-bottom: 24px;">
                    <h1><?php echo $id > 0 ? 'Editar Producto' : 'Crear Nuevo Producto'; ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../../index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Productos</a></li>
                            <li class="breadcrumb-item active"><?php echo $id > 0 ? 'Editar' : 'Nuevo'; ?></li>
                        </ol>
                    </nav>
                </div>
                <form action="save_product.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">

                    <!-- Sección 1: Información Básica -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-info-circle"></i>
                            <h3>Información Básica</h3>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Código Principal *</label>
                                <input type="text" name="codigo"
                                    value="<?php echo htmlspecialchars($product['codigoPrincipal'] ?? ''); ?>"
                                    placeholder="Ej: MED001, FAR123" required>
                            </div>
                            <div class="form-group col-6">
                                <label>Código Auxiliar</label>
                                <input type="text" name="codigo_auxiliar"
                                    value="<?php echo htmlspecialchars($product['codigoAuxiliar'] ?? ''); ?>"
                                    placeholder="Código alternativo o de barras">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-8">
                                <label>Nombre del Producto *</label>
                                <input type="text" name="nombre"
                                    value="<?php echo htmlspecialchars($product['nombre'] ?? ''); ?>"
                                    placeholder="Nombre completo del producto" required>
                            </div>
                            <div class="form-group col-4">
                                <label>Registro Sanitario</label>
                                <input type="text" name="registro_sanitario"
                                    value="<?php echo htmlspecialchars($product['registroSanitario'] ?? ''); ?>"
                                    placeholder="RSA-XXX-XXXX">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-4">
                                <label><i class="fas fa-calendar-alt"></i> Fecha de Caducidad</label>
                                <input type="date" name="fecha_caducidad"
                                    value="<?php echo $product['fechaCaducidad'] ?? ''; ?>">
                                <span class="hint">Fecha de expiración (debe ser posterior a hoy)</span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Descripción</label>
                                <textarea name="descripcion" rows="3"
                                    placeholder="Descripción detallada del producto"></textarea>
                            </div>
                            <div class="form-group col-6">
                                <label>Observaciones</label>
                                <textarea name="observaciones" rows="3"
                                    placeholder="Notas adicionales, instrucciones especiales"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Clasificación -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-flask"></i>
                            <h3>Clasificación Farmacéutica</h3>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-4">
                                <label>Tipo de Producto *</label>
                                <select name="tipo_producto" required>
                                    <option value="">Seleccionar tipo...</option>
                                    <option>Medicamento</option>
                                    <option>Insumo Médico</option>
                                </select>
                            </div>
                            <div class="form-group col-4">
                                <label>Clase de Producto *</label>
                                <select name="clase_producto" required>
                                    <option value="">Seleccionar clase...</option>
                                    <option>Genérico</option>
                                    <option>Comercial</option>
                                </select>
                            </div>
                            <div class="form-group col-4">
                                <label>Clasificación ABC</label>
                                <select name="clasificacion_abc">
                                    <option>Sin clasificar</option>
                                    <option>A (Alta Rotación)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-4">
                                <label>Categoría *</label>
                                <select name="categoria_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($product['idCategoria']) && $product['idCategoria'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-4">
                                <label>Marca</label>
                                <select name="marca_id">
                                    <option value="">Seleccionar marca...</option>
                                    <?php foreach ($marcas as $m): ?>
                                        <option value="<?php echo $m['id']; ?>" <?php echo (isset($product['idMarca']) && $product['idMarca'] == $m['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class=" form-group col-4">
                                <label>Laboratorio</label>
                                <select name="laboratorio_id">
                                    <option value="">Seleccionar laboratorio...</option>
                                    <?php foreach ($laboratorios as $lab): ?>
                                        <option value="<?php echo $lab['id']; ?>" <?php echo (isset($product['idLaboratorio']) && $product['idLaboratorio'] == $lab['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($lab['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Precios y Costos -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-dollar-sign"></i>
                            <h3>Precios y Costos</h3>
                        </div>

                        <!-- Dashboard de Precios -->
                        <div class="price-dashboard">
                            <div class="price-stat">
                                <label>Margen de Ganancia</label>
                                <div class="val danger" id="display-margin">0.00%</div>
                            </div>
                            <div class="price-stat">
                                <label>Ganancia por Unidad</label>
                                <div class="val" id="display-profit">$0.00</div>
                            </div>
                            <div class="price-stat">
                                <label>Valor Total Inventario</label>
                                <div class="val" id="display-inventory">$0.00</div>
                            </div>
                            <div class="price-stat">
                                <label>Estado Stock</label>
                                <div class="stock-badge danger" id="display-stock-status">Bajo</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-3">
                                <label>Costo por Unidad *</label>
                                <div class="input-currency"><span>$</span><input type="number" name="precio_compra"
                                        step="0.0001"
                                        value="<?php echo number_format($product['costoUnidad'] ?? 0, 4, '.', ''); ?>"
                                        required></div>
                            </div>
                            <div class="form-group col-3">
                                <label>Costo por Caja</label>
                                <div class="input-currency"><span>$</span><input type="number" name="costo_caja"
                                        step="0.0001"
                                        value="<?php echo number_format($product['costoCaja'] ?? 0, 4, '.', ''); ?>">
                                </div>
                            </div>
                            <div class="form-group col-3">
                                <label>PVP Unidad</label>
                                <div class="input-currency"><span>$</span><input type="number" name="pvp_unidad"
                                        step="0.0001"
                                        value="<?php echo number_format($product['pvpUnidad'] ?? 0, 4, '.', ''); ?>">
                                </div>
                            </div>
                            <div class="form-group col-3">
                                <label>Precio de Venta *</label>
                                <div class="input-currency"><span>$</span><input type="number" name="precio_venta"
                                        step="0.0001"
                                        value="<?php echo number_format($product['precioVenta'] ?? 0, 4, '.', ''); ?>"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 4: Control de Inventario -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-warehouse"></i>
                            <h3>Control de Inventario</h3>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-3">
                                <label>Stock Actual *</label>
                                <input type="number" name="stock_actual"
                                    value="<?php echo number_format($product['stock'] ?? 0, 2, '.', ''); ?>" step="0.01"
                                    <?php echo $id > 0 ? 'readonly' : ''; ?>>
                                <?php if ($id > 0): ?><span class="hint">Usar ajustes para cambiar
                                        stock</span><?php endif; ?>
                            </div>
                            <div class="form-group col-3">
                                <label>Stock Mínimo</label>
                                <input type="number" name="stock_minimo"
                                    value="<?php echo number_format($product['stockMinimo'] ?? 5, 2, '.', ''); ?>"
                                    step="0.01">
                                <span class="hint">Alerta cuando el stock esté por debajo</span>
                            </div>
                            <div class="form-group col-3">
                                <label>Stock Máximo</label>
                                <input type="number" name="stock_maximo"
                                    value="<?php echo number_format($product['stockMaximo'] ?? 100, 2, '.', ''); ?>"
                                    step="0.01">
                                <span class="hint">Límite máximo de almacenamiento</span>
                            </div>
                            <div class="form-group col-3">
                                <label>Punto de Reorden</label>
                                <div class="reorder-info">
                                    <span class="val">0 unidades</span>
                                    <span class="hint">Basado en stock mínimo + margen</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 5: Características -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-microscope"></i>
                            <h3>Características Farmacéuticas</h3>
                        </div>
                        <div class="switches-grid">
                            <div class="switch-item">
                                <label class="switch">
                                    <input type="checkbox" name="es_divisible" <?php echo (isset($product['esDivisible']) && $product['esDivisible']) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <div class="switch-lbl">
                                    <strong>Es Divisible</strong>
                                    <span>Se puede vender en fracciones</span>
                                </div>
                            </div>
                            <div class="switch-item">
                                <label class="switch">
                                    <input type="checkbox" name="es_psicotropico" <?php echo (isset($product['esPsicotropico']) && $product['esPsicotropico']) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <div class="switch-lbl">
                                    <strong><i class="fas fa-exclamation-triangle text-danger"></i> Es
                                        Psicotrópico</strong>
                                    <span>Requiere control especial</span>
                                </div>
                            </div>
                            <div class="switch-item">
                                <label class="switch">
                                    <input type="checkbox" name="cadena_frio" <?php echo (isset($product['requiereCadenaFrio']) && $product['requiereCadenaFrio']) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <div class="switch-lbl">
                                    <strong><i class="fas fa-snowflake text-info"></i> Cadena de Frío</strong>
                                    <span>Requiere refrigeración</span>
                                </div>
                            </div>
                            <div class="switch-item">
                                <label class="switch">
                                    <input type="checkbox" name="maneja_lote" <?php echo (!isset($product['manejaLote']) || $product['manejaLote']) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <div class="switch-lbl">
                                    <strong>Maneja Lote</strong>
                                    <span>Control de inventario por lotes</span>
                                </div>
                            </div>
                            <div class="switch-item active-stock">
                                <label class="switch">
                                    <input type="checkbox" name="estado" <?php echo (!isset($product['activo']) || $product['activo']) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <div class="switch-lbl">
                                    <strong>Producto Activo</strong>
                                    <span>Disponible para venta</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-footer">
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>
                            <?php echo $id > 0 ? 'Guardar Cambios' : 'Crear Producto'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="../../assets/js/producto_form.js"></script>
</body>

</html>