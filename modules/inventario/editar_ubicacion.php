<?php
/**
 * Editar Ubicación - Warehouse POS premium
 */
session_start();
require_once '../../includes/db.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    $user_id = 1;
} else {
    $user_id = $_SESSION['user_id'];
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: ubicaciones.php');
    exit;
}

$message = '';
$error = '';

// Cargar datos actuales
try {
    $stmt = $pdo->prepare("SELECT * FROM inventario_ubicacion WHERE id = ? AND anulado = 0");
    $stmt->execute([$id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) {
        header('Location: ubicaciones.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Lógica de Actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_location'])) {
    try {
        $codigo = $_POST['codigo'];
        $nombre = $_POST['nombre'];
        $tipo = $_POST['tipo'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $responsable = $_POST['responsable'] ?? 'Administrador';
        $activo = isset($_POST['activo']) ? 1 : (($_POST['activo_select'] ?? '1') == '1' ? 1 : 0);
        $now = date('Y-m-d H:i:s');

        $sql = "UPDATE inventario_ubicacion SET 
                codigo = ?, nombre = ?, tipo = ?, direccion = ?, telefono = ?, responsable = ?, activo = ?, editadoDate = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $codigo,
            $nombre,
            $tipo,
            $direccion,
            $telefono,
            $responsable,
            $activo,
            $now,
            $id
        ]);

        $message = "¡Ubicación actualizada con éxito!";
        // Recargar datos
        $stmt = $pdo->prepare("SELECT * FROM inventario_ubicacion WHERE id = ?");
        $stmt->execute([$id]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error al actualizar: " . $e->getMessage();
    }
}

$current_page = 'inventario_ubicaciones';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ubicación | Warehouse POS</title>
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
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .nu-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            align-items: start;
        }

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

        textarea.form-control {
            padding-left: 15px;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 18px;
        }

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
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
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
        }

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
                        <h1><i class="fas fa-edit"></i> Editar Ubicación:
                            <?php echo htmlspecialchars($u['nombre']); ?>
                        </h1>
                        <div class="breadcrumb">
                            <a href="../../index.php">Dashboard</a> / <a href="ubicaciones.php">Inventario</a> / <a
                                href="ubicaciones.php">Ubicaciones</a> / <span>Editar</span>
                        </div>
                    </div>
                    <a href="ubicaciones.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="nu-grid" autocomplete="off">
                    <div class="nu-left">
                        <div class="nu-panel">
                            <div class="nu-panel-header">
                                <i class="fas fa-info-circle"></i> Información de la Ubicación
                            </div>
                            <div class="nu-panel-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nombre de la Ubicación <span>*</span></label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-building"></i>
                                            <input type="text" name="nombre" class="form-control"
                                                value="<?php echo htmlspecialchars($u['nombre']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Código de Ubicación <span>*</span></label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-barcode"></i>
                                            <input type="text" name="codigo" class="form-control"
                                                value="<?php echo htmlspecialchars($u['codigo']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Tipo de Ubicación <span>*</span></label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-tags"></i>
                                            <select name="tipo" class="form-control" required>
                                                <option value="sucursal" <?php echo $u['tipo'] == 'sucursal' ? 'selected' : ''; ?>>Sucursal</option>
                                                <option value="bodega" <?php echo $u['tipo'] == 'bodega' ? 'selected' : ''; ?>>Bodega</option>
                                                <option value="almacen" <?php echo $u['tipo'] == 'almacen' ? 'selected' : ''; ?>>Almacén</option>
                                                <option value="establecimiento" <?php echo $u['tipo'] == 'establecimiento' ? 'selected' : ''; ?>>Establecimiento</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-toggle-on"></i>
                                            <select name="activo_select" class="form-control">
                                                <option value="1" <?php echo $u['activo'] ? 'selected' : ''; ?>>Activo
                                                </option>
                                                <option value="0" <?php echo !$u['activo'] ? 'selected' : ''; ?>
                                                    >Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Dirección <span>*</span></label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-map-pin"></i>
                                        <input type="text" name="direccion" class="form-control"
                                            value="<?php echo htmlspecialchars($u['direccion']); ?>" required>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 20px;">
                                    <div class="form-group">
                                        <label>Teléfono</label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-phone-alt"></i>
                                            <input type="text" name="telefono" class="form-control"
                                                value="<?php echo htmlspecialchars($u['telefono']); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Responsable</label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-user-tie"></i>
                                            <input type="text" name="responsable" class="form-control"
                                                value="<?php echo htmlspecialchars($u['responsable']); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Capacidad (m²)</label>
                                        <div class="input-icon-wrapper">
                                            <i class="fas fa-expand-arrows-alt"></i>
                                            <input type="number" step="0.01" name="capacidad" class="form-control"
                                                value="<?php echo htmlspecialchars($u['capacidad'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="nu-footer">
                                    <a href="ubicaciones.php" class="btn-cancel">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" name="update_location" class="btn-save">
                                        <i class="fas fa-save"></i> Actualizar Cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="nu-right">
                        <div class="nu-panel">
                            <div class="nu-panel-header" style="background: var(--secondary);">
                                <i class="fas fa-history"></i> Datos de Registro
                            </div>
                            <div class="nu-panel-body">
                                <div style="font-size: 0.85rem; color: #64748b;">
                                    <p><strong>Creado el:</strong><br>
                                        <?php echo $u['creadoDate']; ?>
                                    </p>
                                    <p><strong>Última edición:</strong><br>
                                        <?php echo $u['editadoDate']; ?>
                                    </p>
                                    <p><strong>Ubicación ID:</strong> #
                                        <?php echo $u['id']; ?>
                                    </p>
                                </div>
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