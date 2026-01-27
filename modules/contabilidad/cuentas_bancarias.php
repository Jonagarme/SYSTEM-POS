<?php
/**
 * Cuentas Bancarias - Accounting Module
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'contabilidad_cuentas_bancarias';

// Cargar cuentas bancarias desde la base de datos para la vista inicial
$cuentas = [];
try {
    $stmt = $pdo->query("SELECT cb.*, cc.nombre as cuenta_contable_nombre, cc.codigo as cuenta_contable_codigo 
                         FROM contabilidad_cuentabancaria cb
                         LEFT JOIN contabilidad_cuentacontable cc ON cb.cuenta_contable_id = cc.id
                         ORDER BY cb.nombre ASC");
    $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar cuentas bancarias: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas Bancarias | Warehouse POS</title>
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

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn-accounting {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-accounting:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: #2563eb;
        }

        .btn-success {
            background: #059669;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .table-toolbar {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
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
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-active {
            background: #ecfdf5;
            color: #047857;
        }

        .status-inactive {
            background: #fef2f2;
            color: #b91c1c;
        }

        .actions-cell {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #eff6ff;
        }

        .btn-icon.btn-delete:hover {
            border-color: #dc2626;
            color: #dc2626;
            background: #fef2f2;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #94a3b8;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #1e293b;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-modal {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-submit {
            background: #2563eb;
            color: white;
        }

        /* Autocomplete */
        .autocomplete-container {
            position: relative;
        }

        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .autocomplete-results.active {
            display: block;
        }

        .autocomplete-item {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
        }

        .autocomplete-item:hover {
            background: #f8fafc;
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
                    <h1><i class="fas fa-university"></i> Cuentas Bancarias</h1>
                    <div class="header-actions">
                        <button class="btn-accounting btn-primary" onclick="openModalCuenta()">
                            <i class="fas fa-plus"></i> Nueva Cuenta
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-toolbar">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Buscar por nombre o banco..."
                                onkeyup="filterTable()">
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="acc-table" id="cuentasTable">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Banco</th>
                                    <th>Nº Cuenta</th>
                                    <th>Tipo</th>
                                    <th style="text-align: right;">Saldo Inicial</th>
                                    <th>Fecha Apertura</th>
                                    <th>Estado</th>
                                    <th style="text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cuentas)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                                            No se encontraron cuentas bancarias.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cuentas as $c): ?>
                                        <tr>
                                            <td style="font-weight: 600;">
                                                <?php echo htmlspecialchars($c['nombre']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($c['banco']); ?>
                                            </td>
                                            <td><code><?php echo htmlspecialchars($c['numero_cuenta']); ?></code></td>
                                            <td>
                                                <?php echo htmlspecialchars($c['tipo']); ?>
                                            </td>
                                            <td style="text-align: right; font-weight: 600;">$
                                                <?php echo number_format($c['saldo_inicial'], 2); ?>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($c['fecha_apertura'])); ?>
                                            </td>
                                            <td>
                                                <span
                                                    class="status-badge <?php echo $c['activa'] ? 'status-active' : 'status-inactive'; ?>">
                                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                                    <?php echo $c['activa'] ? 'Activa' : 'Inactiva'; ?>
                                                </span>
                                            </td>
                                            <td class="actions-cell">
                                                <button class="btn-icon" title="Editar"
                                                    onclick='editCuenta(<?php echo json_encode($c); ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-icon btn-delete" title="Eliminar"
                                                    onclick="deleteCuenta(<?php echo $c['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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

    <!-- Modal Nueva/Editar Cuenta -->
    <div class="modal-overlay" id="modalCuenta">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-plus-circle"></i> Nueva Cuenta Bancaria</h2>
                <button class="modal-close" onclick="closeModal('modalCuenta')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCuenta" onsubmit="saveCuenta(event)">
                    <input type="hidden" name="id" id="cuenta_id">

                    <div class="form-group">
                        <label>Nombre de la Cuenta *</label>
                        <input type="text" name="nombre" id="nombre" placeholder="Ej: Cuenta Corriente Principal"
                            required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Banco *</label>
                            <input type="text" name="banco" id="banco" placeholder="Ej: Banco Pichincha" required>
                        </div>
                        <div class="form-group">
                            <label>Número de Cuenta *</label>
                            <input type="text" name="numero_cuenta" id="numero_cuenta" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Tipo de Cuenta *</label>
                            <select name="tipo" id="tipo" required>
                                <option value="Ahorros">Ahorros</option>
                                <option value="Corriente">Corriente</option>
                                <option value="Virtual">Virtual / Digital</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Saldo Inicial *</label>
                            <input type="number" name="saldo_inicial" id="saldo_inicial" step="0.01" value="0.00"
                                required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Fecha de Apertura *</label>
                            <input type="date" name="fecha_apertura" id="fecha_apertura"
                                value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="activa" id="activa">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Cuenta Contable Relacionada</label>
                        <div class="autocomplete-container">
                            <input type="text" id="cc_search" placeholder="Buscar cuenta contable..."
                                autocomplete="off">
                            <input type="hidden" name="cuenta_contable_id" id="cuenta_contable_id">
                            <div class="autocomplete-results" id="cc_results"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('modalCuenta')">Cancelar</button>
                <button type="submit" form="formCuenta" class="btn-modal btn-submit">Guardar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openModalCuenta() {
            document.getElementById('formCuenta').reset();
            document.getElementById('cuenta_id').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Nueva Cuenta Bancaria';
            document.getElementById('cuenta_contable_id').value = '';
            document.getElementById('cc_search').value = '';
            document.getElementById('modalCuenta').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function editCuenta(data) {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Cuenta Bancaria';
            document.getElementById('cuenta_id').value = data.id;
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('banco').value = data.banco;
            document.getElementById('numero_cuenta').value = data.numero_cuenta;
            document.getElementById('tipo').value = data.tipo;
            document.getElementById('saldo_inicial').value = data.saldo_inicial;
            document.getElementById('fecha_apertura').value = data.fecha_apertura;
            document.getElementById('activa').value = data.activa;
            document.getElementById('cuenta_contable_id').value = data.cuenta_contable_id || '';
            document.getElementById('cc_search').value = data.cuenta_contable_nombre ? (data.cuenta_contable_codigo + ' - ' + data.cuenta_contable_nombre) : '';
            document.getElementById('modalCuenta').classList.add('active');
        }

        async function saveCuenta(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('api_cuentas_bancarias.php?action=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const res = await response.json();
                if (res.success) {
                    Swal.fire('Éxito', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.error, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Ocurrió un error al procesar la solicitud', 'error');
            }
        }

        async function deleteCuenta(id) {
            const result = await Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#dc2626',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`api_cuentas_bancarias.php?action=delete&id=${id}`);
                    const res = await response.json();
                    if (res.success) {
                        Swal.fire('Eliminado', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.error, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Ocurrió un error al eliminar', 'error');
                }
            }
        }

        // Search in table
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('cuentasTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const tdName = tr[i].getElementsByTagName('td')[0];
                const tdBanco = tr[i].getElementsByTagName('td')[1];
                if (tdName || tdBanco) {
                    const txtValue = (tdName.textContent || tdName.innerText) + ' ' + (tdBanco.textContent || tdBanco.innerText);
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        // Autocomplete for Cuenta Contable
        const ccSearch = document.getElementById('cc_search');
        const ccResults = document.getElementById('cc_results');
        const ccId = document.getElementById('cuenta_contable_id');

        ccSearch.addEventListener('input', async (e) => {
            const val = e.target.value;
            if (val.length < 2) {
                ccResults.innerHTML = '';
                ccResults.classList.remove('active');
                return;
            }

            try {
                const response = await fetch(`api_cuentas_bancarias.php?action=search_cuenta_contable&q=${val}`);
                const res = await response.json();
                if (res.success && res.data.length > 0) {
                    ccResults.innerHTML = res.data.map(item => `
                        <div class="autocomplete-item" onclick="selectCuentaContable(${item.id}, '${item.codigo} - ${item.nombre}')">
                            <strong>${item.codigo}</strong> - ${item.nombre}
                        </div>
                    `).join('');
                    ccResults.classList.add('active');
                } else {
                    ccResults.innerHTML = '<div class="autocomplete-item">No se encontraron cuentas</div>';
                    ccResults.classList.add('active');
                }
            } catch (error) {
                console.error(error);
            }
        });

        function selectCuentaContable(id, text) {
            ccId.value = id;
            ccSearch.value = text;
            ccResults.innerHTML = '';
            ccResults.classList.remove('active');
        }

        // Close results when clicking outside
        document.addEventListener('click', (e) => {
            if (!ccSearch.contains(e.target)) {
                ccResults.classList.remove('active');
            }
        });
    </script>
</body>

</html>