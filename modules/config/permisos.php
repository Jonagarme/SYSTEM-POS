<?php
/**
 * Role Permissions Management - Gestión de Roles y Permisos
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'config_permisos';

// Fetch all roles
$stmt = $pdo->query("SELECT * FROM roles WHERE anulado = 0 ORDER BY id ASC");
$roles = $stmt->fetchAll();

$selected_role_id = isset($_GET['role_id']) ? (int) $_GET['role_id'] : ($roles[0]['id'] ?? 0);

// Handle New Permission Creation
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_permission'])) {
    $mod = $_POST['new_modulo'] ?? '';
    $key = $_POST['new_key'] ?? '';
    $label = $_POST['new_label'] ?? '';

    try {
        $stmt = $pdo->prepare("INSERT INTO permisos_disponibles (modulo, permiso_key, etiqueta, creadoDate) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$mod, $key, $label]);
        $message = "Nuevo permiso '$label' creado correctamente.";

        // Log Audit
        require_once '../../includes/audit.php';
        registrarAuditoria('Configuración', 'CREAR', 'permisos_disponibles', $pdo->lastInsertId(), "Nuevo permiso registrado: $label ($key)");
    } catch (Exception $e) {
        $error = "Error al crear permiso: " . $e->getMessage();
    }
}

// Build permissions map from DB
$permissions_map = [];
try {
    $stmt = $pdo->query("SELECT * FROM permisos_disponibles ORDER BY modulo ASC, etiqueta ASC");
    while ($row = $stmt->fetch()) {
        $permissions_map[$row['modulo']][$row['permiso_key']] = $row['etiqueta'];
    }
} catch (Exception $e) {
    $error = "Error al cargar catálogo de permisos: " . $e->getMessage();
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_permissions'])) {
    $role_id = (int) $_POST['role_id'];

    try {
        $pdo->beginTransaction();

        // Clear existing permissions for this role
        $stmt = $pdo->prepare("DELETE FROM rol_permisos WHERE idRol = ?");
        $stmt->execute([$role_id]);

        // Insert new permissions
        $stmt = $pdo->prepare("INSERT INTO rol_permisos (idRol, modulo, permiso, puede_crear, puede_editar, puede_eliminar, puede_ver, creadoDate) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

        foreach ($permissions_map as $module_name => $perms) {
            foreach ($perms as $perm_key => $perm_label) {
                $ver = isset($_POST["perm_{$perm_key}_ver"]) ? 1 : 0;
                $crear = isset($_POST["perm_{$perm_key}_crear"]) ? 1 : 0;
                $editar = isset($_POST["perm_{$perm_key}_editar"]) ? 1 : 0;
                $eliminar = isset($_POST["perm_{$perm_key}_eliminar"]) ? 1 : 0;

                if ($ver || $crear || $editar || $eliminar) {
                    $stmt->execute([$role_id, strtolower($module_name), $perm_key, $crear, $editar, $eliminar, $ver]);
                }
            }
        }

        $pdo->commit();
        $message = "Permisos actualizados correctamente para el rol seleccionado.";

        // Log Audit
        require_once '../../includes/audit.php';
        // Get role name for the log
        $role_log_name = 'Desconocido';
        foreach ($roles as $r)
            if ($r['id'] == $role_id)
                $role_log_name = $r['nombre'];
        registrarAuditoria('Configuración', 'EDITAR', 'roles', $role_id, "Permisos actualizados para el rol: $role_log_name");
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al actualizar permisos: " . $e->getMessage();
    }
}

// Fetch current permissions for selected role
$current_perms = [];
if ($selected_role_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM rol_permisos WHERE idRol = ?");
        $stmt->execute([$selected_role_id]);
        while ($row = $stmt->fetch()) {
            $current_perms[$row['permiso']] = $row;
        }
    } catch (Exception $e) {
        $error = "Error al cargar permisos del rol: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles y Permisos | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .p-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            gap: 20px;
        }

        .p-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .p-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 25px;
            align-items: start;
        }

        .roles-list {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        .roles-list-header {
            padding: 15px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 700;
            font-size: 0.9rem;
            color: #475569;
        }

        .role-item {
            display: block;
            padding: 12px 20px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            border-bottom: 1px solid #f8fafc;
        }

        .role-item:hover {
            background: #f1f5f9;
            color: #2563eb;
        }

        .role-item.active {
            background: #eff6ff;
            color: #2563eb;
            border-left: 4px solid #2563eb;
        }

        .perms-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
            padding: 25px;
        }

        .module-group {
            margin-bottom: 30px;
        }

        .module-group-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1rem;
            color: #1e293b;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f1f5f9;
            flex-wrap: wrap;
        }

        .module-group-header i {
            color: #2563eb;
        }

        .perm-table {
            width: 100%;
            border-collapse: collapse;
        }

        .perm-table th {
            text-align: left;
            padding: 10px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            background: #f8fafc;
        }

        .perm-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .perm-name {
            font-weight: 600;
            color: #334155;
            width: 40%;
        }

        .checkbox-cell {
            text-align: center;
            width: 15%;
        }

        .checkbox-custom {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .p-footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 450px;
            max-width: 90%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #1e293b;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: #475569;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }

        .modal-footer {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Responsive Improvements */
        @media (max-width: 992px) {
            .p-layout {
                grid-template-columns: 1fr;
            }

            .roles-list {
                margin-bottom: 25px;
            }
        }

        @media (max-width: 768px) {
            .p-header {
                flex-direction: column;
                gap: 20px;
            }

            .p-header h1 {
                font-size: 1.25rem;
            }

            .p-header .btn-group {
                width: 100%;
                flex-direction: column;
            }

            .perm-table thead {
                display: none;
            }

            .perm-table tr {
                display: block;
                border: 1px solid #f1f5f9;
                border-radius: 12px;
                margin-bottom: 15px;
                padding: 10px;
            }

            .perm-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: none;
                padding: 8px 5px;
                width: 100% !important;
                text-align: right;
            }

            .perm-table td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #64748b;
                font-size: 0.75rem;
                text-transform: uppercase;
            }

            .checkbox-cell {
                justify-content: flex-end;
            }

            .p-footer {
                flex-direction: column-reverse;
            }

            .p-footer button {
                width: 100%;
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
                <div class="p-header">
                    <div class="p-header-title">
                        <h1><i class="fas fa-user-shield"></i> Roles y Permisos del Sistema</h1>
                        <p style="color: #64748b; margin-top: 5px;">Configura qué acciones puede realizar cada rol de
                            usuario en los diferentes módulos.</p>
                    </div>
                    <div class="p-header-actions" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="../usuarios/nuevo_rol.php" class="btn btn-secondary"
                            style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 0.85rem; padding: 10px 15px; text-decoration: none; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-plus-circle"></i> Nuevo Rol
                        </a>
                        <button type="button" class="btn btn-primary" onclick="openModal()"
                            style="background: #2563eb; color: white; border: none; font-size: 0.85rem; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-plus"></i> Registrar Permiso
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <div class="p-layout">
                    <!-- Roles Sidebar -->
                    <div class="roles-list">
                        <div class="roles-list-header">ROLES DISPONIBLES</div>
                        <?php foreach ($roles as $r): ?>
                            <a href="?role_id=<?php echo $r['id']; ?>"
                                class="role-item <?php echo $selected_role_id == $r['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($r['nombre']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Permissions Panel -->
                    <div class="perms-container">
                        <form action="" method="POST">
                            <input type="hidden" name="role_id" value="<?php echo $selected_role_id; ?>">

                            <?php
                            $role_info = null;
                            foreach ($roles as $r)
                                if ($r['id'] == $selected_role_id)
                                    $role_info = $r;
                            ?>

                            <div
                                style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px;">
                                <div>
                                    <h2 style="font-size: 1.25rem; color: #1e293b; margin-bottom: 5px;">
                                        Configurando: <span
                                            style="background: #eff6ff; color: #2563eb; padding: 2px 10px; border-radius: 6px;"><?php echo htmlspecialchars($role_info['nombre'] ?? 'Seleccione un rol'); ?></span>
                                    </h2>
                                    <p style="font-size: 0.85rem; color: #64748b;">
                                        <?php echo htmlspecialchars($role_info['descripcion'] ?? ''); ?>
                                    </p>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button type="button" class="btn btn-secondary" onclick="checkAllType('ver')"
                                        style="font-size: 0.75rem; padding: 5px 12px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                                        <i class="fas fa-eye"></i> Ver Todo
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="checkAllType('crear')"
                                        style="font-size: 0.75rem; padding: 5px 12px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                                        <i class="fas fa-plus"></i> Crear Todo
                                    </button>
                                </div>
                            </div>

                            <?php if ($selected_role_id): ?>
                                <?php foreach ($permissions_map as $module => $perms): ?>
                                    <div class="module-group"
                                        id="module-<?php echo strtolower(str_replace(' ', '', $module)); ?>">
                                        <div class="module-group-header">
                                            <i class="fas fa-folder-open"></i> <?php echo $module; ?>
                                            <div style="margin-left: auto; display: flex; gap: 8px;">
                                                <button type="button"
                                                    onclick="checkModule('<?php echo strtolower(str_replace(' ', '', $module)); ?>', true)"
                                                    style="font-size: 0.65rem; background: none; border: 1px solid #e2e8f0; border-radius: 4px; padding: 2px 6px; color: #64748b; cursor: pointer;">Seleccionar
                                                    Todo</button>
                                                <button type="button"
                                                    onclick="checkModule('<?php echo strtolower(str_replace(' ', '', $module)); ?>', false)"
                                                    style="font-size: 0.65rem; background: none; border: 1px solid #e2e8f0; border-radius: 4px; padding: 2px 6px; color: #64748b; cursor: pointer;">Limpiar</button>
                                            </div>
                                        </div>
                                        <table class="perm-table">
                                            <thead>
                                                <tr>
                                                    <th>Acción / Permiso</th>
                                                    <th class="checkbox-cell">Ver</th>
                                                    <th class="checkbox-cell">Crear</th>
                                                    <th class="checkbox-cell">Editar</th>
                                                    <th class="checkbox-cell">Eliminar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($perms as $key => $label):
                                                    $p = $current_perms[$key] ?? ['puede_ver' => 0, 'puede_crear' => 0, 'puede_editar' => 0, 'puede_eliminar' => 0];
                                                    ?>
                                                    <tr class="perm-row-hover">
                                                        <td data-label="Acción / Permiso" class="perm-name"><?php echo $label; ?>
                                                        </td>
                                                        <td data-label="Ver" class="checkbox-cell">
                                                            <input type="checkbox" name="perm_<?php echo $key; ?>_ver"
                                                                class="checkbox-custom cb-ver" <?php echo $p['puede_ver'] ? 'checked' : ''; ?>>
                                                        </td>
                                                        <td data-label="Crear" class="checkbox-cell">
                                                            <input type="checkbox" name="perm_<?php echo $key; ?>_crear"
                                                                class="checkbox-custom cb-crear" <?php echo $p['puede_crear'] ? 'checked' : ''; ?>>
                                                        </td>
                                                        <td data-label="Editar" class="checkbox-cell">
                                                            <input type="checkbox" name="perm_<?php echo $key; ?>_editar"
                                                                class="checkbox-custom cb-editar" <?php echo $p['puede_editar'] ? 'checked' : ''; ?>>
                                                        </td>
                                                        <td data-label="Eliminar" class="checkbox-cell">
                                                            <input type="checkbox" name="perm_<?php echo $key; ?>_eliminar"
                                                                class="checkbox-custom cb-eliminar" <?php echo $p['puede_eliminar'] ? 'checked' : ''; ?>>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endforeach; ?>

                                <div class="p-footer">
                                    <button type="submit" name="save_permissions" class="btn btn-primary"
                                        style="padding: 12px 40px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);">
                                        <i class="fas fa-save" style="margin-right: 10px;"></i> Guardar Cambios de Seguridad
                                    </button>
                                </div>
                            <?php else: ?>
                                <div style="text-align: center; padding: 50px; color: #94a3b8;">
                                    <i class="fas fa-user-tag fa-3x" style="margin-bottom: 15px; opacity: 0.5;"></i>
                                    <p>Seleccione un rol de la lista lateral para configurar sus permisos.</p>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nuevo Permiso -->
    <div class="modal-overlay" id="permModal">
        <div class="modal-container">
            <div class="modal-header">Registrar Nuevo Permiso</div>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Módulo</label>
                    <input type="text" name="new_modulo" placeholder="Ej: Farmacia, Inventario, Contabilidad" required>
                </div>
                <div class="form-group">
                    <label>Clave del Permiso (Key)</label>
                    <input type="text" name="new_key" placeholder="Ej: ver_recetas, anular_factura" required>
                </div>
                <div class="form-group">
                    <label>Etiqueta (Nombre visible)</label>
                    <input type="text" name="new_label" placeholder="Ej: Ver Recetas Médicas" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()"
                        style="padding: 10px 20px; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer;">Cancelar</button>
                    <button type="submit" name="create_permission" class="btn btn-primary"
                        style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer;">Guardar
                        Permiso</button>
                </div>
            </form>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        function openModal() { document.getElementById('permModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('permModal').style.display = 'none'; }

        function checkAllType(type) {
            const checkboxes = document.querySelectorAll('.cb-' + type);
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => cb.checked = anyUnchecked);
        }

        function checkModule(moduleId, check) {
            const container = document.getElementById('module-' + moduleId);
            if (container) {
                const checkboxes = container.querySelectorAll('.checkbox-custom');
                checkboxes.forEach(cb => cb.checked = check);
            }
        }
    </script>
</body>

</html>