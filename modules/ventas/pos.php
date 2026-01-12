<?php
/**
 * POS Premium - Point of Sale Interface
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Fetch company data
$stmtEmpresa = $pdo->query("SELECT * FROM empresas LIMIT 1");
$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

$defaults = [
    'id' => null,
    'ruc' => '',
    'razon_social' => 'EMPRESA NO CONFIGURADA',
    'nombre_comercial' => '',
    'direccion_matriz' => 'DIRECCION NO CONFIGURADA',
    'telefono' => '',
    'email' => '',
    'contribuyente_especial' => '',
    'obligado_contabilidad' => 0,
    'sri_ambiente' => 1,
    'logo' => null
];

$empresa = $empresa ? array_merge($defaults, $empresa) : $defaults;

$establishment = $empresa['nombre_comercial'] ?: $empresa['razon_social'];
$environment = ($empresa['sri_ambiente'] == 1) ? "PRUEBAS" : "PRODUCCIÓN";
$user_name = $_SESSION['user_name'] ?? "Usuario Administrador";
$date_now = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta | <?php echo $establishment; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/pos_premium.css">
    <style>
        /* Ticket Thermal Styles */
        .ticket-thermal {
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 5mm;
            background: white;
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            font-size: 12px;
            line-height: 1.2;
        }

        .ticket-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .ticket-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .ticket-logo i {
            font-size: 24px;
            color: #10b981;
        }

        .ticket-logo span {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .ticket-slogan {
            font-style: italic;
            font-size: 10px;
            margin-bottom: 10px;
            display: block;
        }

        .ticket-info {
            font-size: 10px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .ticket-divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .ticket-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
            font-size: 10px;
            padding: 4px 0;
        }

        .ticket-table td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 10px;
        }

        .ticket-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .ticket-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 9px;
        }

        .ticket-qr {
            margin: 10px auto;
            width: 80px;
            height: 80px;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #modal-ticket,
            #modal-ticket * {
                visibility: visible;
            }

            #modal-ticket {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
            }

            .modal-header,
            .modal-footer,
            .btn-print-hide {
                display: none !important;
            }

            .ticket-thermal {
                width: 100%;
                padding: 0;
            }
        }

        /* Confirmation Modal Specifics */
        .confirm-card {
            text-align: center;
            padding: 30px;
        }

        .confirm-icon {
            font-size: 60px;
            color: #6366f1;
            margin-bottom: 20px;
        }

        .confirm-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .confirm-text {
            color: #64748b;
            margin-bottom: 30px;
        }

        .confirm-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <?php
        $root = '../../';
        $current_page = 'venta_pos';
        include $root . 'includes/sidebar.php';
        ?>

        <main class="main-content">
            <div class="pos-container">
                <!-- 1. System Info Header -->
                <header class="pos-system-header">
                    <div class="system-info-item">
                        <button id="toggle-sidebar" class="btn-sidebar-toggle"
                            style="background: rgba(255,255,255,0.1); border: none; color: white; cursor: pointer; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 15px; transition: all 0.2s;">
                            <i class="fas fa-bars"></i>
                        </button>
                        <i class="fas fa-hospital"></i>
                        <span>ESTABLECIMIENTO: <strong><?php echo $establishment; ?></strong></span>
                    </div>
                    <div class="system-info-item">
                        <i class="fas fa-vial"></i>
                        <span>AMBIENTE SRI: <strong style="color: #fbbf24;"><i class="fas fa-edit"></i>
                                <?php echo $environment; ?></strong></span>
                    </div>
                    <div class="system-info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>FECHA DE EMISIÓN: <strong><?php echo $date_now; ?></strong></span>
                    </div>
                    <div class="system-info-item">
                        <i class="fas fa-user-circle"></i>
                        <span>USUARIO: <strong><?php echo $user_name; ?></strong></span>
                    </div>
                </header>

                <!-- 2. Tabs Bar -->
                <div class="pos-tabs-bar">
                    <div class="tab-item active">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Venta 1</span>
                    </div>
                    <button class="btn-new-tab">
                        <i class="fas fa-plus"></i>
                        <span>Nueva Venta</span>
                    </button>
                </div>

                <!-- 3. Main Interface Grid -->
                <div class="pos-main-grid">

                    <!-- LEFT COLUMN: Search & Actions -->
                    <div class="scrollable-panel">

                        <!-- Client Panel -->
                        <div class="pos-panel">
                            <div class="panel-header">
                                <span><i class="fas fa-user-tag"></i> Datos del Cliente</span>
                                <span class="badge-primary"
                                    style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px;">CONSUMIDOR
                                    FINAL</span>
                            </div>
                            <div class="panel-body">
                                <div class="client-search-box">
                                    <div class="input-with-icon" style="flex: 1;">
                                        <i class="fas fa-search"></i>
                                        <input type="text" class="form-control"
                                            placeholder="Buscar cliente por cédula, RUC o nombre..."
                                            style="padding-left: 35px;">
                                    </div>
                                    <button class="btn btn-secondary" title="Limpiar"><i
                                            class="fas fa-times"></i></button>
                                    <button class="btn btn-primary" id="btn-nuevo-cliente" title="Nuevo Cliente"><i
                                            class="fas fa-user-plus"></i></button>
                                </div>
                                <div class="client-info-display">
                                    <div>
                                        <strong style="display: block; font-size: 0.9rem;">CONSUMIDOR FINAL</strong>
                                        <span style="color: #64748b; font-size: 0.8rem;"><i class="fas fa-id-card"></i>
                                            9999999999999</span>
                                    </div>
                                    <span class="client-badge-verified"><i class="fas fa-check-circle"></i> Cliente
                                        Verificado</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions Grid -->
                        <div class="quick-actions-grid">
                            <div class="action-btn">
                                <i class="fas fa-cash-register"></i>
                                <span>Cerrar Caja</span>
                                <span class="key-hint">F2</span>
                            </div>
                            <div class="action-btn">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span>Facturas</span>
                                <span class="key-hint">F3</span>
                            </div>
                            <div class="action-btn">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Pagos</span>
                                <span class="key-hint">F4</span>
                            </div>
                            <div class="action-btn">
                                <i class="fas fa-exchange-alt"></i>
                                <span>Kardex</span>
                                <span class="key-hint">F6</span>
                            </div>
                        </div>

                        <!-- Product Search & Grid Panel -->
                        <div class="pos-panel" style="flex: 1;">
                            <div class="panel-header">
                                <span><i class="fas fa-box-open"></i> Buscar Productos</span>
                            </div>
                            <div class="panel-body">
                                <input type="text" class="form-control product-search-input"
                                    placeholder="Buscar por nombre, código o categoría...">

                                <div class="pos-product-grid" id="pos-product-grid">
                                    <?php
                                    $stmtP = $pdo->query("
                                        SELECT p.*, l.nombre as lab 
                                        FROM productos p 
                                        LEFT JOIN laboratorios l ON p.idLaboratorio = l.id 
                                        WHERE p.anulado = 0 
                                        LIMIT 20
                                    ");
                                    $pos_products = $stmtP->fetchAll();
                                    foreach ($pos_products as $pp):
                                        $status_style = $pp['stock'] <= 0 ? 'border-left: 3px solid #ef4444;' : ($pp['stock'] <= $pp['stockMinimo'] ? 'border-left: 3px solid #f59e0b;' : '');
                                        ?>
                                        <div class="pos-product-card" style="<?php echo $status_style; ?>"
                                            onclick="addToCart(<?php echo htmlspecialchars(json_encode($pp)); ?>)">
                                            <span class="p-name"
                                                title="<?php echo htmlspecialchars($pp['nombre']); ?>"><?php echo htmlspecialchars($pp['nombre']); ?></span>
                                            <span class="p-code">Cód:
                                                <?php echo htmlspecialchars($pp['codigoPrincipal']); ?></span>
                                            <span class="p-loc"><i class="fas fa-flask"></i>
                                                <?php echo htmlspecialchars($pp['lab'] ?? 'Genérico'); ?></span>
                                            <span class="p-price">$
                                                <?php echo number_format($pp['precioVenta'], 2); ?></span>
                                            <span class="p-stock <?php echo $pp['stock'] <= 0 ? 'low' : ''; ?>">Stock:
                                                <?php echo number_format($pp['stock'], 0); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: Cart & Summary -->
                    <div class="scrollable-panel">

                        <!-- Price Mode Toggle -->
                        <div class="pos-panel">
                            <div class="panel-header" style="border: none; padding-bottom: 5px;">
                                <span><i class="fas fa-tag"></i> Tipo de Precio</span>
                            </div>
                            <div class="panel-body">
                                <div class="price-type-selector">
                                    <div class="price-type-btn active">
                                        <span>PRECIO</span>
                                        <span class="val">$ 0.00</span>
                                    </div>
                                    <div class="price-type-btn">
                                        <span>PRECIO EFE</span>
                                        <span class="val">$ 0.00</span>
                                    </div>
                                    <div class="price-type-btn">
                                        <span>PRECIO TAR</span>
                                        <span class="val">$ 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table Panel -->
                        <div class="pos-panel cart-list-panel">
                            <div class="panel-header">
                                <span><i class="fas fa-shopping-basket"></i> Productos en Carrito</span>
                            </div>
                            <div style="flex: 1; overflow-y: auto;">
                                <!-- Table Header -->
                                <table class="cart-table">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Código</th>
                                            <th>Producto/Item</th>
                                            <th>Cant</th>
                                            <th>P.Final</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Empty View -->
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty-cart-view">
                                                    <i class="fas fa-cart-plus"></i>
                                                    <strong>Carrito vacío</strong>
                                                    <p>Seleccione productos para agregar</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Discount Selection -->
                            <div class="panel-body" style="padding-top: 0;">
                                <span
                                    style="font-size: 0.75rem; font-weight: 700; color: #475569; display: block; margin-bottom: 8px;">
                                    <i class="fas fa-percentage"></i> Descuentos Fijos
                                </span>
                                <div class="discount-options">
                                    <div class="discount-btn active">Sin descuento</div>
                                    <div class="discount-btn">20%</div>
                                    <div class="discount-btn">30%</div>
                                    <div class="discount-btn">40%</div>
                                </div>
                                <div class="form-group" style="margin: 0;">
                                    <label style="font-size: 0.7rem; margin-bottom: 4px;">Descuento
                                        personalizado:</label>
                                    <input type="text" class="form-control" placeholder="0%"
                                        style="height: 32px; font-size: 0.8rem;">
                                </div>
                            </div>

                            <!-- Cart Summary Footer -->
                            <div class="cart-summary">
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <strong>$ 0.00</strong>
                                </div>
                                <div class="summary-row">
                                    <span>Descuento:</span>
                                    <strong>$ 0.00</strong>
                                </div>
                                <div class="summary-row">
                                    <span>IVA (15%):</span>
                                    <strong>$ 0.00</strong>
                                </div>
                                <div class="summary-row total">
                                    <span>TOTAL:</span>
                                    <span>$ 0.00</span>
                                </div>

                                <button class="btn-checkout">
                                    PROCESAR VENTA <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- MODAL: CONFIRMACIÓN DE VENTA -->
            <div class="modal-overlay" id="modal-confirmacion" style="z-index: 1001;">
                <div class="modal-content" style="max-width: 400px; border-radius: 20px;">
                    <div class="confirm-card">
                        <div class="confirm-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="confirm-title">¿Procesar Venta?</div>
                        <div class="confirm-text">Se generará la factura electrónica y el ticket de impresión para el
                            cliente seleccionado.</div>
                        <div class="confirm-actions">
                            <button class="btn btn-secondary"
                                onclick="document.getElementById('modal-confirmacion').style.display='none'">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button class="btn btn-primary" id="btn-finalizar-venta" style="background: #6366f1;">
                                <i class="fas fa-check"></i> Sí, Finalizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL: ELIMINAR VENTA -->
            <div class="modal-overlay" id="modal-eliminar-venta" style="z-index: 1002;">
                <div class="modal-content" style="max-width: 400px; border-radius: 20px;">
                    <div class="confirm-card">
                        <div class="confirm-icon" style="color: #ef4444;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="confirm-title">¿Eliminar Venta?</div>
                        <div class="confirm-text">Se perderán todos los productos agregados a esta pestaña. Esta acción no se puede deshacer.</div>
                        <div class="confirm-actions">
                            <button class="btn btn-secondary" onclick="document.getElementById('modal-eliminar-venta').style.display='none'">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button class="btn btn-primary" id="btn-confirmar-eliminar" style="background: #ef4444;">
                                <i class="fas fa-trash"></i> Sí, Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL: TICKET DE VENTA (Thermal Preview) -->
            <div class="modal-overlay" id="modal-ticket">
                <div class="modal-content" style="max-width: 450px; background: #f1f5f9;">
                    <div class="modal-header">
                        <h2><i class="fas fa-check-circle"></i> ¡Venta Exitosa!</h2>
                        <button class="btn-text close-modal"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body" style="padding: 10px;">
                        <div class="ticket-thermal" id="printable-ticket">
                            <div class="ticket-header">
                                <div class="ticket-logo">
                                    <i class="fas fa-plus-square"></i>
                                    <span>
                                        <?php echo $establishment; ?>
                                    </span>
                                </div>
                                <span class="ticket-slogan">Tu Bienestar, Nuestra Prioridad</span>
                                <div class="ticket-info">
                                    RUC:
                                    <?php echo $empresa['ruc']; ?><br>
                                    Ambiente:
                                    <?php echo $environment; ?><br>
                                    Emisión: NORMAL<br>
                                    Matriz:
                                    <?php echo $empresa['direccion_matriz']; ?><br>
                                    Telf:
                                    <?php echo $empresa['telefono']; ?><br>
                                    Autorización SRI: <br>
                                    <span style="font-size: 8px;">0000000000000000000000000000000000000000</span><br>
                                    Clave de Acceso: <br>
                                    <span
                                        style="font-size: 8px;">1201202601<?php echo $empresa['ruc']; ?>2001001000000001
                                    </span>
                                </div>
                            </div>

                            <div class="ticket-divider"></div>
                            <div style="font-size: 11px; font-weight: bold; text-align: center;">FACTURA No.
                                001-001-000000001</div>
                            <div class="ticket-divider"></div>

                            <div class="ticket-info" style="text-transform: none;">
                                <strong>Cliente:</strong> <span id="t-cliente"></span><br>
                                <strong>CI/RUC:</strong> <span id="t-ruc"></span><br>
                                <strong>Dirección:</strong> <span id="t-direccion"></span><br>
                                <strong>Fecha:</strong>
                                <?php echo $date_now; ?>
                            </div>

                            <div class="ticket-divider"></div>
                            <table class="ticket-table">
                                <thead>
                                    <tr>
                                        <th style="width: 10%;">CANT.</th>
                                        <th style="width: 50%;">DESCRIPCION</th>
                                        <th style="width: 20%;">P.UNI</th>
                                        <th style="width: 20%;">VALOR</th>
                                    </tr>
                                </thead>
                                <tbody id="t-items">
                                    <!-- Items load here -->
                                </tbody>
                            </table>
                            <div class="ticket-divider"></div>

                            <div class="ticket-total-row">
                                <span>A pagar:</span>
                                <span id="t-total">$ 0.00</span>
                            </div>

                            <div style="font-size: 10px; margin-top: 10px;">
                                <strong>FORMA DE PAGO:</strong> EFECTIVO
                            </div>

                            <div class="ticket-footer">
                                <p><strong>Su ahorro fue: $ 0.00</strong></p>
                                <div class="ticket-qr">QR CODE</div>
                                <p>GRACIAS POR SU COMPRA</p>
                                <p>PUEDE VERIFICAR SU FACTURA ELECTRÓNICA EN:<br>https://srienlinea.sri.gob.ec</p>
                                <p>Cualquier cotización a este número:
                                    <?php echo $empresa['telefono']; ?>
                                </p>
                                <p><?php echo $establishment; ?> TE ESPERA</p>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer btn-print-hide">
                        <button class="btn btn-secondary close-modal">Cerrar</button>
                        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir
                            Ticket</button>
                    </div>
                </div>
            </div>

            <!-- MODAL: NUEVO CLIENTE (Pharmaceutical style) -->
            <div class="modal-overlay" id="modal-cliente">
                <div class="modal-content" style="max-width: 800px;">
                    <div class="modal-header">
                        <h2><i class="fas fa-user-plus"></i> Nuevo Cliente</h2>
                        <button class="btn-text close-modal"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="price-type-selector" style="margin-bottom: 24px;">
                            <div class="price-type-btn active" style="padding: 12px;"><i class="fas fa-user"></i>
                                Persona Natural</div>
                            <div class="price-type-btn" style="padding: 12px;"><i class="fas fa-building"></i> Empresa
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                            <!-- Personal Info -->
                            <div>
                                <h3 style="font-size: 0.9rem; margin-bottom: 15px; color: #0061f2;"><i
                                        class="fas fa-info-circle"></i> Información Personal</h3>
                                <div class="form-group">
                                    <label>Nombres *</label>
                                    <input type="text" class="form-control" placeholder="">
                                </div>
                                <div class="form-group">
                                    <label>Apellidos *</label>
                                    <input type="text" class="form-control" placeholder="">
                                </div>
                                <div class="form-group">
                                    <label>Número de Identidad / RUC *</label>
                                    <input type="text" class="form-control" placeholder="">
                                </div>
                            </div>
                            <!-- Contact Info -->
                            <div>
                                <h3 style="font-size: 0.9rem; margin-bottom: 15px; color: #0061f2;"><i
                                        class="fas fa-phone-alt"></i> Información de Contacto</h3>
                                <div class="form-group">
                                    <label>Teléfono *</label>
                                    <input type="text" class="form-control" placeholder="">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" placeholder="">
                                </div>
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <textarea class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 20px 0;">

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                            <!-- Commercial Info -->
                            <div>
                                <h3 style="font-size: 0.9rem; margin-bottom: 15px; color: #0061f2;"><i
                                        class="fas fa-briefcase"></i> Información Comercial</h3>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                    <div class="form-group">
                                        <label>Límite Crédito</label>
                                        <input type="number" class="form-control" value="0.00">
                                    </div>
                                    <div class="form-group">
                                        <label>Días Crédito</label>
                                        <input type="number" class="form-control" value="0">
                                    </div>
                                </div>
                            </div>
                            <!-- Settings -->
                            <div>
                                <h3 style="font-size: 0.9rem; margin-bottom: 15px; color: #0061f2;"><i
                                        class="fas fa-cog"></i> Configuraciones</h3>
                                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="checkbox" checked> <span>Cliente Activo</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="checkbox"> <span>Exento de Impuestos</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary close-modal">Cancelar</button>
                        <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cliente</button>
                    </div>
                </div>
            </div>
    </div> <!-- .pos-container -->
    </main> <!-- .main-content -->
    </div> <!-- .app-container -->
    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        const modal = document.getElementById('modal-cliente');
        const btnOpen = document.getElementById('btn-nuevo-cliente');
        const btnsClose = document.querySelectorAll('.close-modal');
        const btnCheckout = document.querySelector('.btn-checkout');
        const tabsBar = document.querySelector('.pos-tabs-bar');
        const btnNewTab = document.querySelector('.btn-new-tab');

        let sales = [{
            cart: [],
            client: { id: 1, nombres: 'CONSUMIDOR', apellidos: 'FINAL', cedula_ruc: '9999999999' },
            discount: 0,
            priceMode: 'NORMAL'
        }];
        let activeSaleIndex = 0;

        const companyInfo = <?php echo json_encode([
            'razon_social' => $empresa['razon_social'],
            'ruc' => $empresa['ruc'],
            'direccion_matriz' => $empresa['direccion_matriz'],
            'obligado_contabilidad' => $empresa['obligado_contabilidad'] ? 'SI' : 'NO'
        ]); ?>;

        btnOpen.onclick = () => modal.style.display = 'flex';
        btnsClose.forEach(btn => btn.onclick = () => modal.style.display = 'none');
        btnCheckout.onclick = () => processSale();

        // --- TAB MANAGEMENT ---
        function renderTabs() {
            // Keep the "Nueva Venta" button
            const newBtn = btnNewTab.cloneNode(true);
            tabsBar.innerHTML = '';

            sales.forEach((sale, index) => {
                const tab = document.createElement('div');
                tab.className = `tab-item ${index === activeSaleIndex ? 'active' : ''}`;
                tab.innerHTML = `
                    <i class="fas fa-shopping-cart"></i>
                    <span>Venta ${index + 1}</span>
                    ${index > 0 ? `<i class="fas fa-times close-tab" data-index="${index}" style="margin-left:8px; font-size:0.7rem; opacity:0.5;"></i>` : ''}
                `;
                tab.onclick = (e) => {
                    if (e.target.classList.contains('close-tab')) {
                        closeTab(parseInt(e.target.dataset.index));
                    } else {
                        switchTab(index);
                    }
                };
                tabsBar.appendChild(tab);
            });

            newBtn.onclick = addNewTab;
            tabsBar.appendChild(newBtn);
        }

        function addNewTab() {
            if (sales.length >= 8) return alert("Máximo 8 ventas simultáneas.");
            sales.push({
                cart: [],
                client: { id: 1, nombres: 'CONSUMIDOR', apellidos: 'FINAL', cedula_ruc: '9999999999' },
                discount: 0,
                priceMode: 'NORMAL'
            });
            activeSaleIndex = sales.length - 1;
            syncUIWithActiveSale();
            renderTabs();
        }

        function switchTab(index) {
            activeSaleIndex = index;
            syncUIWithActiveSale();
            renderTabs();
        }

        function closeTab(index) {
            const sale = sales[index];
            
            // Si el carrito tiene productos, mostrar modal personalizado
            if (sale.cart.length > 0) {
                const modalEliminar = document.getElementById('modal-eliminar-venta');
                modalEliminar.style.display = 'flex';
                
                document.getElementById('btn-confirmar-eliminar').onclick = function() {
                    modalEliminar.style.display = 'none';
                    executeCloseTab(index);
                };
            } else {
                // Si está vacío, cerrar inmediatamente sin preguntar
                executeCloseTab(index);
            }
        }

        function executeCloseTab(index) {
            if (activeSaleIndex === index) activeSaleIndex = Math.max(0, index - 1);
            else if (activeSaleIndex > index) activeSaleIndex--;
            sales.splice(index, 1);
            syncUIWithActiveSale();
            renderTabs();
        }

        function syncUIWithActiveSale() {
            const sale = sales[activeSaleIndex];
            // Actualizar selectores de precio
            priceModeBtns.forEach(b => {
                b.classList.remove('active');
                const span = b.querySelector('span');
                if (!span) return;
                const text = span.innerText;
                if (sale.priceMode === 'EFECTIVO' && text.includes('EFE')) b.classList.add('active');
                else if (sale.priceMode === 'TARJETA' && text.includes('TAR')) b.classList.add('active');
                else if (sale.priceMode === 'NORMAL' && !text.includes('EFE') && !text.includes('TAR')) b.classList.add('active');
            });

            // Actualizar descuentos
            discountBtns.forEach(b => {
                b.classList.remove('active');
                if (parseFloat(b.innerText) === sale.discount) b.classList.add('active');
                if (sale.discount === 0 && b.innerText.includes('Sin')) b.classList.add('active');
            });
            customDiscountInput.value = (sale.discount % 5 !== 0 && sale.discount !== 0) ? sale.discount : '';

            // Actualizar cliente en pantalla
            selectClient(sale.client);
            renderCart();
        }

        btnNewTab.onclick = addNewTab;
        renderTabs();

        // --- PRICE MODE & DISCOUNT SELECTORS ---
        const priceModeBtns = document.querySelectorAll('.pos-panel .price-type-btn');
        const discountBtns = document.querySelectorAll('.discount-btn');
        const customDiscountInput = document.querySelector('input[placeholder="0%"]');

        priceModeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                priceModeBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const span = btn.querySelector('span');
                if (!span) return;
                const modeText = span.innerText;
                if (modeText.includes('EFE')) sales[activeSaleIndex].priceMode = 'EFECTIVO';
                else if (modeText.includes('TAR')) sales[activeSaleIndex].priceMode = 'TARJETA';
                else sales[activeSaleIndex].priceMode = 'NORMAL';
                renderCart();
            });
        });

        discountBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                discountBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const discText = btn.innerText;
                if (discText.includes('%')) {
                    sales[activeSaleIndex].discount = parseFloat(discText.replace('%', ''));
                } else {
                    sales[activeSaleIndex].discount = 0;
                }
                customDiscountInput.value = '';
                renderCart();
            });
        });

        customDiscountInput.addEventListener('input', (e) => {
            discountBtns.forEach(b => b.classList.remove('active'));
            let val = parseFloat(e.target.value.replace('%', ''));
            sales[activeSaleIndex].discount = isNaN(val) ? 0 : val;
            renderCart();
        });

        // --- SEARCH PRODUCTS ---
        const productSearchInput = document.querySelector('.product-search-input');
        const productGrid = document.getElementById('pos-product-grid');

        productSearchInput.addEventListener('input', debounce(function (e) {
            const query = e.target.value;
            if (query.trim() === '') return;

            productGrid.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';

            fetch(`search_api.php?action=search_products&q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    productGrid.innerHTML = '';
                    if (data.length === 0) {
                        productGrid.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding: 20px; color: #94a3b8;"><i class="fas fa-search"></i> No se encontraron productos coincidentes.</div>';
                        return;
                    }
                    data.forEach(p => {
                        const pp = JSON.stringify(p).replace(/'/g, "&apos;");
                        const status_style = p.stock <= 0 ? 'border-left: 3px solid #ef4444;' : (p.stock <= p.stockMinimo ? 'border-left: 3px solid #f59e0b;' : '');
                        productGrid.innerHTML += `
                            <div class="pos-product-card" style="${status_style}" onclick='addToCart(${pp})'>
                                <span class="p-name" title="${p.nombre}">${p.nombre}</span>
                                <span class="p-code">Cód: ${p.codigoPrincipal}</span>
                                <span class="p-loc"><i class="fas fa-flask"></i> ${p.lab || 'Genérico'}</span>
                                <span class="p-price">$ ${parseFloat(p.precioVenta).toFixed(2)}</span>
                                <span class="p-stock ${p.stock <= 0 ? 'low' : ''}">Stock: ${parseInt(p.stock)}</span>
                            </div>
                        `;
                    });
                })
                .catch(err => {
                    console.error("Search error:", err);
                    productGrid.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding: 20px; color: #ef4444;"><i class="fas fa-exclamation-circle"></i> Error al buscar productos.</div>';
                });
        }, 300));

        // --- SEARCH CLIENTS ---
        const clientSearchInput = document.querySelector('.client-search-box input');
        const clientInfoDisplay = document.querySelector('.client-info-display');

        clientSearchInput.addEventListener('input', debounce(function (e) {
            const query = e.target.value;
            if (query.trim().length < 3) return;

            fetch(`search_api.php?action=search_clients&q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.length > 0) {
                        selectClient(data[0]);
                        clientSearchInput.style.borderColor = '#10b981'; // Green feedback
                    } else {
                        clientSearchInput.style.borderColor = '#ef4444'; // Red feedback
                    }
                })
                .catch(err => {
                    console.error("Client search error:", err);
                });
        }, 500));

        function selectClient(client) {
            sales[activeSaleIndex].client = client;
            clientInfoDisplay.innerHTML = `
                <div>
                    <strong style="display: block; font-size: 0.9rem;">${client.nombres} ${client.apellidos}</strong>
                    <span style="color: #64748b; font-size: 0.8rem;"><i class="fas fa-id-card"></i> ${client.cedula_ruc}</span>
                </div>
                <span class="client-badge-verified"><i class="fas fa-check-circle"></i> Cliente Verificado</span>
            `;
            const badge = document.querySelector('.badge-primary');
            if (badge) badge.textContent = client.cedula_ruc == '9999999999' ? 'CONSUMIDOR FINAL' : 'CLIENTE';
            clientSearchInput.value = `${client.nombres} ${client.apellidos}`;
        }

        // --- CART MANAGEMENT ---
        function addToCart(product) {
            const sale = sales[activeSaleIndex];
            const existing = sale.cart.find(item => item.id === product.id);

            if (existing) {
                existing.quantity++;
            } else {
                sale.cart.push({
                    id: product.id,
                    code: product.codigoPrincipal,
                    name: product.nombre,
                    basePrice: parseFloat(product.precioVenta),
                    price: parseFloat(product.precioVenta),
                    quantity: 1
                });
            }
            renderCart();
        }

        function removeFromCart(index) {
            sales[activeSaleIndex].cart.splice(index, 1);
            renderCart();
        }

        function updateQty(index, qty) {
            if (qty <= 0) return removeFromCart(index);
            sales[activeSaleIndex].cart[index].quantity = parseFloat(qty);
            renderCart();
        }

        function renderCart() {
            const sale = sales[activeSaleIndex];
            const tbody = document.querySelector('.cart-table tbody');
            const summaryRows = document.querySelectorAll('.summary-row strong');
            const totalDisplay = document.querySelector('.summary-row.total span:last-child');
            const priceTypeVals = document.querySelectorAll('.price-type-btn .val');

            if (sale.cart.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7"><div class="empty-cart-view"><i class="fas fa-cart-plus"></i><strong>Carrito vacío</strong><p>Seleccione productos para agregar</p></div></td></tr>`;
                summaryRows[0].textContent = '$ 0.00';
                summaryRows[1].textContent = '$ 0.00';
                summaryRows[2].textContent = '$ 0.00';
                totalDisplay.textContent = '$ 0.00';
                priceTypeVals.forEach(v => v.textContent = '$ 0.00');
                return;
            }

            tbody.innerHTML = '';
            let subtotal = 0;

            sale.cart.forEach((item, index) => {
                let unitPrice = item.basePrice;
                if (sale.priceMode === 'EFECTIVO') unitPrice = item.basePrice * 0.95;
                if (sale.priceMode === 'TARJETA') unitPrice = item.basePrice * 1.05;

                item.price = unitPrice;
                const lineTotal = item.price * item.quantity;
                subtotal += lineTotal;

                tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.code}</td>
                        <td title="${item.name}">${item.name.substring(0, 30)}...</td>
                        <td><input type="number" value="${item.quantity}" style="width: 50px; text-align:center;" onchange="updateQty(${index}, this.value)"></td>
                        <td>$ ${item.price.toFixed(2)}</td>
                        <td>$ ${lineTotal.toFixed(2)}</td>
                        <td><button onclick="removeFromCart(${index})" style="color:red; border:none; background:none; cursor:pointer;"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `;
            });

            const discountAmount = subtotal * (sale.discount / 100);
            const subtotalWithDiscount = subtotal - discountAmount;
            const iva = subtotalWithDiscount * 0.15;
            const total = subtotalWithDiscount + iva;

            summaryRows[0].textContent = `$ ${subtotal.toFixed(2)}`;
            summaryRows[1].textContent = `$ ${discountAmount.toFixed(2)} (${sale.discount}%)`;
            summaryRows[2].textContent = `$ ${iva.toFixed(2)}`;
            totalDisplay.textContent = `$ ${total.toFixed(2)}`;

            priceTypeVals[0].textContent = `$ ${total.toFixed(2)}`;
            priceTypeVals[1].textContent = `$ ${(total * 0.9).toFixed(2)}`;
            priceTypeVals[2].textContent = `$ ${(total * 1.1).toFixed(2)}`;
        }

        function processSale() {
            const sale = sales[activeSaleIndex];
            // 1. Validaciones
            if (sale.cart.length === 0) {
                alert("El carrito está vacío.");
                return;
            }

            if (!sale.client) {
                alert("Debe seleccionar un cliente.");
                return;
            }

            // Mostrar modal profesional en lugar de confirm browser
            const modalConfirm = document.getElementById('modal-confirmacion');
            modalConfirm.style.display = 'flex';

            document.getElementById('btn-finalizar-venta').onclick = function () {
                modalConfirm.style.display = 'none';
                executeSale();
            };
        }

        function executeSale() {
            const sale = sales[activeSaleIndex];
            // 2. Construcción del JSON
            const subtotal = sale.cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
            const discountAmount = subtotal * (sale.discount / 100);
            const subtotalWithDiscount = subtotal - discountAmount;
            const ivaVal = subtotalWithDiscount * 0.15;
            const totalVal = subtotalWithDiscount + ivaVal;

            const saleData = {
                "tipo": "factura",
                "data": {
                    "fechaEmision": new Date().toLocaleDateString('es-ES'),
                    "dirEstablecimiento": companyInfo.direccion_matriz,
                    "obligadoContabilidad": companyInfo.obligado_contabilidad,
                    "tipoIdentificacionComprador": sale.client.cedula_ruc.length === 13 ? "04" : (sale.client.cedula_ruc.length === 10 ? "05" : "06"),
                    "razonSocialComprador": `${sale.client.nombres} ${sale.client.apellidos}`.trim(),
                    "identificacionComprador": sale.client.cedula_ruc,
                    "totalSinImpuestos": parseFloat(subtotalWithDiscount.toFixed(2)),
                    "totalDescuento": parseFloat(discountAmount.toFixed(2)),
                    "importeTotal": parseFloat(totalVal.toFixed(2)),
                    "moneda": "DOLAR",
                    "impuestos": [
                        {
                            "codigo": "2",
                            "codigoPorcentaje": "2",
                            "baseImponible": parseFloat(subtotalWithDiscount.toFixed(2)),
                            "valor": parseFloat(ivaVal.toFixed(2))
                        }
                    ],
                    "pagos": [
                        {
                            "formaPago": "01",
                            "total": parseFloat(totalVal.toFixed(2))
                        }
                    ],
                    "detalles": sale.cart.map(item => ({
                        "codigoPrincipal": item.code,
                        "description": item.name,
                        "cantidad": item.quantity,
                        "precioUnitario": item.price,
                        "descuento": 0,
                        "precioTotalSinImpuesto": parseFloat((item.price * item.quantity).toFixed(2)),
                        "impuestos": [
                            {
                                "codigo": "2",
                                "codigoPorcentaje": "2",
                                "tarifa": 15,
                                "baseImponible": parseFloat((item.price * item.quantity).toFixed(2)),
                                "valor": parseFloat((item.price * item.quantity * 0.15).toFixed(2))
                            }
                        ]
                    }))
                }
            };

            console.log("JSON a enviar:", saleData);

            // 3. Poblar Ticket Térmico
            document.getElementById('t-cliente').innerText = saleData.data.razonSocialComprador;
            document.getElementById('t-ruc').innerText = saleData.data.identificacionComprador;
            document.getElementById('t-direccion').innerText = sale.client.direccion || 'S/N';
            document.getElementById('t-total').innerText = `$ ${saleData.data.importeTotal.toFixed(2)}`;

            const tItems = document.getElementById('t-items');
            tItems.innerHTML = '';
            sale.cart.forEach(item => {
                tItems.innerHTML += `
                    <tr>
                        <td>${item.quantity}</td>
                        <td>${item.name}</td>
                        <td>${item.price.toFixed(2)}</td>
                        <td>${(item.price * item.quantity).toFixed(2)}</td>
                    </tr>
                `;
            });

            // 4. Mostrar Modal de Éxito/Ticket
            document.getElementById('modal-ticket').style.display = 'flex';

            // Vaciar carrito de ESTA venta y remover tab si no es la primera, 
            // o simplemente resetearla. 
            if (sales.length > 1) {
                closeTab(activeSaleIndex);
            } else {
                sales[0] = {
                    cart: [],
                    client: { id: 1, nombres: 'CONSUMIDOR', apellidos: 'FINAL', cedula_ruc: '9999999999' },
                    discount: 0,
                    priceMode: 'NORMAL'
                };
                syncUIWithActiveSale();
            }
        }

        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    </script>
</body>

</html>