<?php
/**
 * Create/Edit Role - Crear/Editar Rol
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'usuarios_roles';
$is_edit = isset($_GET['nombre']);
$role_name = $is_edit ? $_GET['nombre'] : "";
$role_desc = $is_edit ? "Acceso total al sistema" : "";

$modules = [
    [
        'id' => 'prod',
        'name' => 'Productos',
        'icon' => 'fas fa-box',
        'perms' => 4,
        'open' => true,
        'actions' => [
            ['name' => 'Ver lista de productos', 'checked' => true],
            ['name' => 'Crear productos', 'checked' => true],
            ['name' => 'Editar productos', 'checked' => true],
            ['name' => 'Eliminar productos', 'checked' => true],
        ]
    ],
    ['id' => 'vent', 'name' => 'Ventas', 'icon' => 'fas fa-shopping-cart', 'perms' => 3, 'open' => false, 'actions' => []],
    ['id' => 'caja', 'name' => 'Caja', 'icon' => 'fas fa-cash-register', 'perms' => 3, 'open' => false, 'actions' => []],
    ['id' => 'clie', 'name' => 'Clientes', 'icon' => 'fas fa-users', 'perms' => 3, 'open' => false, 'actions' => []],
    ['id' => 'inv', 'name' => 'Inventario', 'icon' => 'fas fa-warehouse', 'perms' => 5, 'open' => false, 'actions' => []],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $is_edit ? 'Editar Rol' : 'Crear Nuevo Rol'; ?> | Warehouse POS
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .nr-header {
            background: #2563eb;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nr-card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
            margin-bottom: 30px;
        }

        .nr-body {
            padding: 30px;
        }

        .nr-grid-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-group-nr label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .form-group-nr label span {
            color: #dc2626;
        }

        .form-group-nr input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .form-group-nr .hint {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 5px;
        }

        .nr-perms-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nr-perms-hint {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 25px;
        }

        /* Permissions Accordion */
        .module-section {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .module-header {
            background: #f8fafc;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.2s;
        }

        .module-header:hover {
            background: #f1f5f9;
        }

        .module-header.active {
            background: #dbeafe;
        }

        .module-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
        }

        .module-title i {
            color: #475569;
        }

        .module-badge {
            background: #64748b;
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .module-content {
            padding: 10px 0;
            display: none;
            background: white;
        }

        .module-content.active {
            display: block;
        }

        .perm-row {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            border-bottom: 1px solid #f8fafc;
            transition: background 0.2s;
        }

        .perm-row:hover {
            background: #f8fafc;
        }

        .perm-row:last-child {
            border-bottom: none;
        }

        .perm-main {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }

        .perm-actions {
            display: flex;
            gap: 20px;
        }

        .action-check {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
        }

        .action-check input {
            width: 14px;
            height: 14px;
        }

        .nr-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
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
                <div class="nr-card">
                    <div class="nr-header">
                        <i class="fas <?php echo $is_edit ? 'fa-user-edit' : 'fa-plus-circle'; ?>"></i>
                        <?php echo $is_edit ? "Editar Rol: " . $role_name : "Crear Nuevo Rol"; ?>
                    </div>
                    <div class="nr-body">
                        <div class="nr-grid-info">
                            <div class="form-group-nr">
                                <label>Nombre del Rol <span>*</span></label>
                                <input type="text" value="<?php echo $role_name; ?>"
                                    placeholder="Ej: Farmacéutico, Cajero, Vendedor">
                                <p class="hint">Ej: Farmacéutico, Cajero, Vendedor</p>
                            </div>
                            <div class="form-group-nr">
                                <label>Descripción</label>
                                <input type="text" value="<?php echo $role_desc; ?>"
                                    placeholder="Breve descripción de las responsabilidades">
                                <p class="hint">Breve descripción de las responsabilidades</p>
                            </div>
                        </div>

                        <div class="nr-perms-title">
                            <i class="fas fa-lock"></i> Permisos del Rol
                        </div>
                        <p class="nr-perms-hint">Seleccione los módulos y acciones que podrá realizar este rol</p>

                        <div class="nr-accordion">
                            <?php foreach ($modules as $m): ?>
                                <div class="module-section">
                                    <div class="module-header <?php echo $m['open'] ? 'active' : ''; ?>"
                                        onclick="toggleModule('<?php echo $m['id']; ?>')">
                                        <div class="module-title">
                                            <i class="<?php echo $m['icon']; ?>"></i>
                                            <span>
                                                <?php echo $m['name']; ?>
                                            </span>
                                            <span class="module-badge">
                                                <?php echo $m['perms']; ?>
                                                <?php echo $m['perms'] == 1 ? 'permiso' : 'permisos'; ?>
                                            </span>
                                        </div>
                                        <i class="fas fa-chevron-<?php echo $m['open'] ? 'up' : 'down'; ?> arrow-icon"
                                            id="arrow-<?php echo $m['id']; ?>"></i>
                                    </div>
                                    <div class="module-content <?php echo $m['open'] ? 'active' : ''; ?>"
                                        id="content-<?php echo $m['id']; ?>">
                                        <?php if (!empty($m['actions'])): ?>
                                            <?php foreach ($m['actions'] as $act): ?>
                                                <div class="perm-row">
                                                    <div class="perm-main">
                                                        <input type="checkbox" <?php echo ($is_edit && $act['checked']) ? 'checked' : ''; ?> style="width: 16px; height: 16px;">
                                                        <span>
                                                            <?php echo $act['name']; ?>
                                                        </span>
                                                    </div>
                                                    <div class="perm-actions">
                                                        <label class="action-check"><input type="checkbox" <?php echo ($is_edit && $act['checked']) ? 'checked' : ''; ?>> Ver</label>
                                                        <label class="action-check"><input type="checkbox" <?php echo ($is_edit && $act['checked']) ? 'checked' : ''; ?>> Crear</label>
                                                        <label class="action-check"><input type="checkbox" <?php echo ($is_edit && $act['checked']) ? 'checked' : ''; ?>> Editar</label>
                                                        <label class="action-check"><input type="checkbox" <?php echo ($is_edit && $act['checked']) ? 'checked' : ''; ?>> Eliminar</label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div style="padding: 20px; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                                                No hay permisos específicos cargados para este módulo
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="nr-footer">
                            <a href="roles.php" class="btn btn-secondary" style="padding: 10px 25px;">Cancelar</a>
                            <button class="btn btn-primary" style="padding: 10px 30px; background: #2563eb;">
                                <i class="fas fa-save"></i>
                                <?php echo $is_edit ? 'Actualizar Rol' : 'Crear Rol'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        function toggleModule(id) {
            const header = event.currentTarget;
            const content = document.getElementById('content-' + id);
            const arrow = document.getElementById('arrow-' + id);

            const isActive = content.classList.contains('active');

            // Toggle current
            header.classList.toggle('active');
            content.classList.toggle('active');

            if (isActive) {
                arrow.classList.replace('fa-chevron-up', 'fa-chevron-down');
            } else {
                arrow.classList.replace('fa-chevron-down', 'fa-chevron-up');
            }
        }
    </script>
</body>

</html>