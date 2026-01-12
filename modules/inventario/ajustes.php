<?php
/**
 * Inventory Adjustments List - Ajustes de Inventario
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'inventario_ajustes';

// Mock data to match screenshot
$ajustes = [
    ['fecha' => '14/12/2025 16:40', 'producto' => 'AGUA ALL NATURAL S-GASX1LT (7861075200060)', 'tipo' => 'Entrada', 'tipo_class' => 'badge-entrance', 'cant_ant' => '2', 'cant_ajust' => '3', 'dif' => '+ 1', 'motivo' => 'inventario_fisico', 'usuario' => 'Usuario Administrador', 'usr_name' => 'usr_1'],
    ['fecha' => '14/12/2025 16:34', 'producto' => 'AGUA ALL NATURAL S-GASX1LT (7861075200060)', 'tipo' => 'Corrección', 'tipo_class' => 'badge-correction', 'cant_ant' => '2', 'cant_ajust' => '2', 'dif' => '0', 'motivo' => 'correccion_costo', 'usuario' => 'Usuario Administrador', 'usr_name' => 'usr_1'],
    ['fecha' => '14/12/2025 16:22', 'producto' => 'AGUA ALL NATURAL S-GASX1LT (7861075200060)', 'tipo' => 'Corrección', 'tipo_class' => 'badge-correction', 'cant_ant' => '2', 'cant_ajust' => '2', 'dif' => '0', 'motivo' => 'correccion_costo', 'usuario' => 'Usuario Administrador', 'usr_name' => 'usr_1'],
    ['fecha' => '14/12/2025 15:51', 'producto' => 'AGUA ALL NATURAL S-GASX1LT (7861075200060)', 'tipo' => 'Corrección', 'tipo_class' => 'badge-correction', 'cant_ant' => '2', 'cant_ajust' => '2', 'dif' => '0', 'motivo' => 'correccion_costo', 'usuario' => 'Usuario Administrador', 'usr_name' => 'usr_1'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustes de Inventario | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .ajustes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .ajustes-title h1 {
            font-size: 1.25rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filters-grid-ajustes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .filters-grid-ajustes label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .summary-cards-ajustes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .aj-card {
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .aj-card .info h3 {
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 5px;
            opacity: 0.9;
        }

        .aj-card .info .value {
            font-size: 1.4rem;
            font-weight: 800;
        }

        .aj-card .icon {
            font-size: 1.8rem;
            opacity: 0.3;
        }

        .aj-blue {
            background: #007bff;
        }

        .aj-green {
            background: #198754;
        }

        .aj-red {
            background: #dc3545;
        }

        .aj-yellow {
            background: #ffc107;
        }

        .aj-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .aj-table th {
            background: #212529;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .aj-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.8rem;
            color: #1e293b;
        }

        .aj-table tr:hover {
            background: #f8fafc;
        }

        .badge- entrance,
        .badge-correction {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-entrance {
            background: #198754;
        }

        .badge-correction {
            background: #ffc107;
        }

        .qty-badge {
            background: #6c757d;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
        }

        .qty-badge-blue {
            background: #0dcaf0;
        }

        .btn-view {
            color: #0d6efd;
            background: transparent;
            border: 1px solid #0d6efd;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
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
                <div class="ajustes-header">
                    <div class="ajustes-title">
                        <h1><i class="fas fa-balance-scale"></i> Ajustes de Inventario</h1>
                    </div>
                    <a href="nuevo_ajuste.php" class="btn btn-primary"
                        style="padding: 10px 20px; border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-plus"></i> Nuevo Ajuste
                    </a>
                </div>

                <div class="filters-grid-ajustes">
                    <div>
                        <label>Fecha Inicio</label>
                        <input type="date" class="form-control">
                    </div>
                    <div>
                        <label>Fecha Fin</label>
                        <input type="date" class="form-control">
                    </div>
                    <div>
                        <label>Tipo de Ajuste</label>
                        <select class="form-control">
                            <option>Todos los tipos</option>
                            <option>Entrada</option>
                            <option>Salida</option>
                            <option>Corrección</option>
                        </select>
                    </div>
                    <div>
                        <label>Usuario</label>
                        <select class="form-control">
                            <option>Todos los usuarios</option>
                            <option>Usuario Administrador</option>
                        </select>
                    </div>
                </div>

                <div class="summary-cards-ajustes">
                    <div class="aj-card aj-blue">
                        <div class="info">
                            <h3>Total Ajustes</h3>
                            <div class="value">4</div>
                        </div>
                        <i class="fas fa-balance-scale icon"></i>
                    </div>
                    <div class="aj-card aj-green">
                        <div class="info">
                            <h3>Entradas</h3>
                            <div class="value">1</div>
                        </div>
                        <i class="fas fa-arrow-up icon"></i>
                    </div>
                    <div class="aj-card aj-red">
                        <div class="info">
                            <h3>Salidas</h3>
                            <div class="value">0</div>
                        </div>
                        <i class="fas fa-arrow-down icon"></i>
                    </div>
                    <div class="aj-card aj-yellow">
                        <div class="info">
                            <h3>Correcciones</h3>
                            <div class="value">3</div>
                        </div>
                        <i class="fas fa-edit icon"></i>
                    </div>
                </div>

                <table class="aj-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th style="text-align: center;">Cantidad Anterior</th>
                            <th style="text-align: center;">Cantidad Ajustada</th>
                            <th style="text-align: center;">Diferencia</th>
                            <th>Motivo</th>
                            <th>Usuario</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ajustes as $a): ?>
                            <tr>
                                <td style="font-size: 0.7rem;">
                                    <?php echo $a['fecha']; ?>
                                </td>
                                <td style="font-weight: 700;">
                                    <?php echo $a['producto']; ?>
                                </td>
                                <td>
                                    <span class="<?php echo $a['tipo_class']; ?>">
                                        <i
                                            class="fas <?php echo ($a['tipo'] == 'Entrada' ? 'fa-arrow-up' : 'fa-edit'); ?>"></i>
                                        <?php echo $a['tipo']; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;"><span class="qty-badge">
                                        <?php echo $a['cant_ant']; ?>
                                    </span></td>
                                <td style="text-align: center;"><span class="qty-badge qty-badge-blue">
                                        <?php echo $a['cant_ajust']; ?>
                                    </span></td>
                                <td style="text-align: center; color: #198754; font-weight: 800;">
                                    <?php echo $a['dif']; ?>
                                </td>
                                <td style="color: #64748b;">
                                    <?php echo $a['motivo']; ?>
                                </td>
                                <td style="font-size: 0.75rem;">
                                    <?php echo $a['usuario']; ?><br>
                                    <span style="color: #94a3b8;">
                                        <?php echo $a['usr_name']; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="ver_ajuste.php?id=123" class="btn-view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>