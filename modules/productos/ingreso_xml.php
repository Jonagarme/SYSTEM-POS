<?php
/**
 * XML/SRI Product Entry Module - Professional Version
 */
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso de Productos | XML SRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/ingreso_xml.css">
    <style>
        .provider-bubble {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 1rem 1.5rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .provider-bubble strong {
            display: block;
            font-size: 1.1rem;
        }

        .provider-bubble small {
            opacity: 0.8;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
        }

        .progress-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 5px solid #f1f5f9;
            border-top-color: #6366f1;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .item-config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .badge-match {
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .badge-match:hover {
            background: #fee2e2;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <?php
        $root = '../../';
        $current_page = 'producto_xml';
        include $root . 'includes/sidebar.php';
        ?>

        <main class="main-content">
            <?php include $root . 'includes/navbar.php'; ?>

            <!-- Header Principal -->
            <div class="ingreso-header">
                <div class="container-fluid" style="padding: 0 40px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h1 style="font-size: 2.2rem; font-weight: 800; margin: 0;">
                                <i class="fas fa-file-invoice" style="margin-right: 15px; opacity: 0.8;"></i>Ingreso de
                                Productos
                            </h1>
                            <p style="margin: 5px 0 0; opacity: 0.9; font-size: 1.1rem;">Importación inteligente desde
                                facturas SRI</p>
                        </div>
                        <div style="text-align: right;">
                            <div
                                style="background: white; color: #4f46e5; padding: 10px 20px; border-radius: 50px; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                <i class="fas fa-microchip" style="margin-right: 10px;"></i>Procesamiento XML v2.0
                            </div>
                        </div>
                    </div>

                    <div id="provider-info-header" style="display: none;">
                        <div class="provider-bubble">
                            <div><small>Proveedor</small><strong id="h-prov-name">-</strong></div>
                            <div><small>RUC</small><strong id="h-prov-ruc">-</strong></div>
                            <div><small>Fecha Emisión</small><strong id="h-prov-date">-</strong></div>
                            <div><small>Total Factura</small><strong style="color: #fbbf24;" id="h-prov-total">$
                                    0.00</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid" style="padding: 0 40px; margin-top: -20px;">
                <!-- Navegación por Tabs -->
                <div class="nav-tabs-premium">
                    <button class="nav-link-premium active" id="tab-1-btn">
                        <i class="fas fa-upload" style="margin-right: 10px;"></i>1. Cargar Factura
                    </button>
                    <button class="nav-link-premium" id="tab-2-btn" disabled>
                        <i class="fas fa-tasks" style="margin-right: 10px;"></i>2. Revisar e Inventariar
                    </button>
                    <button class="nav-link-premium" id="tab-3-btn" disabled>
                        <i class="fas fa-check-double" style="margin-right: 10px;"></i>3. Resultado Final
                    </button>
                </div>

                <!-- CONTENIDO TABS -->
                <div class="tab-content-container">

                    <!-- TAB 1: CARGA -->
                    <div id="tab-1-content" class="tab-pane-view">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                            <div class="import-card">
                                <div class="card-header-premium card-header-primary">
                                    <i class="fas fa-file-code"
                                        style="font-size: 2.5rem; margin-bottom: 10px; display:block;"></i>
                                    <h3 style="margin:0;">Archivo XML</h3>
                                    <p style="margin:0; opacity:0.8;">Cargue el archivo .xml de su proveedor</p>
                                </div>
                                <div style="padding: 30px;">
                                    <div class="file-upload-area" id="drop-zone">
                                        <i class="fas fa-cloud-upload-alt"
                                            style="font-size: 3rem; color: #6366f1; margin-bottom: 15px;"></i>
                                        <h4 style="margin:0 0 10px;">Suelte el archivo aquí</h4>
                                        <p style="color: #64748b; margin-bottom: 20px;">Formatos soportados: .xml</p>
                                        <input type="file" id="xml-input" hidden accept=".xml">
                                        <button class="btn-premium btn-premium-primary"
                                            onclick="document.getElementById('xml-input').click()">
                                            <i class="fas fa-search" style="margin-right: 8px;"></i>Examinar Archivos
                                        </button>
                                    </div>
                                    <div id="file-selected-name" style="margin-top: 15px; display:none;">
                                        <div
                                            style="background: #f0fdf4; border: 1px solid #bbfcbd; color: #166534; padding: 12px; border-radius: 12px; display:flex; align-items:center;">
                                            <i class="fas fa-file-check"
                                                style="margin-right: 10px; font-size: 1.2rem;"></i>
                                            <span id="txt-file-name" style="font-weight: 600;">archivo.xml</span>
                                        </div>
                                    </div>
                                    <button class="btn-premium btn-premium-success" id="btn-process-xml" disabled
                                        style="width: 100%; margin-top: 20px; font-size: 1.1rem; padding: 15px;">
                                        <i class="fas fa-magic" style="margin-right: 10px;"></i>Comenzar Procesamiento
                                    </button>
                                </div>
                            </div>

                            <div class="import-card">
                                <div class="card-header-premium card-header-info">
                                    <i class="fas fa-key"
                                        style="font-size: 2.5rem; margin-bottom: 10px; display:block;"></i>
                                    <h3 style="margin:0;">Clave de Acceso</h3>
                                    <p style="margin:0; opacity:0.8;">Consulte directamente del SRI</p>
                                </div>
                                <div style="padding: 30px;">
                                    <div style="margin-bottom: 20px;">
                                        <label
                                            style="display:block; font-weight: 600; margin-bottom: 8px; color: #475569;">Número
                                            de Comprobante (49 dígitos)</label>
                                        <input type="text" class="form-control clave-acceso-input"
                                            placeholder="0000000000000000000000000000000000000000000000000"
                                            maxlength="49">
                                    </div>
                                    <button class="btn-premium btn-premium-primary" id="btn-query-sri"
                                        style="width: 100%; font-size: 1.1rem; padding: 15px;">
                                        <i class="fas fa-globe" style="margin-right: 10px;"></i>Consultar Base SRI
                                    </button>
                                    <div
                                        style="margin-top: 20px; padding: 15px; background: #fffbeb; border-radius: 12px; border: 1px solid #fde68a;">
                                        <p style="margin:0; font-size: 0.85rem; color: #92400e;">
                                            <i class="fas fa-info-circle" style="margin-right: 8px;"></i>El sistema
                                            extraerá automáticamente el proveedor y los productos autorizados.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: REVISION -->
                    <div id="tab-2-content" class="tab-pane-view" style="display:none;">
                        <!-- Loading Section -->
                        <div id="loading-verify" style="padding: 60px 0; text-align: center;">
                            <div class="progress-circle"></div>
                            <h3 style="color: #4f46e5; font-weight: 700;">Verificando duplicados...</h3>
                            <p style="color: #64748b;">Estamos cruzando la información del XML con su inventario actual
                                para evitar registros dobles.</p>
                            <div
                                style="max-width: 500px; margin: 30px auto; background: #e2e8f0; height: 10px; border-radius: 50px; overflow:hidden;">
                                <div id="verify-progress-bar"
                                    style="width: 0%; height: 100%; background: #6366f1; transition: width 0.3s;"></div>
                            </div>
                        </div>

                        <!-- Data Section -->
                        <div id="review-data" style="display:none;">
                            <!-- Estadísticas Rápidas -->
                            <div
                                style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
                                <div class="stats-box">
                                    <span class="stats-val" id="stat-total-items">0</span>
                                    <span class="stats-label">Productos Hallados</span>
                                </div>
                                <div class="stats-box">
                                    <span class="stats-val" id="stat-new-items" style="color: #059669;">0</span>
                                    <span class="stats-label">Nuevos para Sistema</span>
                                </div>
                                <div class="stats-box">
                                    <span class="stats-val" id="stat-match-items" style="color: #ef4444;">0</span>
                                    <span class="stats-label">Posibles Duplicados</span>
                                </div>
                                <div class="stats-box" style="background: #4f46e5;">
                                    <span class="stats-val" id="stat-total-price" style="color: white;">$ 0.00</span>
                                    <span class="stats-label" style="color: rgba(255,255,255,0.7);">Importe Total
                                        Bruto</span>
                                </div>
                            </div>

                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 style="font-weight: 700; color: #1e293b; margin:0;"><i class="fas fa-list-check"
                                        style="margin-right: 12px;"></i>Detalle de Productos</h3>
                                <div style="display:flex; gap: 10px;">
                                    <button class="btn btn-outline-primary" id="btn-select-all"><i
                                            class="fas fa-check-double"></i> Seleccionar Todo</button>
                                    <button class="btn btn-outline-secondary" id="btn-deselect-all"><i
                                            class="fas fa-times"></i> Deseleccionar</button>
                                </div>
                            </div>

                            <div id="products-list-review">
                                <!-- Dinámico -->
                            </div>

                            <!-- Validación de Totales -->
                            <div style="background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-top: 40px; border: 2px solid #f1f5f9;"
                                id="footer-validation">
                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr 1.5fr; gap: 40px; align-items: center;">
                                    <div style="text-align: center; border-right: 1px solid #f1f5f9;">
                                        <small
                                            style="color: #64748b; text-transform: uppercase; font-weight: 600;">Total
                                            Factura de XML</small>
                                        <h2 style="margin:0; font-weight: 800; color: #1e293b;" id="v-total-xml">$ 0.00
                                        </h2>
                                    </div>
                                    <div style="text-align: center; border-right: 1px solid #f1f5f9;">
                                        <small
                                            style="color: #64748b; text-transform: uppercase; font-weight: 600;">Total a
                                            Ingresar</small>
                                        <h2 style="margin:0; font-weight: 800; color: #6366f1;" id="v-total-calc">$ 0.00
                                        </h2>
                                    </div>
                                    <div id="validation-alert"
                                        style="padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 15px;">
                                        <i class="fas fa-info-circle" style="font-size: 1.5rem;"></i>
                                        <div style="flex:1;">
                                            <strong id="v-msg-title">Calculando...</strong><br>
                                            <small id="v-msg-desc">Compare que el total coincida para proceder.</small>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px;">
                                    <button class="btn btn-secondary btn-premium" onclick="location.reload()">Cancelar
                                        Operación</button>
                                    <button class="btn-premium btn-premium-primary" id="btn-finalize-import"
                                        style="padding: 15px 50px; font-size: 1.1rem;" disabled>
                                        <i class="fas fa-save" style="margin-right: 10px;"></i>Procesar e Ingresar a
                                        Inventario
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: RESULTADO -->
                    <div id="tab-3-content" class="tab-pane-view"
                        style="display:none; text-align: center; padding: 80px 0;">
                        <div
                            style="width: 120px; height: 120px; background: #dcfce7; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 4rem; margin: 0 auto 30px;">
                            <i class="fas fa-check"></i>
                        </div>
                        <h1 style="font-weight: 800; color: #1e293b; margin-bottom: 10px;">¡Ingreso Exitoso!</h1>
                        <p style="color: #64748b; font-size: 1.2rem; max-width: 600px; margin: 0 auto 40px;">Se han
                            procesado correctamente los productos seleccionados y actualizado el Kardex del
                            establecimiento.</p>

                        <div style="display: flex; gap: 20px; justify-content: center;">
                            <button class="btn-premium btn-premium-primary" onclick="location.reload()">
                                <i class="fas fa-plus-circle" style="margin-right: 10px;"></i>Nuevo Ingreso
                            </button>
                            <a href="index.php" class="btn btn-outline-primary btn-premium">
                                <i class="fas fa-boxes" style="margin-right: 10px;"></i>Ir al Inventario
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>

    <script>
        let xmlProducts = [];
        let providerInfo = {};
        let totalImporteSinImp = 0;
        let totalImporteConImp = 0;

        // UI Selectors
        const xmlInput = document.getElementById('xml-input');
        const btnProcessXML = document.getElementById('btn-process-xml');
        const txtFileName = document.getElementById('txt-file-name');
        const fileDiv = document.getElementById('file-selected-name');
        const dropZone = document.getElementById('drop-zone');

        // Logic 1: File Selection
        xmlInput.onchange = function (e) {
            if (this.files.length) {
                const file = this.files[0];
                txtFileName.textContent = file.name;
                fileDiv.style.display = 'block';
                btnProcessXML.disabled = false;
            }
        };

        // Drag & Drop
        dropZone.ondragover = (e) => { e.preventDefault(); dropZone.classList.add('dragover'); };
        dropZone.ondragleave = () => dropZone.classList.remove('dragover');
        dropZone.ondrop = (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                xmlInput.files = e.dataTransfer.files;
                xmlInput.onchange();
            }
        };

        // Action: Process XML
        btnProcessXML.onclick = function () {
            const file = xmlInput.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                parseXML(e.target.result);
            };
            reader.readAsText(file);
        };

        function parseXML(content) {
            const parser = new DOMParser();
            let xmlDoc = parser.parseFromString(content, "text/xml");

            // Check for valid XML parsing
            if (xmlDoc.getElementsByTagName("parsererror").length > 0) {
                console.error("XML Parser Error:", xmlDoc.getElementsByTagName("parsererror")[0].textContent);
                alert("Error: El archivo XML tiene un formato inválido o está corrupto.");
                return;
            }

            // 0. Handle SRI Wrapper (comprobante inside autorizacion)
            // Many SRI XMLs wrap the actual invoice inside a <comprobante> tag (sometimes as CDATA/escaped string)
            let comprobanteNode = xmlDoc.getElementsByTagName('comprobante')[0];
            if (comprobanteNode) {
                let innerXml = comprobanteNode.textContent;
                if (innerXml.trim().startsWith('<' + '?xml') || innerXml.trim().startsWith('<')) {
                    const innerDoc = parser.parseFromString(innerXml, "text/xml");
                    if (innerDoc.getElementsByTagName("parsererror").length === 0) {
                        xmlDoc = innerDoc;
                    }
                }
            }

            // Helper to get text by tag name (safe for namespaces)
            const getTagText = (parent, tag) => {
                const el = parent.getElementsByTagName(tag)[0];
                return el ? el.textContent : null;
            };

            const infoTrib = xmlDoc.getElementsByTagName('infoTributaria')[0];
            const infoFact = xmlDoc.getElementsByTagName('infoFactura')[0];

            if (!infoTrib || !infoFact) {
                console.log("XML Structure mismatch:", xmlDoc);
                alert("Error: El archivo no parece ser una factura electrónica válida del SRI. Verifique que sea una Factura (no Nota de Crédito, etc).");
                return;
            }

            // 1. Provider Info
            providerInfo = {
                ruc: getTagText(infoTrib, 'ruc'),
                razonSocial: getTagText(infoTrib, 'razonSocial'),
                matriz: getTagText(infoTrib, 'dirMatriz')
            };

            // 2. Invoice Info
            const fecha = getTagText(infoFact, 'fechaEmision');
            totalImporteSinImp = parseFloat(getTagText(infoFact, 'totalSinImpuestos') || 0);
            totalImporteConImp = parseFloat(getTagText(infoFact, 'importeTotal') || 0);

            // 3. Products
            const items = xmlDoc.getElementsByTagName('detalle');
            xmlProducts = [];
            for (let node of items) {
                xmlProducts.push({
                    codigo: getTagText(node, 'codigoPrincipal') || 'S/N',
                    nombre: getTagText(node, 'descripcion') || 'Sin Nombre',
                    cantidad: parseFloat(getTagText(node, 'cantidad') || 0),
                    costo: parseFloat(getTagText(node, 'precioUnitario') || 0),
                    totalItem: parseFloat(getTagText(node, 'precioTotalSinImpuesto') || 0),
                    isNew: true,
                    match: null
                });
            }

            // Update Header
            document.getElementById('h-prov-name').textContent = providerInfo.razonSocial;
            document.getElementById('h-prov-ruc').textContent = providerInfo.ruc;
            document.getElementById('h-prov-date').textContent = fecha;
            document.getElementById('h-prov-total').textContent = `$ ${totalImporteConImp.toFixed(2)}`;
            document.getElementById('provider-info-header').style.display = 'block';

            // Switch to Tab 2
            switchTab(2);
            startVerification();
        }

        // Logic 2: SRI Query
        document.getElementById('btn-query-sri').onclick = function () {
            const clave = document.querySelector('.clave-acceso-input').value;
            if (clave.length !== 49) {
                alert("La clave de acceso debe tener 49 dígitos.");
                return;
            }
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Consultando...';

            setTimeout(() => {
                alert("SRI: Conectado. Extrayendo comprobante...");
                // Aquí se llamaría a un servicio backend que use cURL para consultar el SRI
                // Simulamos éxito si la clave termina en 1
                this.innerHTML = '<i class="fas fa-globe"></i> Consultar Base SRI';
                this.disabled = false;
                alert("Simulación: Comprobante obtenido. Procesando datos...");
                // Simularíamos cargar un XML
            }, 1500);
        };

        function switchTab(num) {
            document.querySelectorAll('.tab-pane-view').forEach(t => t.style.display = 'none');
            document.querySelectorAll('.nav-link-premium').forEach(l => {
                l.classList.remove('active');
                l.disabled = false;
            });

            document.getElementById(`tab-${num}-content`).style.display = 'block';
            document.getElementById(`tab-${num}-btn`).classList.add('active');
        }

        /**
         * Sistema de Detección de Productos Duplicados (Refactorizado)
         */
        class DetectorDuplicados {
            constructor() {
                this.productoActual = null;
                this.productosSimilares = [];
                this.idxActual = -1;
                this.umbralSimilitud = 0.75;
            }

            async verificarLote(productos) {
                try {
                    const response = await fetch('api_duplicados.php?action=lote', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ productos: productos, umbral: this.umbralSimilitud })
                    });
                    const data = await response.json();
                    return data.success ? data.resultados : null;
                } catch (e) { console.error("Error en verificación por lote:", e); return null; }
            }

            async vincularCodigo(productoId, codigo, nombre) {
                try {
                    const response = await fetch('api_duplicados.php?action=vincular', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ producto_id: productoId, codigo: codigo, nombre_proveedor: nombre })
                    });
                    return await response.json();
                } catch (e) { return { success: false, error: e.message }; }
            }
        }

        const detector = new DetectorDuplicados();

        async function startVerification() {
            const progress = document.getElementById('verify-progress-bar');

            // Prepare data for batch processing
            const batchData = xmlProducts.map(p => ({ nombre: p.nombre, codigo: p.codigo }));

            // Show some fake movement
            progress.style.width = '30%';

            const resultados = await detector.verificarLote(batchData);
            progress.style.width = '100%';

            if (resultados) {
                let matchesCount = 0;
                resultados.forEach((res, i) => {
                    if (res.tiene_similares) {
                        xmlProducts[i].matches = res.similares;
                        xmlProducts[i].selectedMatch = res.similares[0];
                        xmlProducts[i].isNew = false;
                        matchesCount++;
                    } else {
                        xmlProducts[i].isNew = true;
                    }
                });

                setTimeout(() => {
                    document.getElementById('loading-verify').style.display = 'none';
                    document.getElementById('review-data').style.display = 'block';
                    document.getElementById('stat-total-items').textContent = xmlProducts.length;
                    document.getElementById('stat-new-items').textContent = xmlProducts.length - matchesCount;
                    document.getElementById('stat-match-items').textContent = matchesCount;
                    document.getElementById('stat-total-price').textContent = `$ ${totalImporteSinImp.toFixed(2)}`;
                    document.getElementById('v-total-xml').textContent = `$ ${totalImporteSinImp.toFixed(2)}`;
                    renderReviewList();
                }, 500);
            }
        }

        function openMatchModal(idx) {
            currentMatchingIdx = idx;
            const p = xmlProducts[idx];

            document.getElementById('m-factura-codigo').textContent = p.codigo;
            document.getElementById('m-factura-nombre').textContent = p.nombre;
            document.getElementById('m-factura-cantidad').textContent = p.cantidad;
            document.getElementById('m-factura-precio').textContent = `$ ${p.costo.toFixed(2)}`;

            const listCont = document.getElementById('match-list-container');
            listCont.innerHTML = `
                <div class="match-option new-opt ${p.isNew ? 'selected' : ''}" onclick="selectMatch(-1)">
                    <div style="flex:1;">
                        <i class="fas fa-sparkles" style="color: #fbbf24; margin-right: 10px;"></i> 
                        <strong>Crear como nuevo producto</strong>
                    </div>
                </div>
            `;

            if (p.matches) {
                p.matches.forEach((m, mIdx) => {
                    const isSelected = !p.isNew && p.selectedMatch && p.selectedMatch.id === m.id;
                    const displayPrecio = parseFloat(m.precio || 0).toFixed(2);
                    listCont.innerHTML += `
                        <div class="match-option ${isSelected ? 'selected' : ''}" onclick="selectMatch(${mIdx})" id="m-opt-${mIdx}">
                            <div style="flex:1;">
                                <strong style="display:block; margin-bottom: 2px;">${m.nombre}</strong>
                                <small style="color: #64748b;">
                                    Stock: ${m.stock} | Precio: $${displayPrecio} | 
                                    <span style="color: #059669; font-weight: 700;">${parseFloat(m.score_total || 0).toFixed(0)}% similar</span>
                                </small>
                            </div>
                        </div>
                    `;
                });
            }

            document.getElementById('modal-duplicados').style.display = 'flex';
        }

        function selectMatch(mIdx) {
            document.querySelectorAll('.match-option').forEach(o => o.classList.remove('selected'));
            const p = xmlProducts[currentMatchingIdx];

            if (mIdx === -1) {
                p.selectedMatch = null;
                p.isNew = true;
                document.querySelector('.new-opt').classList.add('selected');
            } else {
                p.selectedMatch = p.matches[mIdx];
                p.isNew = false;
                document.getElementById(`m-opt-${mIdx}`).classList.add('selected');
            }
        }

        async function confirmarSeleccion() {
            const p = xmlProducts[currentMatchingIdx];
            const checkVincular = document.getElementById('vincular-auto').checked;

            if (!p.isNew && p.selectedMatch) {
                if (checkVincular) {
                    const res = await detector.vincularCodigo(p.selectedMatch.id, p.codigo, p.nombre);
                    if (res.success) {
                        alert("¡Vinculación exitosa! El código " + p.codigo + " ahora está asociado a este producto.");
                    }
                }
            }

            closeMatchModal();
            renderReviewList();
        }

        if (document.getElementById('btn-confirm-match')) {
            document.getElementById('btn-confirm-match').onclick = confirmarSeleccion;
        }

        function closeMatchModal() {
            document.getElementById('modal-duplicados').style.display = 'none';
        }

        function renderReviewList() {
            const container = document.getElementById('products-list-review');
            container.innerHTML = '';

            xmlProducts.forEach((p, idx) => {
                const suggestedPVP = (p.costo * 1.3).toFixed(2);
                const html = `
                    <div class="producto-row" id="row-${idx}">
                        <div style="display: flex; gap: 20px; align-items: flex-start;">
                            <input type="checkbox" checked class="item-chk" data-idx="${idx}" style="width: 20px; height: 20px; margin-top: 5px;">
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <div>
                                        <span class="badge-status ${p.isNew ? 'badge-new' : 'badge-existente'}">
                                            ${p.isNew ? '<i class="fas fa-plus"></i> NUEVO' : '<i class="fas fa-link"></i> EXISTENTE'}
                                        </span>
                                        <strong style="margin-left: 10px; font-size: 1.1rem; color: #1e293b;">
                                            ${p.isNew ? p.nombre : (p.selectedMatch ? p.selectedMatch.nombre : p.nombre)}
                                        </strong>
                                    </div>
                                    <div style="text-align: right;">
                                        <small style="color: #64748b;">Código: ${p.codigo}</small>
                                        ${p.matches && p.matches.length > 0 ?
                        `<div class="badge-match" onclick="openMatchModal(${idx})"><i class="fas fa-plug"></i> Vincular / Cambiar</div>`
                        : ''}
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 30px; font-size: 0.95rem;">
                                    <div class="price-card">
                                        <small style="display:block; color:#64748b;">Cant. Facturada</small>
                                        <strong style="color: #6366f1; font-size: 1.1rem;">${p.cantidad}</strong>
                                    </div>
                                    <div class="price-card">
                                        <small style="display:block; color:#64748b;">Costo XML</small>
                                        <strong>$ ${p.costo.toFixed(2)}</strong>
                                    </div>
                                    <div class="price-card">
                                        <small style="display:block; color:#64748b;">Subtotal</small>
                                        <strong>$ ${p.totalItem.toFixed(2)}</strong>
                                    </div>
                                    <div class="price-card" style="background: #eef2ff; border: 1px solid #e0e7ff;">
                                        <small style="display:block; color:#4338ca;">PVP Sugerido (30%)</small>
                                        <strong style="color: #4338ca;">$ ${suggestedPVP}</strong>
                                    </div>
                                </div>

                                <div class="item-config-grid">
                                    <div class="form-group">
                                        <label><i class="fas fa-tag"></i> PVP Venta</label>
                                        <input type="number" class="form-control" value="${suggestedPVP}" step="0.01">
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar-times"></i> Caducidad</label>
                                        <input type="date" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-map-marker-alt"></i> Ubicación</label>
                                        <select class="form-control">
                                            <option>Sin ubicación</option>
                                            <option>Percha A-1</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            });

            setupValidation();
        }

        function setupValidation() {
            const checkboxes = document.querySelectorAll('.item-chk');
            const btnFinal = document.getElementById('btn-finalize-import');
            const alertBox = document.getElementById('validation-alert');
            const alertTitle = document.getElementById('v-msg-title');

            const validate = () => {
                let currentCalc = 0;
                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        const idx = cb.dataset.idx;
                        currentCalc += xmlProducts[idx].totalItem;
                    }
                });

                document.getElementById('v-total-calc').textContent = `$ ${currentCalc.toFixed(2)}`;

                const diff = Math.abs(currentCalc - totalImporteSinImp);
                if (diff < 0.05) {
                    alertBox.style.background = '#dcfce7';
                    alertBox.style.color = '#15803d';
                    alertTitle.textContent = "✓ Validación Correcta";
                    btnFinal.disabled = false;
                } else {
                    alertBox.style.background = '#fef2f2';
                    alertBox.style.color = '#b91c1c';
                    alertTitle.textContent = "✗ Diferencia en Totales";
                    btnFinal.disabled = true;
                }
            };

            checkboxes.forEach(cb => cb.onchange = validate);
            validate();
        }

        document.getElementById('btn-select-all').onclick = () => {
            document.querySelectorAll('.item-chk').forEach(c => c.checked = true);
            setupValidation();
        };

        document.getElementById('btn-deselect-all').onclick = () => {
            document.querySelectorAll('.item-chk').forEach(c => c.checked = false);
            setupValidation();
        };

        document.getElementById('btn-finalize-import').onclick = function () {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            // Simular guardado masivo
            setTimeout(() => {
                switchTab(3);
            }, 1000);
        };
    </script>
    <!-- MODAL DUPLICADOS (Premium) -->
    <div id="modal-duplicados" class="modal-premium-overlay" style="display:none;">
        <div class="modal-premium-content" style="max-width: 900px;">
            <div class="modal-premium-header" style="background: #fbbf24; color: #000;">
                <h3 style="margin:0; font-size: 1.2rem; font-weight: 700;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i>Producto Similar Detectado
                </h3>
                <button class="close-modal" onclick="closeMatchModal()">&times;</button>
            </div>

            <div style="padding: 25px;">
                <div
                    style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; padding: 12px 15px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-info-circle" style="font-size: 1.2rem;"></i>
                    <span><strong>Se encontró un producto similar en tu inventario.</strong> Por favor, selecciona qué
                        acción deseas realizar.</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <!-- Columna Izquierda: Producto Factura -->
                    <div>
                        <h4 style="font-size: 0.9rem; color: #6366f1; margin-bottom: 15px; text-transform: uppercase;">
                            <i class="fas fa-file-invoice" style="margin-right: 8px;"></i>Producto en Factura
                        </h4>
                        <div
                            style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9;">
                            <div style="display:grid; grid-template-columns: 80px 1fr; gap: 10px; margin-bottom: 8px;">
                                <span style="font-weight: 600; color: #64748b;">Código:</span> <span
                                    id="m-factura-codigo" style="font-weight: 700;">-</span>
                                <span style="font-weight: 600; color: #64748b;">Nombre:</span> <span
                                    id="m-factura-nombre">-</span>
                                <span style="font-weight: 600; color: #64748b;">Cantidad:</span> <span
                                    id="m-factura-cantidad">-</span>
                                <span style="font-weight: 600; color: #64748b;">Precio:</span> <span
                                    id="m-factura-precio" style="font-weight: 700;">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Productos Similares -->
                    <div>
                        <h4 style="font-size: 0.9rem; color: #059669; margin-bottom: 15px; text-transform: uppercase;">
                            <i class="fas fa-search" style="margin-right: 8px;"></i>Productos Similares en Inventario
                        </h4>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;"><i
                                    class="fas fa-barcode"></i> Buscar con Código de Barras</label>
                            <input type="text" class="form-control" placeholder="Escanea o ingresa código de barras..."
                                style="border-radius: 8px;">
                            <small style="color: #94a3b8; font-size: 0.75rem;"><i class="fas fa-info-circle"></i> Usa el
                                lector de códigos de barras o ingresa manualmente y presiona Enter</small>
                        </div>

                        <p style="font-weight: 600; font-size: 0.85rem; margin: 15px 0 5px;">O selecciona una opción:
                        </p>
                        <div class="match-list-box" id="match-list-container">
                            <!-- Opciones cargadas dinámicamente -->
                        </div>
                    </div>
                </div>

                <div
                    style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="vincular-auto" checked style="width: 18px; height: 18px;">
                    <label for="vincular-auto" style="margin:0; font-weight: 600; font-size: 0.9rem;">
                        <i class="fas fa-link"></i> Vincular este código automáticamente
                    </label>
                </div>
                <small style="display:block; color: #94a3b8; font-size: 0.75rem; margin-left: 28px;">
                    Si activas esta opción, el código de la factura se guardará como código alternativo y las próximas
                    veces se detectará automáticamente.
                </small>
            </div>

            <div class="modal-premium-footer"
                style="background: #f8fafc; padding: 15px 25px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f1f5f9;">
                <button class="btn btn-secondary" onclick="closeMatchModal()"><i class="fas fa-times"></i>
                    Cancelar</button>
                <button class="btn btn-primary" id="btn-confirm-match"
                    style="background: #2563eb; padding: 10px 25px;"><i class="fas fa-check"></i> Confirmar
                    Selección</button>
            </div>
        </div>
    </div>

    <style>
        .modal-premium-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-premium-content {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 95%;
            position: relative;
        }

        .modal-premium-header {
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            opacity: 0.5;
        }

        .match-list-box {
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .match-option {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .match-option:hover {
            background: #f8fafc;
        }

        .match-option.selected {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
        }

        .match-option.new-opt {
            background: #fff;
            color: #6366f1;
            font-weight: 700;
            border-bottom: 2px solid #f1f5f9;
        }
    </style>
</body>

</html>