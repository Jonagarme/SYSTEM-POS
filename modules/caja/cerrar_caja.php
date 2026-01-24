<?php
/**
 * Close Caja & Arqueo - Cierre de Caja con Arqueo
 */
session_start();
require_once '../../includes/db.php';

// 1. Buscar la sesión de caja abierta
$stmt = $pdo->query("SELECT c.*, ca.nombre as caja_nombre, u.nombreCompleto as usuario_nombre 
                     FROM cierres_caja c 
                     LEFT JOIN cajas ca ON c.idCaja = ca.id 
                     LEFT JOIN usuarios u ON c.idUsuarioApertura = u.id 
                     WHERE c.estado = 'ABIERTA' 
                     ORDER BY c.fechaApertura DESC 
                     LIMIT 1");
$sesion = $stmt->fetch();

if (!$sesion) {
    header("Location: estado.php?error=no_session");
    exit;
}

// 2. Calcular saldo esperado (Sistema)
// Saldo Inicial + Ventas - Gastos (si los hubiera)
$stmtV = $pdo->prepare("SELECT SUM(total) as total_ventas FROM facturas_venta WHERE idCierreCaja = ? AND anulado = 0");
$stmtV->execute([$sesion['id']]);
$total_ventas = $stmtV->fetch()['total_ventas'] ?? 0.00;

$saldo_esperado = $sesion['saldoInicial'] + $total_ventas;

$current_page = 'caja_estado';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arqueo de Caja | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .arqueo-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .arqueo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
        }

        .arqueo-header h1 {
            font-size: 1.4rem;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .arqueo-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 20px;
        }

        .panel-arqueo {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .panel-header-count {
            padding: 15px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
            font-weight: 700;
            color: #2563eb;
            display: flex;
            justify-content: space-between;
        }

        .panel-body-count {
            padding: 20px;
        }

        .denominations-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .denom-group h4 {
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 5px;
        }

        .denom-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
        }

        .denom-label {
            width: 80px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }

        .denom-input {
            width: 80px;
            text-align: center;
        }

        .denom-total {
            flex: 1;
            text-align: right;
            font-size: 0.9rem;
            font-weight: 700;
            color: #475569;
        }

        /* Right Column Panels */
        .resumen-arqueo {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .stat-box .lbl {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        .stat-box .val {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
        }

        .stat-highlight {
            border-left: 5px solid #2563eb;
        }

        .stat-success {
            border-left: 5px solid #059669;
        }

        .stat-diff {
            border-left: 5px solid #dc2626;
        }

        .difference-neg {
            color: #dc2626 !important;
        }

        .difference-pos {
            color: #059669 !important;
        }

        .btn-finalize {
            width: 100%;
            background: #2563eb;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .btn-finalize:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .btn-cancel {
            width: 100%;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            justify-content: center;
        }

        /* RESPONSIVE STYLES */
        @media (max-width: 992px) {
            .arqueo-grid {
                grid-template-columns: 1fr;
            }

            .denominations-grid {
                gap: 15px;
            }
        }

        @media (max-width: 600px) {
            .denominations-grid {
                grid-template-columns: 1fr;
            }

            .arqueo-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .btn-cancel {
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
                <form id="form-cierre" action="procesar_cierre.php" method="POST" class="arqueo-container">
                    <input type="hidden" name="id_sesion" value="<?php echo $sesion['id']; ?>">
                    <input type="hidden" name="total_fisico" id="total-fisico-input" value="0">
                    <input type="hidden" name="total_sistema" value="<?php echo $saldo_esperado; ?>">

                    <div class="arqueo-header">
                        <div>
                            <h1><i class="fas fa-calculator"></i> Arqueo de Cierre</h1>
                            <p style="color: #64748b; font-size: 0.85rem; margin-top: 5px;">
                                Caja: <strong><?php echo htmlspecialchars($sesion['caja_nombre']); ?></strong> |
                                Usuario: <strong><?php echo htmlspecialchars($sesion['usuario_nombre']); ?></strong>
                            </p>
                        </div>
                        <a href="estado.php" class="btn-cancel">Cancelar Cierre</a>
                    </div>

                    <div class="arqueo-grid">
                        <div class="panel-arqueo">
                            <div class="panel-header-count">
                                <span>Conteo Físico de Efectivo</span>
                                <span style="color: #64748b;"><i class="fas fa-coins"></i></span>
                            </div>
                            <div class="panel-body-count">
                                <div class="denominations-grid">
                                    <div class="denom-group">
                                        <h4>Billetes</h4>
                                        <?php
                                        $billetes = [100, 50, 20, 10, 5, 2, 1];
                                        foreach ($billetes as $b): ?>
                                            <div class="denom-row">
                                                <div class="denom-label">$ <?php echo $b; ?> </div>
                                                <input type="number" class="form-control denom-input"
                                                    name="bill_<?php echo $b; ?>" value="0" min="0"
                                                    onchange="calculateTotal()">
                                                <div class="denom-total" id="total-b-<?php echo $b; ?>">$ 0.00</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="denom-group">
                                        <h4>Monedas</h4>
                                        <?php
                                        $monedas = [
                                            ['v' => 1.00, 'l' => '$ 1.00'],
                                            ['v' => 0.50, 'l' => '50 ¢'],
                                            ['v' => 0.25, 'l' => '25 ¢'],
                                            ['v' => 0.10, 'l' => '10 ¢'],
                                            ['v' => 0.05, 'l' => '05 ¢'],
                                            ['v' => 0.01, 'l' => '01 ¢'],
                                        ];
                                        foreach ($monedas as $m): ?>
                                            <div class="denom-row">
                                                <div class="denom-label"><?php echo $m['l']; ?></div>
                                                <input type="number" class="form-control denom-input"
                                                    name="coin_<?php echo str_replace('.', '_', $m['v']); ?>" value="0"
                                                    min="0" onchange="calculateTotal()">
                                                <div class="denom-total"
                                                    id="total-m-<?php echo str_replace('.', '_', $m['v']); ?>">$ 0.00</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="resumen-arqueo">
                            <div class="stat-box stat-highlight">
                                <span class="lbl">Saldo Esperado (Sistema)</span>
                                <div class="val" id="saldo-sistema">$ <?php echo number_format($saldo_esperado, 2); ?>
                                </div>
                            </div>

                            <div class="stat-box stat-success">
                                <span class="lbl">Suma Total Arqueo (Físico)</span>
                                <div class="val" id="total-arqueo">$ 0.00</div>
                            </div>

                            <div class="stat-box stat-diff">
                                <span class="lbl">Diferencia / Descuadre</span>
                                <div class="val" id="diferencia-total">$
                                    -<?php echo number_format($saldo_esperado, 2); ?></div>
                                <p id="diff-msg"
                                    style="font-size: 0.75rem; margin-top: 10px; font-weight: 600; color: #64748b;">
                                    Turno con faltante</p>
                            </div>

                            <div class="panel-arqueo">
                                <div class="panel-header-count">Observaciones del Cierre</div>
                                <div class="panel-body-count" style="padding: 15px;">
                                    <textarea class="form-control" name="observaciones" rows="3"
                                        placeholder="Ej: Faltante por entrega de sencillo..."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn-finalize">
                                <i class="fas fa-lock"></i> Finalizar y Cerrar Caja
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>

    <script>
        const expected = <?php echo $saldo_esperado; ?>;
        const bills = [100, 50, 20, 10, 5, 2, 1];
        const coins = [1.00, 0.50, 0.25, 0.10, 0.05, 0.01];

        function calculateTotal() {
            let total = 0;

            // Calculate Bills
            const billInputs = document.querySelectorAll('.denom-group:first-child .denom-input');
            billInputs.forEach((input, index) => {
                const val = bills[index] * parseInt(input.value || 0);
                total += val;
                document.getElementById('total-b-' + bills[index]).textContent = '$ ' + val.toFixed(2);
            });

            // Calculate Coins
            const coinInputs = document.querySelectorAll('.denom-group:last-child .denom-input');
            coinInputs.forEach((input, index) => {
                const val = coins[index] * parseInt(input.value || 0);
                total += val;
                const id = 'total-m-' + coins[index].toString().replace('.', '_');
                document.getElementById(id).textContent = '$ ' + val.toFixed(2);
            });

            // Update Summaries
            const totalArqueo = document.getElementById('total-arqueo');
            totalArqueo.textContent = '$ ' + total.toFixed(2);
            document.getElementById('total-fisico-input').value = total;

            const diff = total - expected;
            const diffTotal = document.getElementById('diferencia-total');
            const diffMsg = document.getElementById('diff-msg');

            diffTotal.textContent = (diff >= 0 ? '+ ' : '') + '$ ' + Math.abs(diff).toFixed(2);

            if (diff === 0) {
                diffTotal.className = 'val';
                diffMsg.textContent = '¡Caja Cuadrada Perfectamente!';
                diffMsg.style.color = '#059669';
            } else if (diff < 0) {
                diffTotal.className = 'val difference-neg';
                diffTotal.textContent = '- $ ' + Math.abs(diff).toFixed(2);
                diffMsg.textContent = 'Turno con faltante de efectivo';
                diffMsg.style.color = '#dc2626';
            } else {
                diffTotal.className = 'val difference-pos';
                diffMsg.textContent = 'Turno con excedente de efectivo';
                diffMsg.style.color = '#059669';
            }
        }

        document.getElementById('form-cierre').onsubmit = function () {
            return confirm('¿Estás seguro de que deseas finalizar el turno y cerrar la caja?');
        };
    </script>
</body>

</html>