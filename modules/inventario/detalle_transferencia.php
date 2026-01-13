<?php
/**
 * Stock Transfer Detail View - Detalle de Transferencia
 */
session_start();
require_once '../../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: transferencias.php');
    exit;
}

try {
    // Fetch Header
    $stmt = $pdo->prepare("SELECT t.*, 
                uo.nombre as origen_nombre, 
                ud.nombre as destino_nombre,
                u.nombre as usuario_nombre
              FROM inventario_transferenciastock t
              LEFT JOIN inventario_ubicacion uo ON t.ubicacion_origen_id = uo.id
              LEFT JOIN inventario_ubicacion ud ON t.ubicacion_destino_id = ud.id
              LEFT JOIN usuarios u ON t.usuario_creacion_id = u.id
              WHERE t.id = :id");
    $stmt->execute([':id' => $id]);
    $transferencia = $stmt->fetch();

    if (!$transferencia) {
        throw new Exception("Transferencia no encontrada.");
    }

    // Fetch Details
    $stmt_det = $pdo->prepare("SELECT d.*, p.nombre as producto_nombre, p.codigoPrincipal as barcode
                               FROM inventario_detalletransferencia d
                               JOIN productos p ON d.producto_id = p.id
                               WHERE d.transferencia_id = :id");
    $stmt_det->execute([':id' => $id]);
    $detalles = $stmt_det->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$current_page = 'inventario_transferencias';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Transferencia | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .dt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .dt-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .dt-card-header {
            background: #f8fafc;
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            justify-content: space-between;
        }

        .dt-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px;
        }

        .info-item label {
            display: block;
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .info-item span {
            font-size: 0.95rem;
            color: #1e293b;
            font-weight: 500;
        }

        .table-det {
            width: 100%;
            border-collapse: collapse;
        }

        .table-det th {
            background: #f8fafc;
            padding: 12px 15px;
            text-align: left;
            font-size: 0.75rem;
            color: #64748b;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-det td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-completed { background: #dcfce7; color: #166534; }
    </style>
</head>

<body>
    <div class="app-container">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="main-content">
            <?php include '../../includes/navbar.php'; ?>
            <div class="content-wrapper">
                <div class="dt-header">
                    <h1>Mantenimiento de Transferencia</h1>
                    <div style="display: flex; gap: 10px;">
                        <a href="transferencias.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
                        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                    </div>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <span>Detalles del Documento: <?php echo htmlspecialchars($transferencia['numero_transferencia']); ?></span>
                        <span class="badge badge-pending"><?php echo $transferencia['estado']; ?></span>
                    </div>
                    <div class="dt-info-grid">
                        <div class="info-item">
                            <label>Ubicación Origen</label>
                            <span><?php echo htmlspecialchars($transferencia['origen_nombre']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Ubicación Destino</label>
                            <span><?php echo htmlspecialchars($transferencia['destino_nombre']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Fecha de Creación</label>
                            <span><?php echo date('d/m/Y H:i', strtotime($transferencia['creadoDate'])); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Motivo</label>
                            <span><?php echo htmlspecialchars($transferencia['motivo']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Usuario Responsable</label>
                            <span><?php echo htmlspecialchars($transferencia['usuario_nombre']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Observaciones</label>
                            <span><?php echo htmlspecialchars($transferencia['observaciones'] ?: 'Sin notas'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">Productos Incluidos</div>
                    <table class="table-det">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th style="text-align: center;">Cantidad Transferida</th>
                                <th style="text-align: center;">Estado Recep.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $d): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($d['barcode']); ?></td>
                                    <td><?php echo htmlspecialchars($d['producto_nombre']); ?></td>
                                    <td style="text-align: center; font-weight: 700;"><?php echo number_format($d['cantidad'], 2); ?></td>
                                    <td style="text-align: center;">-</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($transferencia['estado'] == 'PENDIENTE'): ?>
                    <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px;">
                        <button class="btn btn-outline" style="color: #ef4444; border-color: #fecaca;">Anular Transferencia</button>
                        <button class="btn btn-primary" style="background: #10b981;">Confirmar Recepción</button>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php include '../../includes/scripts.php'; ?>
</body>

</html>
