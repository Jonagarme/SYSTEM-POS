<?php
/**
 * Cronograma de Pagos - Accounting Module
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'contabilidad_cronograma';

// Cargar cronograma completo de la base de datos
$cronograma = [];
try {
    $sql = "SELECT cp.*, c.factura_proveedor, p.razonSocial as proveedor_nombre, c.monto_pendiente
            FROM contabilidad_cronogramapago cp
            JOIN contabilidad_cuentaporpagar c ON cp.cuenta_pagar_id = c.id
            LEFT JOIN proveedores p ON c.proveedor_id = p.id
            ORDER BY cp.fecha_programada ASC";
    $stmt = $pdo->query($sql);
    $cronograma = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar cronograma: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma de Pagos | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .accounting-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .accounting-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .acc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .acc-table th {
            background: #f8fafc;
            padding: 15px 20px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            border-bottom: 2px solid #f1f5f9;
        }

        .acc-table td {
            padding: 18px 20px;
            font-size: 0.9rem;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-pending {
            background: #fff7ed;
            color: #c2410c;
        }

        .status-paid {
            background: #ecfdf5;
            color: #047857;
        }

        .overdue {
            color: #dc2626 !important;
            font-weight: 700;
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
                <div class="accounting-header">
                    <h1><i class="fas fa-calendar-alt"></i> Cronograma de Pagos Programados</h1>
                </div>

                <div class="table-container">
                    <div style="overflow-x: auto;">
                        <table class="acc-table">
                            <thead>
                                <tr>
                                    <th>Fecha Programada</th>
                                    <th>Proveedor</th>
                                    <th>Factura</th>
                                    <th>Cuota</th>
                                    <th style="text-align: right;">Monto</th>
                                    <th>Estado</th>
                                    <th style="text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cronograma)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">No hay
                                            pagos programados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $today = date('Y-m-d');
                                    foreach ($cronograma as $c):
                                        $isOverdue = ($c['estado'] === 'PENDIENTE' && $c['fecha_programada'] < $today);
                                        ?>
                                        <tr>
                                            <td class="<?php echo $isOverdue ? 'overdue' : ''; ?>">
                                                <i class="far fa-calendar-alt"></i>
                                                <?php echo date('d/m/Y', strtotime($c['fecha_programada'])); ?>
                                                <?php if ($isOverdue): ?> <i class="fas fa-exclamation-triangle"
                                                        title="Vencido"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-weight: 600;">
                                                <?php echo htmlspecialchars($c['proveedor_nombre']); ?>
                                            </td>
                                            <td><code><?php echo htmlspecialchars($c['factura_proveedor']); ?></code></td>
                                            <td>Cuota
                                                <?php echo $c['cuota_numero']; ?>
                                            </td>
                                            <td style="text-align: right; font-weight: 600;">$
                                                <?php echo number_format($c['monto'], 2); ?>
                                            </td>
                                            <td>
                                                <span
                                                    class="status-badge <?php echo ($c['estado'] === 'PAGADO') ? 'status-paid' : 'status-pending'; ?>">
                                                    <?php echo $c['estado']; ?>
                                                </span>
                                            </td>
                                            <td style="text-align: right;">
                                                <?php if ($c['estado'] === 'PENDIENTE'): ?>
                                                    <button class="btn-pay"
                                                        onclick="pagarCuota(<?php echo $c['id']; ?>, <?php echo $c['monto']; ?>)"
                                                        style="background: #2563eb; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">
                                                        <i class="fas fa-money-bill-wave"></i> Pagar
                                                    </button>
                                                <?php else: ?>
                                                    <i class="fas fa-check-circle" style="color: #059669;" title="Pagado"></i>
                                                            <?php endif; ?>
                                                            </td>
                                                </tr>
                                        <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        async function pagarCuota(id, monto) {
            const result = await Swal.fire({
                title: '¿Confirmar pago de cuota?',
                text: `Se marcará como pagado el monto de $${monto.toFixed(2)}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, pagar ahora',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('api_pagar.php?action=pay_quota', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.error, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'No se pudo procesar el pago', 'error');
                }
            }
        }
    </script>
</body>

</html>