<?php
/**
 * Nueva Ubicación - Warehouse POS premium
 */
session_start();
require_once '../../includes/db.php';

// Verificar sesión (ajusta según tu sistema de login)
if (!isset($_SESSION['user_id'])) {
    // Para desarrollo, si no hay sesión usaré ID 1, pero lo ideal es redireccionar
    $user_id = 1;
} else {
    $user_id = $_SESSION['user_id'];
}

$message = '';
$error = '';

// Lógica de Guardado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_location'])) {
    try {
        $codigo = $_POST['codigo'];
        $nombre = $_POST['nombre'];
        $tipo = $_POST['tipo'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $responsable = $_POST['responsable'] ?? 'Administrador'; // Campo requerido en DB
        $activo = isset($_POST['activo']) ? 1 : 0;
        $es_principal = 0; // Por defecto no es principal al crear
        $now = date('Y-m-d H:i:s');

        $sql = "INSERT INTO inventario_ubicacion 
                (codigo, nombre, tipo, direccion, telefono, responsable, activo, es_principal, creadoDate, editadoDate, anulado, creadoPor_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $codigo,
            $nombre,
            $tipo,
            $direccion,
            $telefono,
            $responsable,
            $activo,
            $es_principal,
            $now,
            $now,
            $user_id
        ]);

        $message = "¡Ubicación creada con éxito!";
    } catch (PDOException $e) {
        $error = "Error al guardar: " . $e->getMessage();
    }
}

