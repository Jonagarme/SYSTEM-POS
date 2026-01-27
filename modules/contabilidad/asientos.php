<?php
/**
 * Gestión de Asientos Contables - Accounting Module
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'contabilidad_asientos';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asientos Contables | Warehouse POS</title>
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

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
            padding: 15px 20px;
            font-size: 0.9rem;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }

        .bg-green {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #d1fae5;
        }

        .bg-red {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fee2e2;
        }

        /* Modal Refinement */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 20px;
            width: 95%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalIn 0.3s ease-out;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            background: #fafafa;
            border-radius: 20px 20px 0 0;
        }

        .modal-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .modal-close {
            background: #f1f5f9;
            border: none;
            font-size: 1.25rem;
            color: #64748b;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: #fafafa;
            border-radius: 0 0 20px 20px;
        }

        .btn-modal {
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-cancel {
            background: white;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .btn-cancel:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .btn-submit {
            background: #2563eb;
            color: white;
        }

        .btn-submit:hover {
            background: #1d4ed8;
        }

        /* Form Styling within Modal */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
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
            background-color: #ffffff;
            transition: all 0.2s;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background-color: #fff;
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

        /* Asiento Detail Table */
        .asiento-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .asiento-table th {
            background: #f8fafc;
            padding: 12px 15px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
        }

        .asiento-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            background: white;
        }

        .asiento-table tr:last-child td {
            border-bottom: none;
        }

        .asiento-table input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: border-color 0.2s;
        }

        .asiento-table input:focus {
            outline: none;
            border-color: #2563eb;
            ring: 2px rgba(37, 99, 235, 0.1);
        }

        .asiento-table input[type="number"] {
            text-align: right;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.8rem;
            margin: auto;
        }

        .btn-icon:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fee2e2;
            transform: rotate(90deg);
        }

        .btn-icon i {
            pointer-events: none;
        }

        .btn-add-line {
            margin-top: 20px;
            background: #eff6ff;
            color: #2563eb;
            border: 1px dashed #bfdbfe;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.85rem;
            width: 100%;
            transition: all 0.2s;
        }

        .btn-add-line:hover {
            background: #dbeafe;
            border-style: solid;
        }

        .totals-row {
            margin-top: 25px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .total-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .total-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .total-amount {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
        }

        .account-search-results {
            position: absolute;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1100;
            max-height: 250px;
            overflow-y: auto;
            width: 400px;
            display: none;
        }

        .account-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .account-item:last-child {
            border-bottom: none;
        }

        .account-item:hover {
            background: #f8fafc;
            color: #2563eb;
        }

        .account-item b {
            color: #1e293b;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <?php $root = '../../';
        include $root . 'includes/sidebar.php'; ?>
        <main class="main-content">
            <?php include $root . 'includes/navbar.php'; ?>
            <div class="content-wrapper">
                <div class="accounting-header">
                    <h1><i class="fas fa-book"></i> Asientos Contables</h1>
                    <button class="btn-accounting btn-primary" onclick="openAsientoModal()"><i class="fas fa-plus"></i>
                        Nuevo Asiento</button>
                </div>

                <div class="table-container">
                    <table class="acc-table" id="tablaAsientos">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Debe</th>
                                <th>Haber</th>
                                <th>Estado</th>
                                <th style="text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="listaAsientos">
                            <!-- Se carga vía AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Nuevo/Editar Asiento -->
    <div class="modal-overlay" id="modalAsiento">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Asiento Contable</h2>
                <button class="modal-close" onclick="closeAsientoModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formAsiento">
                    <input type="hidden" name="id" id="asiento_id">
                    <div class="form-row">
                        <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" id="asiento_fecha"
                                value="<?php echo date('Y-m-d'); ?>" required></div>
                        <div class="form-group"><label>Tipo de Asiento</label>
                            <select name="tipo" id="asiento_tipo">
                                <option value="DIARIO">Diario</option>
                                <option value="INGRESO">Ingreso</option>
                                <option value="EGRESO">Egreso</option>
                                <option value="AJUSTE">Ajuste</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label>Concepto General *</label><input type="text" name="concepto"
                            id="asiento_concepto" placeholder="Ej: Pago de servicios básicos" required></div>
                    <div class="form-group"><label>Referencia</label><input type="text" name="referencia"
                            id="asiento_referencia" placeholder="Opcional"></div>

                    <h3 style="margin-top:30px; font-size: 1rem; color: #475569;"><i class="fas fa-list"></i> Detalle
                        del Asiento</h3>
                    <table class="asiento-table">
                        <thead>
                            <tr>
                                <th style="width: 350px;">Cuenta Contable</th>
                                <th>Concepto Detalle</th>
                                <th style="width: 120px;">Debe</th>
                                <th style="width: 120px;">Haber</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="detalleAsiento">
                            <!-- Líneas de detalle -->
                        </tbody>
                    </table>
                    <button type="button" class="btn-add-line" onclick="addAsientoLine()"><i class="fas fa-plus"></i>
                        Añadir Línea</button>

                    <div class="totals-row">
                        <div class="total-item">
                            <span class="total-label">Total Debe</span>
                            <span id="total_debe" class="total-amount">$0.00</span>
                        </div>
                        <div class="total-item">
                            <span class="total-label">Total Haber</span>
                            <span id="total_haber" class="total-amount">$0.00</span>
                        </div>
                        <div id="status_cuadrado"
                            style="display: flex; align-items: center; justify-content: flex-end;"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeAsientoModal()">Cancelar</button>
                <button type="button" class="btn-modal btn-submit" onclick="saveAsiento()">Guardar Asiento</button>
            </div>
        </div>
    </div>

    <!-- Buscador de cuentas flotante (clonable) -->
    <div id="accountSearchResults" class="account-search-results"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let editMode = false;

        document.addEventListener('DOMContentLoaded', loadAsientos);

        async function loadAsientos() {
            const res = await fetch('api_asientos.php?action=list');
            const r = await res.json();
            const container = document.getElementById('listaAsientos');
            container.innerHTML = '';

            r.data.forEach(a => {
                const status = a.cuadrado == 1 ? '<span class="status-badge bg-green">Cuadrado</span>' : '<span class="status-badge bg-red">Descuadrado</span>';
                container.innerHTML += `
                    <tr>
                        <td style="font-weight:700;">${a.numero}</td>
                        <td>${new Date(a.fecha).toLocaleDateString()}</td>
                        <td>${a.concepto}</td>
                        <td style="font-weight:600;">$${parseFloat(a.total_debe).toFixed(2)}</td>
                        <td style="font-weight:600;">$${parseFloat(a.total_haber).toFixed(2)}</td>
                        <td>${status}</td>
                        <td style="text-align:right;">
                            <button class="btn-icon" onclick="editAsiento(${a.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn-icon" style="color:#dc2626" onclick="deleteAsiento(${a.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        }

        function openAsientoModal() {
            editMode = false;
            document.getElementById('modalTitle').textContent = 'Nuevo Asiento Contable';
            document.getElementById('formAsiento').reset();
            document.getElementById('asiento_id').value = '';
            document.getElementById('detalleAsiento').innerHTML = '';
            addAsientoLine();
            addAsientoLine();
            document.getElementById('modalAsiento').classList.add('active');
        }

        function closeAsientoModal() {
            document.getElementById('modalAsiento').classList.remove('active');
        }

        function addAsientoLine(data = null) {
            const tbody = document.getElementById('detalleAsiento');
            const tr = document.createElement('tr');
            tr.className = 'asiento-row';
            tr.innerHTML = `
                <td style="position:relative;">
                    <input type="hidden" class="cuenta-id" value="${data ? data.cuenta_id : ''}">
                    <input type="text" class="form-control account-input" placeholder="Buscar cuenta: 1.1..." value="${data ? data.codigo + ' - ' + data.cuenta_nombre : ''}" onkeyup="searchAccounts(this)">
                </td>
                <td><input type="text" class="line-concepto" value="${data ? data.concepto : ''}"></td>
                <td><input type="number" class="line-debe" step="0.01" value="${data ? data.debe : '0.00'}" onchange="calculateTotals()"></td>
                <td><input type="number" class="line-haber" step="0.01" value="${data ? data.haber : '0.00'}" onchange="calculateTotals()"></td>
                <td><button type="button" class="btn-icon" onclick="this.closest('tr').remove(); calculateTotals();"><i class="fas fa-times"></i></button></td>
            `;
            tbody.appendChild(tr);
            calculateTotals();
        }

        function calculateTotals() {
            let debe = 0; let haber = 0;
            document.querySelectorAll('.line-debe').forEach(i => debe += parseFloat(i.value || 0));
            document.querySelectorAll('.line-haber').forEach(i => haber += parseFloat(i.value || 0));

            document.getElementById('total_debe').textContent = '$' + debe.toFixed(2);
            document.getElementById('total_haber').textContent = '$' + haber.toFixed(2);

            const diff = Math.abs(debe - haber);
            const status = document.getElementById('status_cuadrado');
            if (diff < 0.001 && (debe > 0 || haber > 0)) {
                status.innerHTML = '<span class="status-badge bg-green" style="font-size: 0.9rem;"><i class="fas fa-check-circle"></i> Asiento Cuadrado</span>';
            } else {
                status.innerHTML = '<span class="status-badge bg-red" style="font-size: 0.9rem;"><i class="fas fa-exclamation-triangle"></i> Descuadre: $' + diff.toFixed(2) + '</span>';
            }
        }

        let searchTimeout;
        function searchAccounts(input) {
            clearTimeout(searchTimeout);
            const q = input.value;
            const resultsDiv = document.getElementById('accountSearchResults');

            if (q.length < 2) { resultsDiv.style.display = 'none'; return; }

            searchTimeout = setTimeout(async () => {
                const res = await fetch('api_asientos.php?action=search_accounts&q=' + q);
                const r = await res.json();

                resultsDiv.innerHTML = '';
                r.data.forEach(acc => {
                    const div = document.createElement('div');
                    div.className = 'account-item';
                    div.innerHTML = `<b>${acc.codigo}</b><span style="color:#64748b; font-size: 0.75rem;">${acc.nombre}</span>`;
                    div.onclick = () => {
                        const row = input.closest('tr');
                        row.querySelector('.cuenta-id').value = acc.id;
                        input.value = acc.codigo + ' - ' + acc.nombre;
                        resultsDiv.style.display = 'none';
                    };
                    resultsDiv.appendChild(div);
                });

                const rect = input.getBoundingClientRect();
                resultsDiv.style.top = (rect.bottom + window.scrollY) + 'px';
                resultsDiv.style.left = rect.left + 'px';
                resultsDiv.style.display = 'block';
            }, 300);
        }

        async function saveAsiento() {
            const formData = {
                id: document.getElementById('asiento_id').value,
                fecha: document.getElementById('asiento_fecha').value,
                tipo: document.getElementById('asiento_tipo').value,
                concepto: document.getElementById('asiento_concepto').value,
                referencia: document.getElementById('asiento_referencia').value,
                detalles: []
            };

            let valid = true;
            document.querySelectorAll('.asiento-row').forEach(row => {
                const c_id = row.querySelector('.cuenta-id').value;
                const debe = parseFloat(row.querySelector('.line-debe').value || 0);
                const haber = parseFloat(row.querySelector('.line-haber').value || 0);

                if (!c_id) { valid = false; }
                if (debe > 0 || haber > 0) {
                    formData.detalles.push({
                        cuenta_id: c_id,
                        concepto: row.querySelector('.line-concepto').value,
                        debe: debe,
                        haber: haber
                    });
                }
            });

            if (!valid || formData.detalles.length < 2) {
                Swal.fire('Error', 'Selecciona cuentas válidas y al menos 2 líneas de movimiento', 'error');
                return;
            }

            const res = await fetch('api_asientos.php?action=save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const r = await res.json();

            if (r.success) {
                Swal.fire('¡Guardado!', r.message, 'success');
                closeAsientoModal();
                loadAsientos();
            } else {
                Swal.fire('Error', r.error, 'error');
            }
        }

        async function editAsiento(id) {
            const res = await fetch('api_asientos.php?action=get&id=' + id);
            const r = await res.json();
            const a = r.data;

            editMode = true;
            document.getElementById('modalTitle').textContent = 'Editar Asiento ' + a.numero;
            document.getElementById('asiento_id').value = a.id;
            document.getElementById('asiento_fecha').value = a.fecha;
            document.getElementById('asiento_tipo').value = a.tipo;
            document.getElementById('asiento_concepto').value = a.concepto;
            document.getElementById('asiento_referencia').value = a.referencia;

            const tbody = document.getElementById('detalleAsiento');
            tbody.innerHTML = '';
            a.detalles.forEach(d => addAsientoLine(d));

            document.getElementById('modalAsiento').classList.add('active');
        }

        async function deleteAsiento(id) {
            const result = await Swal.fire({
                title: '¿Eliminar asiento?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626'
            });

            if (result.isConfirmed) {
                const res = await fetch('api_asientos.php?action=delete&id=' + id);
                const r = await res.json();
                if (r.success) {
                    Swal.fire('Eliminado', r.message, 'success');
                    loadAsientos();
                } else {
                    Swal.fire('Error', r.error, 'error');
                }
            }
        }

        // Cerrar buscador al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!e.target.classList.contains('account-input')) {
                document.getElementById('accountSearchResults').style.display = 'none';
            }
        });
    </script>
</body>

</html>