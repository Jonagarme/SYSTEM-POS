<?php
/**
 * Caja Movements History - Historial de Movimientos de Caja
 */
session_start();
require_once '../../includes/db.php';

$current_page = 'caja_movimientos';

// Mock data
$movimientos = [
    ['id' => '24', 'caja' => 'CAJA002', 'sub' => 'CAJA SECUNDARIA', 'tipo' => 'APERTURA', 'fecha_ap' => '10/11/2025 20:46:56', 'fecha_ci' => '--', 'estado' => 'ABIERTA', 'saldo_in' => '$0,00', 'saldo_fi' => '--', 'diff' => '--', 'usuario' => 'Apertura: Usuario 3'],
    ['id' => '23', 'caja' => 'CAJA001', 'sub' => 'Caja Principal', 'tipo' => 'CIERRE', 'fecha_ap' => '09/11/2025 19:41:24', 'fecha_ci' => '09/11/2025 19:45:36', 'estado' => 'CERRADA', 'saldo_in' => '$50,00', 'saldo_fi' => '$52,50', 'diff' => '+$2,50', 'usuario' => 'Apertura: Usuario 1', 'u2' => 'Cierre: Usuario 1'],
    ['id' => '21', 'caja' => 'CAJA001', 'sub' => 'Caja Principal', 'tipo' => 'CIERRE', 'fecha_ap' => '08/11/2025 18:30:21', 'fecha_ci' => '08/11/2025 13:33:19', 'estado' => 'CERRADA', 'saldo_in' => '$1000,00', 'saldo_fi' => '$0,00', 'diff' => '$0,00', 'usuario' => 'Apertura: Usuario 1', 'u2' => 'Cierre: Usuario 1'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Movimientos de Caja | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .hist-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .hist-title h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .hist-title p {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 5px;
        }

        .btn-hist-group {
            display: flex;
            gap: 10px;
        }

        .btn-view-actual {
            background: #0ea5e9;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-cerrar-hist {
            background: #f59e0b;
            color: #1e293b;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .active-alert {
            background: #e0faff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
            color: #0369a1;
        }

        .active-alert h4 {
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .active-alert p {
            font-size: 0.85rem;
            font-weight: 600;
            margin: 0;
        }

        .hist-table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .hist-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .hist-table th {
            background: #212529;
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .hist-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .row-open {
            background: #fef9c3;
        }

        .row-closed-profit {
            background: #f0fdf4;
        }

        .row-closed {
            background: #f8fafc;
        }

        .badge-tipo {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .bg-apert {
            background: #f59e0b;
            color: white;
        }

        .bg-cierr {
            background: #64748b;
            color: white;
        }

        .badge-est {
            padding: 4px 10px;
            border-radius: 40px;
            font-size: 0.65rem;
            font-weight: 700;
        }

        .est-abierta {
            background: #fef08a;
            color: #854d0e;
        }

        .est-cerrada {
            background: #64748b;
            color: white;
        }

        .val-badge {
            background: #3b82f6;
            color: white;
            padding: 2px 12px;
            border-radius: 20px;
            font-weight: 700;
        }

        .val-total-c {
            background: #0ea5e9;
            color: white;
            padding: 2px 12px;
            border-radius: 20px;
            font-weight: 700;
        }

        .diff-pos {
            background: #f59e0b;
            color: #1e293b;
            padding: 2px 12px;
            border-radius: 20px;
            font-weight: 700;
        }

        .diff-zero {
            background: #059669;
            color: white;
            padding: 2px 12px;
            border-radius: 20px;
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
                <div class="hist-header">
                    <div class="hist-title">
                        <h1><i class="fas fa-history"></i> Historial de Movimientos de Caja</h1>
                        <p>Registro de todas las aperturas y cierres de caja</p>
                    </div>
                    <div class="btn-hist-group">
                        <button class="btn-view-actual"><i class="fas fa-eye"></i> Ver Estado Actual</button>
                        <button class="btn-cerrar-hist"><i class="fas fa-lock"></i> Cerrar Caja</button>
                    </div>
                </div>

                <div class="active-alert">
                    <h4><i class="fas fa-info-circle"></i> Caja Actualmente Abierta</h4>
                    <p>CAJA SECUNDARIA - Abierta el 10/11/2025 a las 20:46</p>
                    <small>Saldo inicial: $0,00</small>
                </div>

                <div class="hist-table-container">
                    <table class="hist-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Caja</th>
                                <th>Tipo</th>
                                <th>Fecha Apertura</th>
                                <th>Fecha Cierre</th>
                                <th>Estado</th>
                                <th>Saldo Inicial</th>
                                <th>Saldo Final</th>
                                <th>Diferencia</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 (Open) -->
                            <tr class="row-open">
                                <td style="font-weight: 700;">#24</td>
                                <td>
                                    <strong>CAJA002</strong><br>
                                    <small style="color: #64748b;">CAJA SECUNDARIA</small>
                                </td>
                                <td><span class="badge-tipo bg-apert"><i class="fas fa-lock-open"></i> APERTURA</span>
                                </td>
                                <td>10/11/2025<br>20:46:56</td>
                                <td style="color: #cbd5e1;">--</td>
                                <td><span class="badge-est est-abierta">ABIERTA</span></td>
                                <td><span class="val-badge">$0,00</span></td>
                                <td style="color: #cbd5e1;">--</td>
                                <td style="color: #cbd5e1;">--</td>
                                <td><strong>Apertura:</strong> Usuario 3</td>
                            </tr>
                            <!-- Row 2 (Closed with Profit) -->
                            <tr class="row-closed-profit">
                                <td style="font-weight: 700;">#23</td>
                                <td>
                                    <strong>CAJA001</strong><br>
                                    <small style="color: #64748b;">Caja Principal</small>
                                </td>
                                <td><span class="badge-tipo bg-cierr"><i class="fas fa-lock"></i> CIERRE</span></td>
                                <td>09/11/2025<br>19:41:24</td>
                                <td>09/11/2025<br>19:45:36</td>
                                <td><span class="badge-est est-cerrada">CERRADA</span></td>
                                <td><span class="val-badge">$50,00</span></td>
                                <td><span class="val-total-c">$52,50</span></td>
                                <td><span class="diff-pos">+$2,50</span></td>
                                <td><strong>Apertura:</strong> Usuario 1<br><strong>Cierre:</strong> Usuario 1</td>
                            </tr>
                            <!-- Row 3 (Closed Default) -->
                            <tr class="row-closed">
                                <td style="font-weight: 700;">#21</td>
                                <td>
                                    <strong>CAJA001</strong><br>
                                    <small style="color: #64748b;">Caja Principal</small>
                                </td>
                                <td><span class="badge-tipo bg-cierr"><i class="fas fa-lock"></i> CIERRE</span></td>
                                <td>08/11/2025<br>18:30:21</td>
                                <td>08/11/2025<br>13:33:19</td>
                                <td><span class="badge-est est-cerrada">CERRADA</span></td>
                                <td><span class="val-badge">$1000,00</span></td>
                                <td><span class="val-total-c">$0,00</span></td>
                                <td><span class="diff-zero">$0,00</span></td>
                                <td><strong>Apertura:</strong> Usuario 1<br><strong>Cierre:</strong> Usuario 1</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>