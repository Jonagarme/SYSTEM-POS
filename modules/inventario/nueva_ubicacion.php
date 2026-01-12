<?php
/**
 * New Location Form - Nueva Ubicación
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_ubicaciones';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Ubicación | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .nu-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .nu-title h1 {
            font-size: 1.4rem;
            color: #1e293b;
            margin: 0 0 5px 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .breadcrumb {
            display: flex;
            gap: 5px;
            font-size: 0.75rem;
            color: #64748b;
        }

        .breadcrumb a {
            color: #2563eb;
        }

        .btn-back {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nu-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            align-items: start;
        }

        .nu-panel {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin-bottom: 20px;
        }

        .nu-panel-header {
            background: #6366f1;
            color: white;
            padding: 12px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nu-panel-body {
            padding: 25px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group-nu {
            margin-bottom: 15px;
        }

        .form-group-nu label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .form-group-nu p {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 5px;
        }

        .preview-card {
            background: white;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            border: 2px dashed #e2e8f0;
            margin-bottom: 20px;
        }

        .preview-card i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 15px;
            display: block;
        }

        .preview-card h4 {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 10px;
        }

        .preview-card p {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .info-card-nu {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .info-card-header {
            background: #6366f1;
            color: white;
            padding: 10px 15px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card-body {
            padding: 15px;
        }

        .info-card-body h5 {
            font-size: 0.8rem;
            color: #1e293b;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tips-list {
            padding-left: 20px;
            margin-bottom: 15px;
        }

        .tips-list li {
            font-size: 0.75rem;
            color: #475569;
            margin-bottom: 8px;
        }

        .types-legend {
            font-size: 0.75rem;
            color: #475569;
        }

        .types-legend li {
            margin-bottom: 5px;
            list-style: none;
        }

        .nu-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-save-nu {
            background: #6366f1;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .btn-cancel-nu {
            background: #64748b;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
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
                <div class="nu-header">
                    <div class="nu-title">
                        <h1><i class="fas fa-map-marker-alt"></i> Nueva Ubicación</h1>
                        <div class="breadcrumb">
                            <a href="../../index.php">Dashboard</a> / <a href="transferencias.php">Inventario</a> / <a
                                href="ubicaciones.php">Ubicaciones</a> / <span>Nueva Ubicación</span>
                        </div>
                    </div>
                    <a href="ubicaciones.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver a Ubicaciones
                    </a>
                </div>

                <div class="nu-grid">
                    <div class="nu-left">
                        <div class="nu-panel">
                            <div class="nu-panel-header">
                                <i class="fas fa-plus"></i> Información de la Ubicación
                            </div>
                            <div class="nu-panel-body">
                                <div class="form-grid-2">
                                    <div class="form-group-nu">
                                        <label>Nombre de la Ubicación *</label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-hospital" style="left: 10px;"></i>
                                            <input type="text" class="form-control"
                                                placeholder="Ej: Sucursal Centro, Bodega Principal"
                                                style="padding-left: 35px;">
                                        </div>
                                        <p>Nombre descriptivo de la ubicación</p>
                                    </div>
                                    <div class="form-group-nu">
                                        <label>Código de Ubicación *</label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-barcode" style="left: 10px;"></i>
                                            <input type="text" class="form-control" placeholder="Ej: SUC001, BOD001"
                                                style="padding-left: 35px;">
                                        </div>
                                        <p>Código único de identificación</p>
                                    </div>
                                </div>

                                <div class="form-grid-2">
                                    <div class="form-group-nu">
                                        <label>Tipo de Ubicación *</label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-tag" style="left: 10px;"></i>
                                            <select class="form-control" style="padding-left: 35px;">
                                                <option>Seleccione un tipo...</option>
                                                <option>Sucursal</option>
                                                <option>Bodega</option>
                                                <option>Almacén</option>
                                                <option>Establecimiento</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group-nu">
                                        <label>Estado</label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-toggle-on" style="left: 10px;"></i>
                                            <select class="form-control" style="padding-left: 35px;">
                                                <option>Activo</option>
                                                <option>Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-nu">
                                    <label>Dirección</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-map-marker-alt" style="left: 10px;"></i>
                                        <input type="text" class="form-control"
                                            placeholder="Dirección completa de la ubicación"
                                            style="padding-left: 35px;">
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                                    <div class="form-group-nu">
                                        <label>Teléfono</label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-phone" style="left: 10px;"></i>
                                            <input type="text" class="form-control" placeholder="Ej: +504 2234-5678"
                                                style="padding-left: 35px;">
                                        </div>
                                    </div>
                                    <div class="form-group-nu">
                                        <label>Email</label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-envelope" style="left: 10px;"></i>
                                            <input type="email" class="form-control" placeholder="email@ejemplo.com"
                                                style="padding-left: 35px;">
                                        </div>
                                    </div>
                                    <div class="form-group-nu">
                                        <label>Capacidad (m²)</label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-expand-arrows-alt" style="left: 10px;"></i>
                                            <input type="text" class="form-control" placeholder="Ej: 150.50"
                                                style="padding-left: 35px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-nu">
                                    <label>Descripción</label>
                                    <textarea class="form-control" rows="4"
                                        placeholder="Descripción adicional de la ubicación, características especiales, etc."></textarea>
                                    <div style="text-align: right; font-size: 0.7rem; color: #94a3b8; margin-top: 5px;">
                                        Máximo 500 caracteres</div>
                                </div>

                                <div class="nu-footer">
                                    <a href="ubicaciones.php" class="btn-cancel-nu">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button class="btn-save-nu">
                                        <i class="fas fa-save"></i> Guardar Ubicación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="nu-right">
                        <div class="nu-panel">
                            <div class="nu-panel-header" style="background: #4f46e5;">
                                <i class="fas fa-eye"></i> Vista Previa
                            </div>
                            <div class="nu-panel-body">
                                <div class="preview-card">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <h4>Vista previa de la ubicación</h4>
                                    <p>Complete el formulario para ver una vista previa</p>
                                </div>
                            </div>
                        </div>

                        <div class="info-card-nu">
                            <div class="nu-panel-header">
                                <i class="fas fa-info-circle"></i> Información
                            </div>
                            <div class="info-card-body">
                                <h5><i class="fas fa-lightbulb" style="color: #fbbf24;"></i> Consejos:</h5>
                                <ul class="tips-list">
                                    <li>Use códigos únicos para cada ubicación</li>
                                    <li>Mantenga nombres descriptivos y cortos</li>
                                    <li>Complete la dirección para mejor control</li>
                                    <li>La capacidad ayuda en la planificación</li>
                                </ul>

                                <h5><i class="fas fa-info-circle" style="color: #0ea5e9;"></i> Tipos de Ubicación:</h5>
                                <ul class="types-legend">
                                    <li><strong>• Sucursal:</strong> Punto de venta</li>
                                    <li><strong>• Bodega:</strong> Almacenamiento principal</li>
                                    <li><strong>• Almacén:</strong> Depósito secundario</li>
                                    <li><strong>• Establecimiento:</strong> Punto de venta principal</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>