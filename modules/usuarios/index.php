<?php
/**
 * User Management - Gestión de Usuarios
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'usuarios_index';

// Fetch users from both 'usuarios' (new system) and 'auth_user' (original system)
$stmt = $pdo->query("
    SELECT id, nombreUsuario, nombreCompleto, email, activo, creadoDate, 'Sistema' as origen FROM usuarios WHERE anulado = 0
    UNION
    SELECT id, username as nombreUsuario, CONCAT(first_name, ' ', last_name) as nombreCompleto, email, is_active as activo, last_login as creadoDate, 'Legacy' as origen FROM auth_user
    ORDER BY creadoDate DESC
");
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .u-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .u-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .u-filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: 2fr 1.5fr 1.5fr 120px;
            gap: 15px;
            align-items: flex-end;
            border: 1px solid #f1f5f9;
        }

        .u-filters label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }

        .u-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .u-table {
            width: 100%;
            border-collapse: collapse;
        }

        .u-table th {
            background: #f8fafc;
            color: #475569;
            text-align: left;
            padding: 12px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #f1f5f9;
        }

        .u-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #334155;
            vertical-align: middle;
        }

        .user-info-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
        }

        .badge-role {
            background: #22d3ee;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .badge-status-u {
            background: #059669;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .u-actions {
            display: flex;
            gap: 6px;
        }

        .btn-u-act {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            background: white;
            transition: all 0.2s;
        }

        .btn-u-act.view {
            color: #0891b2;
            border-color: #0891b2;
        }

        .btn-u-act.edit {
            color: #2563eb;
            border-color: #2563eb;
        }

        .btn-u-act.pause {
            color: #f59e0b;
            border-color: #f59e0b;
        }

        .btn-u-act.delete {
            color: #dc2626;
            border-color: #dc2626;
        }

        .btn-u-act:hover {
            filter: brightness(0.9);
            transform: scale(1.05);
        }

        /* Responsive Improvements */
        @media (max-width: 992px) {
            .u-filters {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .u-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .u-header a {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .u-filters {
                grid-template-columns: 1fr;
            }

            /* Card-based table on mobile */
            .u-table thead {
                display: none;
            }

            .u-table,
            .u-table tbody,
            .u-table tr,
            .u-table td {
                display: block;
                width: 100%;
            }

            .u-table tr {
                margin-bottom: 15px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 10px;
                background: #f8fafc;
            }

            .u-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                padding: 10px 5px;
                border-bottom: 1px solid #edf2f7;
            }

            .u-table td:last-child {
                border-bottom: none;
            }

            .u-table td::before {
                content: attr(data-label);
                font-weight: 700;
                font-size: 0.7rem;
                color: #64748b;
                text-transform: uppercase;
                text-align: left;
                margin-right: 10px;
            }

            .user-info-cell {
                justify-content: flex-end;
            }

            .u-actions {
                justify-content: flex-end;
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
                <div class="u-header">
                    <h1><i class="fas fa-users-cog"></i> Gestión de Usuarios</h1>
                    <a href="nuevo.php" class="btn btn-primary"
                        style="padding: 10px 20px; border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </a>
                </div>

                <div class="u-filters">
                    <div>
                        <label>Buscar</label>
                        <input type="text" class="form-control" placeholder="Nombre, usuario o email...">
                    </div>
                    <div>
                        <label>Rol</label>
                        <select class="form-control">
                            <option>Todos los roles</option>
                        </select>
                    </div>
                    <div>
                        <label>Estado</label>
                        <select class="form-control">
                            <option>Todos los estados</option>
                            <option>Activo</option>
                            <option>Inactivo</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" style="height: 42px;"><i class="fas fa-search"></i> Buscar</button>
                </div>

                <div class="u-table-container" style="background: transparent; border: none; box-shadow: none;">
                    <table class="u-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td data-label="Usuario">
                                        <div class="user-info-cell">
                                            <div class="user-avatar"
                                                style="background: <?php echo $u['origen'] == 'Legacy' ? '#94a3b8' : ''; ?>;">
                                                <?php echo strtoupper(substr($u['nombreUsuario'], 0, 1)); ?>
                                            </div>
                                            <strong>
                                                <?php echo htmlspecialchars($u['nombreUsuario']); ?>
                                            </strong>
                                        </div>
                                    </td>
                                    <td data-label="Nombre Completo">
                                        <?php echo htmlspecialchars($u['nombreCompleto'] ?: 'Sin nombre'); ?>
                                    </td>
                                    <td data-label="Email">
                                        <?php echo htmlspecialchars($u['email']); ?>
                                    </td>
                                    <td data-label="Rol">
                                        <span class="badge-role"
                                            style="background: <?php echo $u['origen'] == 'Legacy' ? '#64748b' : ''; ?>;">
                                            <?php echo $u['origen'] == 'Legacy' ? 'Legacy (Django)' : 'Usuario'; ?>
                                        </span>
                                    </td>
                                    <td data-label="Estado"><span class="badge-status-u"
                                            style="background: <?php echo $u['activo'] ? '#059669' : '#94a3b8'; ?>;">
                                            <?php echo $u['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span></td>
                                    <td data-label="Creado" style="font-size: 0.75rem; color: #64748b;">
                                        <?php echo $u['creadoDate'] ? date('d/m/Y H:i', strtotime($u['creadoDate'])) : 'N/A'; ?>
                                    </td>
                                    <td data-label="Acciones">
                                        <div class="u-actions">
                                            <a href="perfil.php?id=<?php echo $u['id']; ?>&orig=<?php echo $u['origen']; ?>"
                                                class="btn-u-act view"><i class="fas fa-eye"></i></a>
                                            <a href="editar.php?id=<?php echo $u['id']; ?>&orig=<?php echo $u['origen']; ?>"
                                                class="btn-u-act edit"><i class="fas fa-edit"></i></a>
                                            <button class="btn-u-act pause"><i class="fas fa-pause"></i></button>
                                            <button class="btn-u-act delete"><i class="fas fa-ban"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>