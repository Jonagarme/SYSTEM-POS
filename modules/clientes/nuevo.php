<?php
require_once '../../includes/db.php';
$current_page = 'clientes';
$root = '../../';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO clientes (nombres, apellidos, cedula_ruc, direccion, celular, email, tipo_cliente, creadoPor, creadoDate) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())");
    $stmt->execute([
        $_POST['nombres'],
        $_POST['apellidos'],
        $_POST['cedula_ruc'],
        $_POST['direccion'],
        $_POST['celular'],
        $_POST['email'],
        $_POST['tipo_cliente']
    ]);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Nuevo Cliente | Sistema POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr !important;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .form-actions a,
            .form-actions button {
                width: 100%;
                justify-content: center;
            }

            .content-wrapper {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="app-container">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="main-content">
            <?php include '../../includes/navbar.php'; ?>
            <div class="content-wrapper">
                <div style="max-width: 800px; margin: 0 auto;">
                    <div style="margin-bottom: 25px;">
                        <a href="index.php" style="color: #64748b; font-size: 0.9rem;"><i class="fas fa-arrow-left"></i>
                            Volver a la lista</a>
                        <h1 style="margin-top: 10px;">Nuevo Cliente</h1>
                    </div>

                    <form action="" method="POST"
                        style="background: white; padding: 30px; border-radius: 12px; box-shadow: var(--shadow-sm);">
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Nombres *</label>
                                <input type="text" name="nombres" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Apellidos *</label>
                                <input type="text" name="apellidos" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Cédula / RUC *</label>
                                <input type="text" name="cedula_ruc" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Tipo de Cliente</label>
                                <select name="tipo_cliente" class="form-control">
                                    <option value="Natural">Persona Natural</option>
                                    <option value="Juridica">Persona Jurídica</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>WhatsApp / Celular</label>
                                <input type="text" name="celular" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="form-group" style="grid-column: 1 / span 2;">
                                <label>Dirección</label>
                                <textarea name="direccion" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-actions"
                            style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar
                                Cliente</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>