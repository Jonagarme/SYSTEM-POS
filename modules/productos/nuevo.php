<?php
/**
 * Product Form - Create & Edit
 */
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto | Registro Profesional</title>
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
                    <h1>Crear Nuevo Producto</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../../index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Productos</a></li>
                            <li class="breadcrumb-item active">Nuevo</li>
                        </ol>
                    </nav>
                </div>
                <form action="save_product.php" method="POST">

                    <!-- Sección 1: Información Básica -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-info-circle"></i>
                            <h3>Información Básica</h3>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Código Principal *</label>
                                <input type="text" name="codigo" placeholder="Ej: MED001, FAR123" required>
                            </div>
                            <div class="form-group col-6">
                                <label>Código Auxiliar</label>
                                <input type="text" name="codigo_auxiliar" placeholder="Código alternativo o de barras">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-8">
                                <label>Nombre del Producto *</label>
                                <input type="text" name="nombre" placeholder="Nombre completo del producto" required>
                            </div>
                            <div class="form-group col-4">
                                <label>Registro Sanitario</label>
                                <input type="text" name="registro_sanitario" placeholder="RSA-XXX-XXXX">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-4">
                                <label><i class="fas fa-calendar-alt"></i> Fecha de Caducidad</label>
                                <input type="date" name="fecha_caducidad">
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
                                </select>
                            </div>
                            <div class="form-group col-4">
                                <label>Marca *</label>
                                <input type="text" name="marca" placeholder="Marca...">
                            </div>
                            <div class="form-group col-4">
                                <label>Laboratorio</label>
                                <input type="text" name="laboratorio" placeholder="Laboratorio...">
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
                                <div class="val danger">0.00%</div>
                            </div>
                            <div class="price-stat">
                                <label>Ganancia por Unidad</label>
                                <div class="val">$0.00</div>
                            </div>
                            <div class="price-stat">
                                <label>Valor Total Inventario</label>
                                <div class="val">$0.00</div>
                            </div>
                            <div class="price-stat">
                                <label>Estado Stock</label>
                                <div class="stock-badge danger">Bajo</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-3">
                                <label>Costo por Unidad *</label>
                                <div class="input-currency"><span>$</span><input type="number" name="precio_compra"
                                        step="0.0001" required></div>
                            </div>
                            <div class="form-group col-3">
                                <label>Costo por Caja</label>
                                <div class="input-currency"><span>$</span><input type="number" name="costo_caja"
                                        step="0.0001"></div>
                            </div>
                            <div class="form-group col-3">
                                <label>PVP Unidad</label>
                                <div class="input-currency"><span>$</span><input type="number" name="pvp_unidad"
                                        step="0.0001"></div>
                            </div>
                            <div class="form-group col-3">
                                <label>Precio de Venta *</label>
                                <div class="input-currency"><span>$</span><input type="number" name="precio_venta"
                                        step="0.0001" required></div>
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
                                <input type="number" name="stock_actual" value="0" step="0.01">
                            </div>
                            <div class="form-group col-3">
                                <label>Stock Mínimo</label>
                                <input type="number" name="stock_minimo" value="5" step="0.01">
                                <span class="hint">Alerta cuando el stock esté por debajo</span>
                            </div>
                            <div class="form-group col-3">
                                <label>Stock Máximo</label>
                                <input type="number" name="stock_maximo" step="0.01">
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
                                    <input type="checkbox" name="es_divisible">
                                    <span class="slider"></span>
                                </label>
                                <div class="switch-lbl">
                                    <strong>Es Divisible</strong>
                                    <span>Se puede vender en fracciones</span>
                                </div>
                            </div>
                            <div class="switch-item">
                                <label class="switch">
                                    <input type="checkbox" name="es_psicotropico">
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
                                    <input type="checkbox" name="cadena_frio">
                                    <span class="slider"></span>
                                </label>
                                <div class="switch-lbl">
                                    <strong><i class="fas fa-snowflake text-info"></i> Cadena de Frío</strong>
                                    <span>Requiere refrigeración</span>
                                </div>
                            </div>
                            <div class="switch-item active-stock">
                                <label class="switch">
                                    <input type="checkbox" name="estado" checked>
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
                        <button type="button" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear
                            Producto</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="../../assets/js/producto_form.js"></script>
</body>

</html>