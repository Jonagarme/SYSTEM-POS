<?php
/**
 * Puntos de Emisión - Sistema POS
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'config_puntos';

// 1. Obtener establecimientos
$stmtEst = $pdo->query("SELECT * FROM establecimientos ORDER BY codigo ASC");
$establecimientos = $stmtEst->fetchAll(PDO::FETCH_ASSOC);

// 2. Obtener usuarios para el responsable
$stmtUser = $pdo->query("SELECT id, nombreCompleto as nombre FROM usuarios WHERE activo = 1 ORDER BY nombreCompleto ASC");
$usuarios = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

// 3. Procesar acciones (AJAX o POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_punto') {
            $id = $_POST['id'] ?? null;
            $id_est = $_POST['id_establecimiento'];
            $codigo = $_POST['codigo'];
            $descripcion = $_POST['descripcion'];
            $id_usuario = $_POST['id_usuario_responsable'] ?: null;
            $activo = isset($_POST['activo']) ? 1 : 0;
            $sec_factura = $_POST['secuencial_factura'] ?: 1;
            $sec_nc = $_POST['secuencial_nota_credito'] ?: 1;
            $sec_nd = $_POST['secuencial_nota_debito'] ?: 1;
            $sec_gr = $_POST['secuencial_guia_remision'] ?: 1;
            $sec_ret = $_POST['secuencial_retencion'] ?: 1;

            if ($id) {
                $stmt = $pdo->prepare("UPDATE puntos_emision SET 
                    id_establecimiento = ?, codigo = ?, descripcion = ?, id_usuario_responsable = ?, 
                    activo = ?, secuencial_factura = ?, secuencial_nota_credito = ?, 
                    secuencial_nota_debito = ?, secuencial_guia_remision = ?, secuencial_retencion = ? 
                    WHERE id = ?");
                $stmt->execute([$id_est, $codigo, $descripcion, $id_usuario, $activo, $sec_factura, $sec_nc, $sec_nd, $sec_gr, $sec_ret, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO puntos_emision (id_establecimiento, codigo, descripcion, id_usuario_responsable, activo, secuencial_factura, secuencial_nota_credito, secuencial_nota_debito, secuencial_guia_remision, secuencial_retencion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_est, $codigo, $descripcion, $id_usuario, $activo, $sec_factura, $sec_nc, $sec_nd, $sec_gr, $sec_ret]);
            }
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'delete_punto') {
            $id = $_POST['id'];
            $pdo->prepare("DELETE FROM puntos_emision WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puntos de Emisión | Sistema POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .est-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            border: 1px solid #eef2f7;
            overflow: hidden;
        }

        .est-header {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .est-info h2 {
            font-size: 1.1rem;
            margin: 0;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .est-info p {
            font-size: 0.85rem;
            color: #64748b;
            margin: 5px 0 0;
        }

        .puntos-grid {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .punto-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            position: relative;
            transition: all 0.2s;
        }

        .punto-card:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        }

        .punto-code {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .punto-desc {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 15px;
        }

        .punto-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 0.8rem;
            color: #475569;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }

        .stat-item span:last-child {
            font-weight: 700;
            color: #6366f1;
        }

        .punto-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 5px;
        }

        .btn-icon {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .btn-edit {
            background: #e0e7ff;
            color: #4338ca;
        }

        .btn-edit:hover {
            background: #c7d2fe;
        }

        .btn-delete {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-delete:hover {
            background: #fecaca;
        }

        /* Modal custom */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .c-form-group {
            margin-bottom: 15px;
        }

        .c-form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #334155;
        }

        .c-form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .secuenciales-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
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
                <div class="page-header"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <div>
                        <h1>Configuración de Puntos de Emisión</h1>
                        <p style="color: #64748b;">Gestiona las sucursales y cajas habilitadas para facturación
                            electrónica</p>
                    </div>
                </div>

                <?php foreach ($establecimientos as $est): ?>
                    <div class="est-card">
                        <div class="est-header">
                            <div class="est-info">
                                <h2><i class="fas fa-store"></i>
                                    <?php echo $est['codigo']; ?> -
                                    <?php echo htmlspecialchars($est['nombre_comercial']); ?>
                                </h2>
                                <p><i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($est['direccion']); ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <span class="badge"
                                    style="background: <?php echo $est['estado'] == 'Activo' ? '#dcfce7; color: #15803d;' : '#fee2e2; color: #991b1b;'; ?>">
                                    <?php echo $est['estado']; ?>
                                </span>
                                <button class="btn btn-primary btn-sm" onclick="openModal(<?php echo $est['id']; ?>)">
                                    <i class="fas fa-plus"></i> Nuevo Punto
                                </button>
                            </div>
                        </div>
                        <div class="puntos-grid">
                            <?php
                            $stmtP = $pdo->prepare("SELECT p.*, u.nombreCompleto as responsable 
                                FROM puntos_emision p 
                                LEFT JOIN usuarios u ON p.id_usuario_responsable = u.id 
                                WHERE p.id_establecimiento = ?");
                            $stmtP->execute([$est['id']]);
                            $puntos = $stmtP->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($puntos as $p): ?>
                                <div class="punto-card">
                                    <div class="punto-actions">
                                        <button class="btn-icon btn-edit"
                                            onclick="editPunto(<?php echo htmlspecialchars(json_encode($p)); ?>)">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="deletePunto(<?php echo $p['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="punto-code">
                                        <?php echo $p['codigo']; ?>
                                    </div>
                                    <div class="punto-desc">
                                        <?php echo htmlspecialchars($p['descripcion'] ?: 'Sin descripción'); ?>
                                    </div>
                                    <?php if ($p['responsable']): ?>
                                        <div style="font-size: 0.8rem; margin-bottom: 10px; color: #6366f1;">
                                            <i class="fas fa-user-circle"></i>
                                            <?php echo htmlspecialchars($p['responsable']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="punto-stats">
                                        <div class="stat-item"><span>Factura:</span> <span>
                                                <?php echo $p['secuencial_factura']; ?>
                                            </span></div>
                                        <div class="stat-item"><span>N. Crédito:</span> <span>
                                                <?php echo $p['secuencial_nota_credito']; ?>
                                            </span></div>
                                        <div class="stat-item"><span>N. Débito:</span> <span>
                                                <?php echo $p['secuencial_nota_debito']; ?>
                                            </span></div>
                                        <div class="stat-item"><span>Guía Rem.:</span> <span>
                                                <?php echo $p['secuencial_guia_remision']; ?>
                                            </span></div>
                                        <div class="stat-item"><span>Retención:</span> <span>
                                                <?php echo $p['secuencial_retencion']; ?>
                                            </span></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Modal Form -->
    <div class="modal-overlay" id="modal-punto">
        <div class="modal-card">
            <h2 id="modal-title" style="margin-bottom: 25px;">Nuevo Punto de Emisión</h2>
            <form id="form-punto">
                <input type="hidden" name="action" value="save_punto">
                <input type="hidden" name="id" id="punto-id">
                <input type="hidden" name="id_establecimiento" id="est-id">

                <div style="display: grid; grid-template-columns: 120px 1fr; gap: 20px;">
                    <div class="c-form-group">
                        <label>Código (3 dígitos)</label>
                        <input type="text" name="codigo" id="p-codigo" class="c-form-control" placeholder="999"
                            maxlength="3" required>
                    </div>
                    <div class="c-form-group">
                        <label>Descripción</label>
                        <input type="text" name="descripcion" id="p-descripcion" class="c-form-control"
                            placeholder="Ej: Caja Pruebas SRI">
                    </div>
                </div>

                <div class="c-form-group">
                    <label>Usuario Responsable</label>
                    <select name="id_usuario_responsable" id="p-usuario" class="c-form-control">
                        <option value="">-- Seleccionar Usuario --</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo $u['id']; ?>">
                                <?php echo htmlspecialchars($u['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="c-form-group" style="margin-top: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="activo" id="p-activo" checked style="width: 18px; height: 18px;">
                        Punto Activo
                    </label>
                </div>

                <h3 style="font-size: 0.85rem; text-transform: uppercase; color: #64748b; margin-top: 25px;">
                    Secuenciales (Inicio / Actual)</h3>
                <div class="secuenciales-grid">
                    <div class="c-form-group">
                        <label>Factura</label>
                        <input type="number" name="secuencial_factura" id="s-fac" class="c-form-control" value="1">
                    </div>
                    <div class="c-form-group">
                        <label>Nota de Crédito</label>
                        <input type="number" name="secuencial_nota_credito" id="s-nc" class="c-form-control" value="1">
                    </div>
                    <div class="c-form-group">
                        <label>Nota de Débito</label>
                        <input type="number" name="secuencial_nota_debito" id="s-nd" class="c-form-control" value="1">
                    </div>
                    <div class="c-form-group">
                        <label>Guía Remisión</label>
                        <input type="number" name="secuencial_guia_remision" id="s-gr" class="c-form-control" value="1">
                    </div>
                    <div class="c-form-group">
                        <label>Retención</label>
                        <input type="number" name="secuencial_retencion" id="s-ret" class="c-form-control" value="1">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="background: #6366f1;">
                        <i class="fas fa-save"></i> Guardar Punto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        const modal = document.getElementById('modal-punto');
        const form = document.getElementById('form-punto');

        function openModal(estId) {
            form.reset();
            document.getElementById('punto-id').value = '';
            document.getElementById('est-id').value = estId;
            document.getElementById('modal-title').innerText = 'Nuevo Punto de Emisión';
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function editPunto(data) {
            form.reset();
            document.getElementById('punto-id').value = data.id;
            document.getElementById('est-id').value = data.id_establecimiento;
            document.getElementById('p-codigo').value = data.codigo;
            document.getElementById('p-descripcion').value = data.descripcion;
            document.getElementById('p-usuario').value = data.id_usuario_responsable || '';
            document.getElementById('p-activo').checked = data.activo == 1;
            document.getElementById('s-fac').value = data.secuencial_factura;
            document.getElementById('s-nc').value = data.secuencial_nota_credito;
            document.getElementById('s-nd').value = data.secuencial_nota_debito;
            document.getElementById('s-gr').value = data.secuencial_guia_remision;
            document.getElementById('s-ret').value = data.secuencial_retencion;

            document.getElementById('modal-title').innerText = 'Editar Punto: ' + data.codigo;
            modal.style.display = 'flex';
        }

        form.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const res = await fetch('puntos_emision.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        };

        async function deletePunto(id) {
            if (!confirm('¿Estás seguro de eliminar este punto de emisión?')) return;
            const formData = new FormData();
            formData.append('action', 'delete_punto');
            formData.append('id', id);
            const res = await fetch('puntos_emision.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        }
    </script>
</body>

</html>