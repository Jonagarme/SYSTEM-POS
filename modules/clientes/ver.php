<?php
require_once '../../includes/db.php'; $current_page = 'clientes'; $root = '../../';

$id = $_GET['id'] ?? 0;
// Fetch client details
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) { header("Location: index.php"); exit; }

// Fetch sales history for this client
$stmtSales = $pdo->prepare("
    SELECT f.*, u.nombreUsuario as vendedor
    FROM facturas_venta f
    LEFT JOIN usuarios u ON f.idUsuario = u.id
    WHERE f.idCliente = ? AND f.anulado = 0
    ORDER BY f.fechaEmision DESC
");
$stmtSales->execute([$id]);
$ventas = $stmtSales->fetchAll();

// Calculate some totals
$totalComprado = 0;
foreach($ventas as $v) $totalComprado += $v['total'];
$cantCompras = count($ventas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Perfil del Cliente | Sistema POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .profile-container { display: grid; grid-template-columns: 350px 1fr; gap: 25px; }
        .info-card { background: white; border-radius: 12px; box-shadow: var(--shadow-sm); padding: 25px; height: fit-content; }
        .history-card { background: white; border-radius: 12px; box-shadow: var(--shadow-sm); padding: 25px; }
        .avatar-lg { width: 80px; height: 80px; background: #0061f2; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; margin: 0 auto 15px; }
        .stat-mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; }
        .stat-mini { background: #f8fafc; padding: 15px; border-radius: 10px; text-align: center; }
        .h-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .h-table th { text-align: left; padding: 12px; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 0.8rem; text-transform: uppercase; }
        .h-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        .status-badge { padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .status-authorized { background: #dcfce7; color: #15803d; }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="main-content">
            <?php include '../../includes/navbar.php'; ?>
            <div class="content-wrapper">
                <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <a href="index.php" style="color: #64748b; font-size: 0.9rem;"><i class="fas fa-arrow-left"></i> Volver</a>
                        <h1 style="margin-top: 10px;">Perfil del Cliente</h1>
                    </div>
                    <div style="display:flex; gap: 10px;">
                        <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-secondary"><i class="fas fa-edit"></i> Editar</a>
                    </div>
                </div>

                <div class="profile-container">
                    <!-- Left: Info Card -->
                    <div class="info-card">
                        <div class="avatar-lg"><?php echo strtoupper(substr($cliente['nombres'], 0, 1)); ?></div>
                        <h2 style="text-align: center; margin-bottom: 5px;"><?php echo htmlspecialchars($cliente['nombres'].' '.$cliente['apellidos']); ?></h2>
                        <p style="text-align: center; color: #64748b; font-size: 0.9rem; margin-bottom: 20px;"><?php echo htmlspecialchars($cliente['tipo_cliente']); ?></p>
                        
                        <div style="border-top: 1px solid #f1f5f9; padding-top: 20px;">
                            <div style="margin-bottom: 12px;">
                                <label style="font-size: 0.75rem; color: #64748b; display: block;">Identificación</label>
                                <strong><?php echo htmlspecialchars($cliente['cedula_ruc']); ?></strong>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="font-size: 0.75rem; color: #64748b; display: block;">Email</label>
                                <strong><?php echo htmlspecialchars($cliente['email'] ?: 'No registrado'); ?></strong>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="font-size: 0.75rem; color: #64748b; display: block;">Teléfono</label>
                                <strong><?php echo htmlspecialchars($cliente['celular'] ?: 'No registrado'); ?></strong>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="font-size: 0.75rem; color: #64748b; display: block;">Dirección</label>
                                <strong style="font-size: 0.85rem; color: #475569;"><?php echo htmlspecialchars($cliente['direccion'] ?: 'No registrado'); ?></strong>
                            </div>
                        </div>

                        <div class="stat-mini-grid">
                            <div class="stat-mini">
                                <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Compras</span>
                                <div style="font-weight: 700; font-size: 1.2rem; color: #0061f2;"><?php echo $cantCompras; ?></div>
                            </div>
                            <div class="stat-mini">
                                <span style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">Total Comprado</span>
                                <div style="font-weight: 700; font-size: 1.2rem; color: #10b981;">$<?php echo number_format($totalComprado, 2); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Sales History -->
                    <div class="history-card">
                        <h3 style="margin-bottom: 20px;"><i class="fas fa-history"></i> Historial de Ventas</h3>
                        <?php if (empty($ventas)): ?>
                            <div style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fas fa-shopping-bag" style="font-size: 3rem; opacity: 0.2; margin-bottom: 15px;"></i>
                                <p>Este cliente aún no registra compras.</p>
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table class="h-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>N° Factura</th>
                                            <th>Vendedor</th>
                                            <th>Estado</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ventas as $v): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($v['fechaEmision'])); ?></td>
                                            <td style="font-weight: 600; color: #0061f2;"><?php echo $v['numeroFactura']; ?></td>
                                            <td><?php echo htmlspecialchars($v['vendedor'] ?: 'Admin'); ?></td>
                                            <td><span class="status-badge status-authorized"><?php echo $v['estado']; ?></span></td>
                                            <td style="font-weight: 700;">$<?php echo number_format($v['total'], 2); ?></td>
                                            <td>
                                                <a href="../ventas/ver_factura.php?id=<?php echo $v['id']; ?>" class="btn-act view" title="Ver Factura"><i class="fas fa-file-invoice"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include '../../includes/scripts.php'; ?>
</body>
</html>
