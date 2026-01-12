<?php
/**
 * Location Management - Ubicaciones
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_ubicaciones';

// Mock locations
$ubicaciones = [
    ['id' => 'BOD-001', 'nombre' => 'Bodega Principal', 'direccion' => 'Ubicación principal del sistema', 'tel' => '0000000000', 'admin' => 'Administrador General', 'tipo' => 'Bodega', 'tipo_class' => 'type-bodega'],
    ['id' => 'SUC-001', 'nombre' => 'Sucursal Centro', 'direccion' => 'Centro de la ciudad', 'tel' => '0991234567', 'admin' => 'María González', 'tipo' => 'Sucursal', 'tipo_class' => 'type-sucursal'],
    ['id' => 'SUC-002', 'nombre' => 'Sucursal Norte', 'direccion' => 'Zona Norte', 'tel' => '0987654321', 'admin' => 'Carlos Ramírez', 'tipo' => 'Sucursal', 'tipo_class' => 'type-sucursal'],
];
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
        .ubicaciones-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
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
        }

        .summary-grid-ubic {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            max-width: 600px;
        }

        .u-card {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .u-card .info h3 {
            font-size: 1.6rem;
            font-weight: 800;
            margin: 0;
        }

        .u-card .info .label {
            font-size: 0.75rem;
            font-weight: 500;
            opacity: 0.9;
        }

        .u-card .icon {
            font-size: 1.8rem;
            opacity: 0.3;
        }

        .locations-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .loc-item-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
            position: relative;
        }

        .loc-id {
            font-size: 0.75rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 5px;
            display: block;
        }

        .loc-name {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            display: block;
        }

        .loc-info {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .loc-type-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 5px;
        }

        .badge-type {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            color: white;
        }

        .type-bodega {
            background: #198754;
        }

        .type-sucursal {
            background: #007bff;
        }

        .badge-principal {
            background: #ff7e00;
        }

        .loc-admin {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            color: #64748b;
        }

        .loc-actions {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .btn-loc-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
            background: white;
        }

        .btn-edit {
            color: #2563eb;
            border-color: #2563eb;
        }

        .btn-tree {
            color: #0ea5e9;
            border-color: #0ea5e9;
        }

        .btn-config {
            color: #198754;
            border-color: #198754;
        }

        .info-legend-panel {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            border: 1px solid #e2e8f0;
        }

        .legend-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .legend-list {
            list-style: none;
            padding: 0;
        }

        .legend-list li {
            font-size: 0.8rem;
            color: #475569;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-sucursal {
            background: #007bff;
        }

        .dot-bodega {
            background: #198754;
        }

        .dot-almacen {
            background: #ffc107;
        }

        .dot-deposito {
            background: #6c757d;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            font-size: 0.8rem;
            color: #475569;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feature-list i {
            color: #198754;
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
                        <h1>Ubicaciones</h1>
                        <div class="breadcrumb">
                            <a href="../../index.php">Dashboard</a> / <a href="../inventario/kardex.php">Inventario</a>
                            / <span>Ubicaciones</span>
                        </div>
                    </div>
                    <a href="nueva_ubicacion.php" class="btn btn-primary" style="padding: 10px 20px; font-weight: 600;">
                        <i class="fas fa-plus"></i> Nueva Ubicación
                    </a>
                </div>

                <div class="summary-grid-ubic">
                    <div class="u-card">
                        <div class="info">
                            <h3>3</h3>
                            <div class="label">Total Ubicaciones</div>
                        </div>
                        <i class="fas fa-map-marker-alt icon"></i>
                    </div>
                    <div class="u-card">
                        <div class="info">
                            <h3>2</h3>
                            <div class="label">Activas</div>
                        </div>
                        <i class="fas fa-check-circle icon"></i>
                    </div>
                </div>

                <div class="locations-grid">
                    <?php foreach ($ubicaciones as $u): ?>
                        <div class="loc-item-card">
                            <div class="loc-type-badge">
                                <span class="badge-type <?php echo $u['tipo_class']; ?>">
                                    <?php echo $u['tipo']; ?>
                                </span>
                                <?php if ($u['nombre'] == 'Bodega Principal')
                                    echo '<span class="badge-type badge-principal">Principal</span>'; ?>
                            </div>
                            <span class="loc-id">
                                <?php echo $u['id']; ?>
                            </span>
                            <span class="loc-name">
                                <?php echo $u['nombre']; ?>
                            </span>
                            <div class="loc-info">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo $u['direccion']; ?>
                            </div>
                            <div class="loc-info">
                                <i class="fas fa-phone"></i>
                                <?php echo $u['tel']; ?>
                            </div>
                            <div class="loc-admin">
                                <i class="fas fa-user"></i>
                                <?php echo $u['admin']; ?>
                            </div>
                            <div class="loc-actions">
                                <button class="btn-loc-action btn-edit"><i class="fas fa-edit"></i></button>
                                <button class="btn-loc-action btn-tree"><i class="fas fa-sitemap"></i></button>
                                <button class="btn-loc-action btn-config"><i class="fas fa-cog"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="info-legend-panel">
                    <div>
                        <div class="legend-title"><i class="fas fa-info-circle"></i> Información sobre Ubicaciones</div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #2563eb; margin-bottom: 10px;">Tipos de
                            Ubicaciones:</div>
                        <ul class="legend-list">
                            <li><span class="dot dot-sucursal"></span> <strong>Sucursal</strong> - Puntos de venta al
                                público</li>
                            <li><span class="dot dot-bodega"></span> <strong>Bodega</strong> - Almacenamiento principal
                            </li>
                            <li><span class="dot dot-almacen"></span> <strong>Almacén</strong> - Almacenamiento
                                secundario</li>
                            <li><span class="dot dot-deposito"></span> <strong>Depósito</strong> - Almacenamiento
                                temporal</li>
                        </ul>
                    </div>
                    <div>
                        <div class="legend-title" style="visibility: hidden;">Funcionalidades</div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #2563eb; margin-bottom: 10px;">
                            Funcionalidades:</div>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> Control de stock por ubicación</li>
                            <li><i class="fas fa-check"></i> Transferencias entre ubicaciones</li>
                            <li><i class="fas fa-check"></i> Órdenes de compra por ubicación</li>
                            <li><i class="fas fa-check"></i> Reportes de inventario por ubicación</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>