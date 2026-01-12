<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
$current_page = 'clientes';
$root = '../../';

$search = $_GET['search'] ?? '';
$where = "WHERE anulado = 0";
$params = [];

if (!empty($search)) {
    $where .= " AND (nombres LIKE ? OR apellidos LIKE ? OR cedula_ruc LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Stats
$total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes WHERE anulado = 0")->fetchColumn();
$clientes_mes = $pdo->query("SELECT COUNT(*) FROM clientes WHERE anulado = 0 AND creadoDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();

// Fetch limited list
$stmt = $pdo->prepare("SELECT * FROM clientes $where ORDER BY creadoDate DESC LIMIT 20");
$stmt->execute($params);
$clientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes | Sistema POS</title>
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

        .u-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .u-table {
            width: 100%;
            border-collapse: collapse;
        }

        .u-table th {
            background: #f8fafc;
            padding: 15px 20px;
            text-align: left;
            font-size: 0.85rem;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }

        .u-table td {
            padding: 12px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .badge-verified {
            background: #dcfce7;
            color: #15803d;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .u-actions {
            display: flex;
            gap: 8px;
        }

        .btn-act {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-act.edit {
            background: #eff6ff;
            color: #1e69ff;
        }

        .btn-act.view {
            background: #f8fafc;
            color: #64748b;
        }

        .btn-act.delete {
            background: #fff1f2;
            color: #e11d48;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="main-content">
            <?php include '../../includes/navbar.php'; ?>
            <div class="content-wrapper">
                <!-- Stats Cards -->
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div
                        style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); border-left: 4px solid #0061f2;">
                        <span
                            style="color: #64748b; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Total
                            Clientes</span>
                        <h2 style="margin: 5px 0; font-size: 1.8rem;"><?php echo $total_clientes; ?></h2>
                        <span style="font-size: 0.75rem; color: #10b981;"><i class="fas fa-users"></i> Base de datos
                            activa</span>
                    </div>
                    <div
                        style="background: white; padding: 20px; border-radius: 12px; box-shadow: var(--shadow-sm); border-left: 4px solid #10b981;">
                        <span
                            style="color: #64748b; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Nuevos
                            (30 días)</span>
                        <h2 style="margin: 5px 0; font-size: 1.8rem;"><?php echo $clientes_mes; ?></h2>
                        <span style="font-size: 0.75rem; color: #10b981;"><i class="fas fa-arrow-up"></i> Crecimiento
                            mensual</span>
                    </div>
                </div>

                <div class="u-header">
                    <div>
                        <h1>Listado de Clientes</h1>
                        <p style="color: #64748b;">Resultados: <?php echo count($clientes); ?> mostrados</p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <form action="" method="GET" style="display: flex; gap: 5px;">
                            <input type="text" name="search" class="form-control" placeholder="Nombre o Cédula..."
                                value="<?php echo htmlspecialchars($search); ?>" style="width: 250px;">
                            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
                        </form>
                        <a href="nuevo.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo</a>
                    </div>
                </div>

                <div class="u-table-container">
                    <table class="u-table">
                        <thead>
                            <tr>
                                <th>CLIENTE</th>
                                <th>IDENTIFICACIÓN</th>
                                <th>CONTACTO</th>
                                <th>UBICACIÓN</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $c): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div
                                                style="width: 35px; height: 35px; background: #e0f2fe; color: #0369a1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                                <?php echo strtoupper(substr($c['nombres'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong style="display: block; color: #1e293b;">
                                                    <?php echo htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']); ?>
                                                </strong>
                                                <span style="font-size: 0.75rem; color: #64748b;">
                                                    <?php echo htmlspecialchars($c['tipo_cliente'] ?? 'Natural'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong>
                                            <?php echo htmlspecialchars($c['cedula_ruc']); ?>
                                        </strong></td>
                                    <td>
                                        <div style="font-size: 0.8rem;">
                                            <div><i class="fas fa-phone" style="width: 15px;"></i>
                                                <?php echo htmlspecialchars($c['celular']); ?>
                                            </div>
                                            <div style="color: #64748b;"><i class="fas fa-envelope"
                                                    style="width: 15px;"></i>
                                                <?php echo htmlspecialchars($c['email']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width: 200px; font-size: 0.8rem; color: #475569;">
                                        <?php echo htmlspecialchars($c['direccion']); ?>
                                    </td>
                                    <td><span class="badge-verified">
                                            <?php echo $c['estado'] ? 'Activo' : 'Inactivo'; ?>
                                        </span></td>
                                    <td>
                                        <div class="u-actions">
                                            <a href="ver.php?id=<?php echo $c['id']; ?>" class="btn-act view"><i
                                                    class="fas fa-eye"></i></a>
                                            <a href="editar.php?id=<?php echo $c['id']; ?>" class="btn-act edit"><i
                                                    class="fas fa-edit"></i></a>
                                            <button class="btn-act delete"><i class="fas fa-trash"></i></button>
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
    <?php include '../../includes/scripts.php'; ?>
</body>

</html>