<?php
/**
 * Tax Management - Gestión de Impuestos
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'config_impuestos';
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_tax'])) {
        $id = $_POST['id'] ?? null;
        $codigo = $_POST['codigo'];
        $nombre = $_POST['nombre'];
        $porcentaje = $_POST['porcentaje'];
        $vigenteDesde = !empty($_POST['vigenteDesde']) ? $_POST['vigenteDesde'] : null;
        $vigenteHasta = !empty($_POST['vigenteHasta']) ? $_POST['vigenteHasta'] : null;
        $activo = isset($_POST['activo']) ? 1 : 0;
        $descripcion = $_POST['descripcion'];

        try {
            if ($id) {
                // Update
                $stmt = $pdo->prepare("UPDATE impuestos SET codigo = ?, nombre = ?, porcentaje = ?, vigenteDesde = ?, vigenteHasta = ?, activo = ?, descripcion = ? WHERE id = ?");
                $stmt->execute([$codigo, $nombre, $porcentaje / 100, $vigenteDesde, $vigenteHasta, $activo, $descripcion, $id]);
                $message = "Impuesto actualizado correctamente.";
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO impuestos (codigo, nombre, porcentaje, vigenteDesde, vigenteHasta, activo, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$codigo, $nombre, $porcentaje / 100, $vigenteDesde, $vigenteHasta, $activo, $descripcion]);
                $message = "Impuesto creado correctamente.";
            }
            $action = 'list';
        } catch (PDOException $e) {
            $error = "Error al guardar: " . $e->getMessage();
        }
    }

    if (isset($_POST['delete_tax'])) {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM impuestos WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Impuesto eliminado correctamente.";
        } catch (PDOException $e) {
            $error = "No se puede eliminar el impuesto porque está en uso.";
        }
    }
}

// Fetch taxes for list
$impuestos = [];
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM impuestos ORDER BY codigo ASC");
    $impuestos = $stmt->fetchAll();
}

// Fetch tax for edit
$tax = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM impuestos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $tax = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Impuestos | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .tax-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }

        .tax-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tax-card-header h2 {
            font-size: 1rem;
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tax-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tax-table th {
            text-align: left;
            padding: 12px 24px;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #f1f5f9;
        }

        .tax-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-edit {
            color: #3b82f6;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            padding: 6px;
            border-radius: 6px;
        }

        .btn-delete {
            color: #ef4444;
            background: #fef2f2;
            border: 1px solid #fee2e2;
            padding: 6px;
            border-radius: 6px;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
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
                <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1><i class="fas fa-percent"></i> Gestión de Impuestos</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../../index.php">Panel</a></li>
                                <li class="breadcrumb-item active">Impuestos</li>
                            </ol>
                        </nav>
                    </div>
                    <?php if ($action === 'list'): ?>
                        <a href="?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Impuesto</a>
                    <?php else: ?>
                        <a href="?action=list" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                    <?php endif; ?>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <div class="tax-card">
                        <div class="tax-card-header">
                            <h2><i class="fas fa-list"></i> Listado de Impuestos</h2>
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="tax-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Porcentaje</th>
                                        <th>Vigencia</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($impuestos)): ?>
                                        <tr>
                                            <td colspan="6" style="text-align: center; color: #64748b;">No hay impuestos
                                                registrados.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($impuestos as $i): ?>
                                            <tr>
                                                <td style="font-weight: 700; color: #1e293b;">
                                                    <?php echo htmlspecialchars($i['codigo']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($i['nombre']); ?></td>
                                                <td><?php echo number_format($i['porcentaje'] * 100, 4); ?>%</td>
                                                <td style="font-size: 0.8rem; color: #64748b;">
                                                    <?php
                                                    if ($i['vigenteDesde']) {
                                                        echo date('d/m/Y', strtotime($i['vigenteDesde']));
                                                        if ($i['vigenteHasta']) {
                                                            echo " al " . date('d/m/Y', strtotime($i['vigenteHasta']));
                                                        } else {
                                                            echo " (Indefinido)";
                                                        }
                                                    } else {
                                                        echo "No definido";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="<?php echo $i['activo'] ? 'badge-active' : 'badge-inactive'; ?>">
                                                        <?php echo $i['activo'] ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-btns">
                                                        <a href="?action=edit&id=<?php echo $i['id']; ?>" class="btn-edit"
                                                            title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" style="display:inline;"
                                                            onsubmit="return confirm('¿Está seguro de eliminar este impuesto?');">
                                                            <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                                            <button type="submit" name="delete_tax" class="btn-delete"
                                                                title="Eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Form view -->
                    <div class="tax-card">
                        <div class="tax-card-header">
                            <h2><i class="fas <?php echo $tax ? 'fa-edit' : 'fa-plus'; ?>"></i>
                                <?php echo $tax ? 'Editar Impuesto' : 'Crear Impuesto'; ?></h2>
                        </div>
                        <div class="modal-body">
                            <form method="POST">
                                <?php if ($tax): ?>
                                    <input type="hidden" name="id" value="<?php echo $tax['id']; ?>">
                                <?php endif; ?>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                    <div class="form-group">
                                        <label>Código</label>
                                        <input type="text" name="codigo" class="form-control" placeholder="Ej: IVA, ICE"
                                            required value="<?php echo $tax ? htmlspecialchars($tax['codigo']) : ''; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Porcentaje (%)</label>
                                        <input type="number" step="0.0001" name="porcentaje" class="form-control"
                                            placeholder="Ej: 15.0000" required
                                            value="<?php echo $tax ? $tax['porcentaje'] * 100 : ''; ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" class="form-control"
                                        placeholder="Nombre completo del impuesto" required
                                        value="<?php echo $tax ? htmlspecialchars($tax['nombre']) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label>Descripción</label>
                                    <textarea name="descripcion" class="form-control" style="height: 100px;"
                                        placeholder="Opcional: Detalles del impuesto"><?php echo $tax ? htmlspecialchars($tax['descripcion']) : ''; ?></textarea>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                    <div class="form-group">
                                        <label>Vigente Desde</label>
                                        <input type="date" name="vigenteDesde" class="form-control"
                                            value="<?php echo $tax ? $tax['vigenteDesde'] : ''; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Vigente Hasta</label>
                                        <input type="date" name="vigenteHasta" class="form-control"
                                            value="<?php echo $tax ? $tax['vigenteHasta'] : ''; ?>">
                                    </div>
                                </div>

                                <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="activo" id="activoCheckbox"
                                        style="width: 18px; height: 18px;" <?php echo (!$tax || $tax['activo']) ? 'checked' : ''; ?>>
                                    <label for="activoCheckbox" style="margin-bottom: 0; cursor: pointer;">Activo</label>
                                </div>

                                <div style="display: flex; justify-content: flex-end; margin-top: 30px;">
                                    <button type="submit" name="save_tax" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        <?php echo $tax ? 'Actualizar Impuesto' : 'Guardar Impuesto'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
</body>

</html>