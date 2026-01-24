<?php
/**
 * Box Opening - Apertura de Caja
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'caja_apertura';

// Obtener cajas disponibles desde la base de datos
$stmt_cajas = $pdo->query("SELECT id, codigo, nombre FROM cajas WHERE anulado = 0 AND activa = 1 ORDER BY nombre ASC");
$cajas_disponibles = $stmt_cajas->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apertura de Caja | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .apertura-container {
            max-width: 600px;
            margin: 40px auto;
        }

        .apertura-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .apertura-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .apertura-header i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .apertura-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .apertura-header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .apertura-body {
            padding: 30px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .input-with-icon .form-control {
            padding-left: 40px;
            height: 48px;
        }

        .btn-apertura {
            width: 100%;
            background: #059669;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            transition: all 0.2s;
        }

        .btn-apertura:hover {
            background: #047857;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
        }

        .info-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            color: #166534;
            font-size: 0.85rem;
            display: flex;
            gap: 12px;
        }

        .info-box i {
            font-size: 1.2rem;
            margin-top: 2px;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .apertura-container {
                margin: 20px;
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
                <div class="apertura-container">
                    <div class="apertura-card">
                        <div class="apertura-header">
                            <i class="fas fa-unlock-alt"></i>
                            <h1>Apertura de Caja</h1>
                            <p>Inicie su jornada laboral registrando el saldo inicial</p>
                        </div>

                        <form class="apertura-body" action="procesar_apertura.php" method="POST">
                            <div class="info-box">
                                <i class="fas fa-info-circle"></i>
                                <span>Es obligatorio registrar el monto con el que inicia la caja para mantener el
                                    control de su efectivo.</span>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label class="form-label">Seleccionar Caja</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-cash-register"></i>
                                    <select name="caja_id" class="form-control" required>
                                        <option value="">Seleccione una caja...</option>
                                        <?php foreach ($cajas_disponibles as $caja): ?>
                                            <option value="<?php echo $caja['id']; ?>">
                                                <?php echo $caja['codigo'] . ' - ' . $caja['nombre']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div style="margin-bottom: 20px;">
                                <label class="form-label">Monto Inicial (Efectivo)</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                    <input type="number" name="monto_inicial" class="form-control" step="0.01"
                                        placeholder="0.00" required>
                                </div>
                                <small style="color: #64748b; display: block; margin-top: 5px;">Ingrese el dinero físico
                                    disponible en caja.</small>
                            </div>

                            <div style="margin-bottom: 25px;">
                                <label class="form-label">Observaciones (Opcional)</label>
                                <textarea name="observaciones" class="form-control"
                                    style="height: 80px; padding: 12px; resize: none;"
                                    placeholder="Alguna nota sobre el estado de la caja..."></textarea>
                            </div>

                            <button type="submit" class="btn-apertura">
                                <i class="fas fa-check-circle"></i> Confirmar Apertura
                            </button>

                            <a href="index.php"
                                style="display: block; text-align: center; margin-top: 15px; color: #64748b; font-size: 0.85rem; text-decoration: none;">
                                <i class="fas fa-arrow-left"></i> Volver a Gestión de Cajas
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>