$current_page = 'inventario_ubicaciones';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Ubicación | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #64748b;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
        }

        .content-wrapper {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header & Breadcrumb */
        .nu-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .nu-title h1 {
            font-size: 1.6rem;
            color: var(--text-main);
            margin: 0 0 8px 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nu-title h1 i {
            color: var(--primary);
        }

        .breadcrumb {
            display: flex;
            gap: 5px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .btn-back {
            background: var(--surface);
            color: var(--secondary);
            border: 1px solid var(--border);
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-back:hover {
            background: #f1f5f9;
            color: var(--text-main);
        }

        /* Grid Layout */
        .nu-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            align-items: start;
        }

        /* Panels */
        .nu-panel {
            background: var(--surface);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border);
            margin-bottom: 25px;
        }

        .nu-panel-header {
            background: var(--gradient-primary);
            color: white;
            padding: 14px 24px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nu-panel-body {
            padding: 30px;
        }

        /* Forms */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .form-group label span {
            color: #ef4444;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--text-main);
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .form-group .help-text {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        textarea.form-control {
            padding-left: 15px;
            resize: none;
        }

        /* State Select */
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 18px;
        }

        /* Footer Buttons */
        .nu-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn-save {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-cancel {
            background: #ebeef2;
            color: #475569;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            color: var(--text-main);
        }

        /* Right Side Widgets */
        .preview-card {
            background: white;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            border: 2px dashed #cbd5e1;
            margin-bottom: 25px;
            color: var(--text-muted);
        }

        .preview-card i {
            font-size: 3.5rem;
            color: #e2e8f0;
            margin-bottom: 20px;
            display: block;
        }

        .preview-card h4 {
            margin: 0 0 10px 0;
            font-size: 1rem;
            color: var(--text-muted);
        }

        .info-widget {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .info-widget-header {
            background: var(--gradient-primary);
            padding: 12px 20px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-widget-body {
            padding: 20px;
        }

        .info-widget-body h5 {
            font-size: 0.85rem;
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-main);
        }

        .tips-list {
            padding: 0;
            margin: 0 0 20px 0;
            list-style: none;
        }

        .tips-list li {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 10px;
            display: flex;
            gap: 10px;
        }

        .tips-list li::before {
            content: "•";
            color: var(--primary);
            font-weight: bold;
        }

        .types-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .types-list li {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .types-list strong {
            color: var(--text-main);
        }

        /* Notifications */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @media (max-width: 1024px) {
            .nu-grid {
                grid-template-columns: 1fr;
            }

            .nu-right {
                order: -1;
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
                <div class="nu-header">
                    <div class="nu-title">
                        <h1><i class="fas fa-map-marked-alt"></i> Nueva Ubicación</h1>
                        <div class="breadcrumb">
                            <a href="../../index.php">Dashboard</a> / <a href="transferencias.php">Inventario</a> / <a
                                href="ubicaciones.php">Ubicaciones</a> / <span>Nueva Ubicación</span>
                        </div>
                    </div>
                    <a href="ubicaciones.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver a Ubicaciones
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="nu-grid" autocomplete="off">
                    <div class="nu-left">
                        <div class="nu-panel">
                            <div class="nu-panel-header">
                                <i class="fas fa-plus"></i> Información de la Ubicación
                            </div>
                            <div class="nu-panel-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nombre de la Ubicación <span>*</span></label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-building"></i>
                                            <input type="text" name="nombre" class="form-control"
                                                placeholder="Ej: Sucursal Centro, Bodega Principal" required>
                                        </div>
                                        <span class="help-text">Nombre descriptivo de la ubicación</span>
                                    </div>
                                    <div class="form-group">
                                        <label>Código de Ubicación <span>*</span></label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-barcode"></i>
                                            <input type="text" name="codigo" class="form-control"
                                                placeholder="Ej: SUC001, BOD001" required>
                                        </div>
                                        <span class="help-text">Código único de identificación</span>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Tipo de Ubicación <span>*</span></label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-tags"></i>
                                            <select name="tipo" class="form-control" required>
                                                <option value="" disabled selected>Seleccione un tipo...</option>
                                                <option value="sucursal">Sucursal</option>
                                                <option value="bodega">Bodega</option>
                                                <option value="almacen">Almacén</option>
                                                <option value="establecimiento">Establecimiento</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-toggle-on"></i>
                                            <select name="activo" class="form-control">
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Dirección <span>*</span></label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-map-pin"></i>
                                        <input type="text" name="direccion" class="form-control"
                                            placeholder="Dirección completa de la ubicación" required>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 20px;">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-phone-alt"></i>
                                            <input type="text" name="telefono" class="form-control"
                                                placeholder="Ej: +593 999 999 999">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Responsable</label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-user-tie"></i>
                                            <input type="text" name="responsable" class="form-control"
                                                placeholder="Nombre del encargado">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Capacidad (m²)</label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-expand-arrows-alt"></i>
                                            <input type="number" step="0.01" name="capacidad" class="form-control"
                                                placeholder="Ej: 150.50">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-top: 10px;">
                                    <label>Descripción / Observaciones</label>
                                    <textarea name="observaciones" class="form-control" rows="4"
                                        placeholder="Descripción adicional de la ubicación, características especiales, etc."></textarea>
                                    <div
                                        style="text-align: right; font-size: 0.75rem; color: #94a3b8; margin-top: 8px;">
                                        Máximo 500 caracteres
                                    </div>
                                </div>

                                <div class="nu-footer">
                                    <a href="ubicaciones.php" class="btn-cancel">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" name="save_location" class="btn-save">
                                        <i class="fas fa-save"></i> Guardar Ubicación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="nu-right">
                        <div class="nu-panel">
                            <div class="nu-panel-header" style="background: var(--primary-dark);">
                                <i class="fas fa-eye"></i> Vista Previa
                            </div>
                            <div class="nu-panel-body">
                                <div class="preview-card">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <h4>Vista previa de la ubicación</h4>
                                    <p>Complete el formulario para ver los detalles aquí.</p>
                                </div>
                            </div>
                        </div>

                        <div class="info-widget">
                            <div class="info-widget-header">
                                <i class="fas fa-info-circle"></i> Información
                            </div>
                            <div class="info-widget-body">
                                <h5><i class="fas fa-lightbulb" style="color: #fbbf24;"></i> Consejos:</h5>
                                <ul class="tips-list">
                                    <li>Use códigos únicos para cada ubicación para evitar confusiones en inventario.
                                    </li>
                                    <li>Mantenga nombres descriptivos y cortos (Ej: "Bodega Norte").</li>
                                    <li>Complete la dirección exacta para gestionar mejor las rutas de transferencia.
                                    </li>
                                    <li>Definir la capacidad ayuda en la planificación de stock máximo.</li>
                                </ul>

                                <h5 style="margin-top: 25px;"><i class="fas fa-shapes" style="color: #0ea5e9;"></i>
                                    Tipos de Ubicación:</h5>
                                <ul class="types-list">
                                    <li><strong>• Sucursal:</strong> Punto de venta directo al cliente.</li>
                                    <li><strong>• Bodega:</strong> Almacenamiento principal de mercadería.</li>
                                    <li><strong>• Almacén:</strong> Depósito secundario o de tránsito.</li>
                                    <li><strong>• Establecimiento:</strong> Punto de venta o matriz principal.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>