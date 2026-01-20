<?php
/**
 * Role Management - Gestión de Roles
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'usuarios_roles';

// Mock data
$roles = [
    ['nombre' => 'Administrador', 'desc' => 'Acceso total al sistema', 'users' => 4, 'perms' => 36, 'created' => '04/08/2025 19:20'],
    ['nombre' => 'Cajero', 'desc' => 'Acceso al punto de venta y cierre de caja', 'users' => 0, 'perms' => 8, 'created' => '04/08/2025 19:20'],
    ['nombre' => 'Farmacéutico', 'desc' => 'Acceso a inventario, compras y kardex', 'users' => 0, 'perms' => 15, 'created' => '04/08/2025 19:20'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .roles-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .roles-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .roles-search-container {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            border: 1px solid #f1f5f9;
        }

        .roles-search-container input {
            flex: 1;
            border: 1px solid #e2e8f0;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .btn-search-roles {
            background: white;
            border: 1px solid #2563eb;
            color: #2563eb;
            width: 40px;
            height: 40px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .roles-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .roles-table {
            width: 100%;
            border-collapse: collapse;
        }

        .roles-table th {
            text-align: left;
            padding: 12px 20px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }

        .roles-table td {
            padding: 15px 20px;
            font-size: 0.85rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge-users {
            background: #22d3ee;
            color: white;
            padding: 3px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .badge-perms {
            background: #059669;
            color: white;
            padding: 3px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .btn-edit-role {
            background: white;
            color: #2563eb;
            border: 1px solid #2563eb;
            padding: 5px 15px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            width: fit-content;
        }

        .btn-edit-role:hover {
            background: #f8fafc;
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .roles-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .roles-header a {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .roles-search-container {
                flex-direction: column;
            }

            .roles-search-container input {
                width: 100%;
            }

            .btn-search-roles {
                width: 100%;
            }

            /* Card-based table on mobile */
            .roles-table thead {
                display: none;
            }

            .roles-table,
            .roles-table tbody,
            .roles-table tr,
            .roles-table td {
                display: block;
                width: 100%;
            }

            .roles-table tr {
                margin-bottom: 15px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 10px;
                background: #f8fafc;
            }

            .roles-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                padding: 10px 5px;
                border-bottom: 1px solid #edf2f7;
            }

            .roles-table td:last-child {
                border-bottom: none;
            }

            .roles-table td::before {
                content: attr(data-label);
                font-weight: 700;
                font-size: 0.7rem;
                color: #64748b;
                text-transform: uppercase;
                text-align: left;
                margin-right: 10px;
            }

            .content-wrapper {
                padding: 15px;
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
                <div class="roles-header">
                    <h1><i class="fas fa-user-tag"></i> Gestión de Roles</h1>
                    <a href="nuevo_rol.php" class="btn btn-primary" style="padding: 10px 20px; background: #2563eb;">
                        <i class="fas fa-plus"></i> Nuevo Rol
                    </a>
                </div>

                <div class="roles-search-container">
                    <input type="text" placeholder="Buscar roles...">
                    <button class="btn-search-roles"><i class="fas fa-search"></i></button>
                </div>

                <div class="roles-table-container" style="background: transparent; border: none; box-shadow: none;">
                    <table class="roles-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th style="text-align: center;">Usuarios</th>
                                <th style="text-align: center;">Permisos</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $r): ?>
                                <tr>
                                    <td data-label="Nombre" style="font-weight: 700;">
                                        <i class="fas fa-user-shield" style="color: #2563eb; margin-right: 10px;"></i>
                                        <?php echo $r['nombre']; ?>
                                    </td>
                                    <td data-label="Descripción" style="color: #64748b;">
                                        <?php echo $r['desc']; ?>
                                    </td>
                                    <td data-label="Usuarios" style="text-align: center;"><span class="badge-users">
                                            <?php echo $r['users']; ?> usuarios
                                        </span></td>
                                    <td data-label="Permisos" style="text-align: center;"><span class="badge-perms">
                                            <?php echo $r['perms']; ?> permisos
                                        </span></td>
                                    <td data-label="Creado" style="color: #64748b; font-size: 0.8rem;">
                                        <?php echo $r['created']; ?>
                                    </td>
                                    <td data-label="Acciones">
                                        <a href="editar_rol.php?nombre=<?php echo $r['nombre']; ?>" class="btn-edit-role">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
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