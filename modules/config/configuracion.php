<?php
/**
 * System Configuration - Configuración del Sistema
 */
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$current_page = 'config';

// Fetch real company data
$stmt = $pdo->query("SELECT * FROM empresas LIMIT 1");
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

// If no company exists, initialize with empty values to avoid errors
if (!$empresa) {
    $empresa = [
        'id' => null,
        'ruc' => '',
        'razon_social' => '',
        'nombre_comercial' => '',
        'direccion_matriz' => '',
        'telefono' => '',
        'email' => '',
        'contribuyente_especial' => '',
        'obligado_contabilidad' => 0,
        'certificado_p12_path' => '',
        'certificado_password' => '',
        'certificado_fecha_expiracion' => '',
        'sri_ambiente' => 1,
        'tipo_menu' => 'horizontal'
    ];
}

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ruc = $_POST['ruc'] ?? '';
    $razon_social = $_POST['razon_social'] ?? '';
    $nombre_comercial = $_POST['nombre_comercial'] ?? '';
    $direccion_matriz = $_POST['direccion_matriz'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $contribuyente_especial = $_POST['contribuyente_especial'] ?? '';
    $obligado_contabilidad = isset($_POST['obligado_contabilidad']) ? 1 : 0;
    $sri_ambiente = $_POST['sri_ambiente'] ?? 1;
    $certificado_p12_path = $_POST['certificado_p12_path'] ?? '';
    $certificado_password = $_POST['certificado_password'] ?? '';
    $certificado_fecha_expiracion = $_POST['certificado_fecha_expiracion'] ?? null;

    if ($empresa['id']) {
        // Update existing
        $sql = "UPDATE empresas SET 
                ruc = :ruc, 
                razon_social = :razon_social, 
                nombre_comercial = :nombre_comercial, 
                direccion_matriz = :direccion_matriz, 
                telefono = :telefono, 
                email = :email, 
                contribuyente_especial = :contribuyente_especial, 
                obligado_contabilidad = :obligado_contabilidad, 
                sri_ambiente = :sri_ambiente, 
                certificado_p12_path = :certificado_p12_path, 
                certificado_password = :certificado_password, 
                certificado_fecha_expiracion = :certificado_fecha_expiracion 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':ruc' => $ruc,
            ':razon_social' => $razon_social,
            ':nombre_comercial' => $nombre_comercial,
            ':direccion_matriz' => $direccion_matriz,
            ':telefono' => $telefono,
            ':email' => $email,
            ':contribuyente_especial' => $contribuyente_especial,
            ':obligado_contabilidad' => $obligado_contabilidad,
            ':sri_ambiente' => $sri_ambiente,
            ':certificado_p12_path' => $certificado_p12_path,
            ':certificado_password' => $certificado_password,
            ':certificado_fecha_expiracion' => $certificado_fecha_expiracion,
            ':id' => $empresa['id']
        ]);
    } else {
        // Insert new
        $sql = "INSERT INTO empresas (ruc, razon_social, nombre_comercial, direccion_matriz, telefono, email, contribuyente_especial, obligado_contabilidad, sri_ambiente, certificado_p12_path, certificado_password, certificado_fecha_expiracion) 
                VALUES (:ruc, :razon_social, :nombre_comercial, :direccion_matriz, :telefono, :email, :contribuyente_especial, :obligado_contabilidad, :sri_ambiente, :certificado_p12_path, :certificado_password, :certificado_fecha_expiracion)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':ruc' => $ruc,
            ':razon_social' => $razon_social,
            ':nombre_comercial' => $nombre_comercial,
            ':direccion_matriz' => $direccion_matriz,
            ':telefono' => $telefono,
            ':email' => $email,
            ':contribuyente_especial' => $contribuyente_especial,
            ':obligado_contabilidad' => $obligado_contabilidad,
            ':sri_ambiente' => $sri_ambiente,
            ':certificado_p12_path' => $certificado_p12_path,
            ':certificado_password' => $certificado_password,
            ':certificado_fecha_expiracion' => $certificado_fecha_expiracion
        ]);
    }

    if ($result) {
        // Handle logo upload if present
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logo_data = file_get_contents($_FILES['logo']['tmp_name']);
            $stmtLogo = $pdo->prepare("UPDATE empresas SET logo = :logo WHERE id = :id");
            $stmtLogo->execute([':logo' => $logo_data, ':id' => $empresa['id'] ?: $pdo->lastInsertId()]);
        }

        $message = "Configuración guardada correctamente.";
        // Refresh data
        $stmt = $pdo->query("SELECT * FROM empresas LIMIT 1");
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Error al guardar la configuración.";
    }
}

// Ensure all keys exist even if row was found but is missing columns (or to handle empty DB)
$defaults = [
    'id' => null,
    'ruc' => '',
    'razon_social' => '',
    'nombre_comercial' => '',
    'direccion_matriz' => '',
    'telefono' => '',
    'email' => '',
    'contribuyente_especial' => '',
    'obligado_contabilidad' => 0,
    'certificado_p12_path' => '',
    'certificado_password' => '',
    'certificado_fecha_expiracion' => '',
    'sri_ambiente' => 1,
    'tipo_menu' => 'horizontal',
    'logo' => null
];

$empresa = $empresa ? array_merge($defaults, $empresa) : $defaults;

$logo_src = '../../assets/img/login-bg.png'; // Default if nothing else
if (!empty($empresa['logo'])) {
    $logo_src = 'data:image/png;base64,' . base64_encode($empresa['logo']);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema | Warehouse POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .c-header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .c-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 5px;
        }

        .c-header p {
            font-size: 0.9rem;
            color: #64748b;
        }

        .c-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            align-items: flex-start;
        }

        /* Tabs Sidebar */
        .c-tabs {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .c-tab-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            margin-bottom: 5px;
            cursor: pointer;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
        }

        .c-tab-btn:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .c-tab-btn.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .c-tab-btn i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Content Area */
        .c-content-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid #f1f5f9;
        }

        .c-content-header {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 15px 25px;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .c-content-body {
            padding: 30px;
        }

        /* Form styling */
        .alert-info-c {
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            color: #0369a1;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-warn-c {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            color: #92400e;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .c-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .c-form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .c-form-group label i {
            color: #6366f1;
        }

        .c-form-group label span {
            color: #dc2626;
        }

        .c-form-group input,
        .c-form-group select,
        .c-form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .c-form-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .c-form-group .hint {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 6px;
        }

        /* Toggle Switch */
        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
        }

        .toggle-btn-c {
            width: 44px;
            height: 22px;
            background: #cbd5e1;
            border-radius: 20px;
            position: relative;
            transition: all 0.3s;
        }

        .toggle-btn-c::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 18px;
            height: 18px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .toggle-switch.active .toggle-btn-c {
            background: #6366f1;
        }

        .toggle-switch.active .toggle-btn-c::after {
            left: 24px;
        }

        .toggle-label-c {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
        }

        .env-badge {
            background: #f59e0b;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
        }

        .c-footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
        }

        .btn-cancel {
            background: white;
            color: #64748b;
            border: 1px solid #e2e8f0;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-cancel:hover {
            background: #f1f5f9;
        }

        .btn-save-config {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-save-config:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
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
                <div class="c-header-container">
                    <div class="c-header">
                        <h1><i class="fas fa-cog" style="color: #6366f1;"></i> Configuración del Sistema</h1>
                        <p>Administra la información de tu empresa y preferencias del sistema</p>
                    </div>
                    <a href="<?php echo $root; ?>index.php" class="btn btn-secondary"
                        style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert-info-c"
                        style="background: #dcfce7; color: #15803d; border-color: #bbf7d0; margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert-warn-c"
                        style="background: #fee2e2; color: #991b1b; border-color: #fecaca; margin-bottom: 20px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="c-layout">
                    <!-- Tabs Sidebar -->
                    <div class="c-tabs">
                        <button class="c-tab-btn active" onclick="showTab('basica')">
                            <i class="fas fa-building"></i> Información Básica
                        </button>
                        <button class="c-tab-btn" onclick="showTab('contacto')">
                            <i class="fas fa-address-book"></i> Contacto
                        </button>
                        <button class="c-tab-btn" onclick="showTab('fiscal')">
                            <i class="fas fa-file-invoice-dollar"></i> Información Fiscal
                        </button>
                        <button class="c-tab-btn" onclick="showTab('personalizacion')">
                            <i class="fas fa-palette"></i> Personalización
                        </button>
                        <button class="c-tab-btn" onclick="showTab('certificados')">
                            <i class="fas fa-shield-alt"></i> Certificados
                        </button>
                    </div>

                    <!-- Right Panel Content -->
                    <div class="c-content-area">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <!-- Tab: Información Básica -->
                            <div id="tab-basica" class="tab-content active">
                                <div class="c-content-card">
                                    <div class="c-content-header">
                                        <i class="fas fa-building"></i> Información Básica de la Empresa
                                    </div>
                                    <div class="c-content-body">
                                        <div class="alert-info-c">
                                            <i class="fas fa-info-circle"></i> Esta es la información que se mostrará en
                                            reportes y facturas
                                        </div>

                                        <div class="c-form-grid">
                                            <div class="c-form-group">
                                                <label><i class="fas fa-id-card"></i> RUC <span>*</span></label>
                                                <input type="text" name="ruc"
                                                    value="<?php echo htmlspecialchars($empresa['ruc']); ?>" required>
                                                <p class="hint">Número de identificación tributaria</p>
                                            </div>
                                            <div class="c-form-group">
                                                <label><i class="fas fa-briefcase"></i> Razón Social
                                                    <span>*</span></label>
                                                <input type="text" name="razon_social"
                                                    value="<?php echo htmlspecialchars($empresa['razon_social']); ?>"
                                                    required>
                                                <p class="hint">Nombre legal de la empresa</p>
                                            </div>
                                        </div>

                                        <div class="c-form-group" style="margin-bottom: 25px;">
                                            <label><i class="fas fa-store"></i> Nombre Comercial</label>
                                            <input type="text" name="nombre_comercial"
                                                value="<?php echo htmlspecialchars($empresa['nombre_comercial']); ?>">
                                            <p class="hint">Nombre con el que tus clientes te conocen</p>
                                        </div>

                                        <div class="c-form-group">
                                            <label><i class="fas fa-map-marker-alt"></i> Dirección
                                                <span>*</span></label>
                                            <textarea name="direccion_matriz" rows="3"
                                                required><?php echo htmlspecialchars($empresa['direccion_matriz']); ?></textarea>
                                            <p class="hint">Dirección completa de tu establecimiento principal</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Información Fiscal -->
                            <div id="tab-fiscal" class="tab-content">
                                <div class="c-content-card">
                                    <div class="c-content-header">
                                        <i class="fas fa-file-invoice-dollar"></i> Información Fiscal y Tributaria
                                    </div>
                                    <div class="c-content-body">
                                        <div class="c-form-grid">
                                            <div class="c-form-group">
                                                <label><i class="fas fa-certificate"></i> Contribuyente Especial</label>
                                                <input type="text" name="contribuyente_especial"
                                                    value="<?php echo htmlspecialchars($empresa['contribuyente_especial']); ?>">
                                                <p class="hint">Número de resolución (si aplica)</p>
                                            </div>
                                            <div class="c-form-group"
                                                style="display: flex; align-items: center; padding-top: 30px;">
                                                <div class="toggle-switch <?php echo $empresa['obligado_contabilidad'] ? 'active' : ''; ?>"
                                                    id="toggle-contabilidad">
                                                    <input type="hidden" name="obligado_contabilidad"
                                                        value="<?php echo $empresa['obligado_contabilidad']; ?>"
                                                        id="input-contabilidad">
                                                    <div class="toggle-btn-c"></div>
                                                    <span class="toggle-label-c">Obligado a llevar contabilidad</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="c-form-group" style="max-width: 50%;">
                                            <label><i class="fas fa-server"></i> Ambiente SRI</label>
                                            <select name="sri_ambiente">
                                                <option value="1" <?php echo $empresa['sri_ambiente'] == 1 ? 'selected' : ''; ?>>Pruebas</option>
                                                <option value="2" <?php echo $empresa['sri_ambiente'] == 2 ? 'selected' : ''; ?>>Producción</option>
                                            </select>
                                            <div class="env-badge" id="env-badge">
                                                <?php if ($empresa['sri_ambiente'] == 1): ?>
                                                    <i class="fas fa-tools"></i> Ambiente de Pruebas
                                                <?php else: ?>
                                                    <i class="fas fa-check-double"></i> Ambiente de Producción
                                                <?php endif; ?>
                                            </div>
                                            <p class="hint" style="margin-top: 15px;">Selecciona el ambiente para la
                                                emisión de comprobantes electrónicos ante el SRI</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Certificados -->
                            <div id="tab-certificados" class="tab-content">
                                <div class="c-content-card">
                                    <div class="c-content-header">
                                        <i class="fas fa-shield-alt"></i> Configuración de Certificados Digitales
                                    </div>
                                    <div class="c-content-body">
                                        <div class="alert-warn-c">
                                            <i class="fas fa-exclamation-triangle"></i> Esta sección es opcional y solo
                                            necesaria si utilizas facturación electrónica
                                        </div>

                                        <div class="c-form-grid">
                                            <div class="c-form-group">
                                                <label><i class="fas fa-key"></i> Ruta Certificado P12</label>
                                                <input type="text" name="certificado_p12_path"
                                                    value="<?php echo htmlspecialchars($empresa['certificado_p12_path']); ?>"
                                                    placeholder="Ruta del certificado P12 (opcional)">
                                                <p class="hint">Ruta donde se encuentra el archivo del certificado</p>
                                            </div>
                                            <div class="c-form-group">
                                                <label><i class="fas fa-lock"></i> Password Certificado</label>
                                                <input type="password" name="certificado_password"
                                                    value="<?php echo htmlspecialchars($empresa['certificado_password']); ?>"
                                                    placeholder="Password del certificado (opcional)">
                                                <p class="hint">Contraseña del certificado digital</p>
                                            </div>
                                        </div>

                                        <div class="c-form-group" style="max-width: 50%;">
                                            <label><i class="fas fa-calendar-alt"></i> Fecha Expiración
                                                Certificado</label>
                                            <input type="date" name="certificado_fecha_expiracion"
                                                value="<?php echo $empresa['certificado_fecha_expiracion']; ?>">
                                            <p class="hint">Fecha de vencimiento del certificado</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Contacto -->
                            <div id="tab-contacto" class="tab-content">
                                <div class="c-content-card">
                                    <div class="c-content-header">
                                        <i class="fas fa-address-card"></i> Información de Contacto
                                    </div>
                                    <div class="c-content-body">
                                        <div class="c-form-grid">
                                            <div class="c-form-group">
                                                <label><i class="fas fa-phone"></i> Teléfono</label>
                                                <input type="text" name="telefono"
                                                    value="<?php echo htmlspecialchars($empresa['telefono']); ?>">
                                                <p class="hint">Número de contacto principal</p>
                                            </div>
                                            <div class="c-form-group">
                                                <label><i class="fas fa-envelope"></i> Email</label>
                                                <input type="email" name="email"
                                                    value="<?php echo htmlspecialchars($empresa['email']); ?>">
                                                <p class="hint">Correo electrónico de la empresa</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Tab: Personalización -->
                            <div id="tab-personalizacion" class="tab-content">
                                <div class="c-content-card">
                                    <div class="c-content-header">
                                        <i class="fas fa-palette"></i> Personalización del Sistema
                                    </div>
                                    <div class="c-content-body">
                                        <div class="c-form-grid">
                                            <div class="logo-upload">
                                                <div class="c-form-group">
                                                    <label><i class="fas fa-image"></i> Archivo del Logo</label>
                                                    <div
                                                        style="position: relative; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; display: flex; align-items: center; background: white;">
                                                        <label for="logo-input"
                                                            style="background: #f1f5f9; border-right: 1px solid #e2e8f0; padding: 10px 15px; font-size: 0.85rem; color: #475569; cursor: pointer; margin-bottom: 0;">Elegir
                                                            archivo</label>
                                                        <input type="file" name="logo" id="logo-input"
                                                            style="position: absolute; width: 0.1px; height: 0.1px; opacity: 0;"
                                                            onchange="updateFileName(this)">
                                                        <span id="file-name"
                                                            style="padding: 10px 15px; font-size: 0.85rem; color: #94a3b8; flex: 1;">No
                                                            se ha seleccionado ningún archivo</span>
                                                    </div>
                                                    <p class="hint">Seleccionar nuevo logo (JPG, PNG, GIF - máximo 2MB)
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="logo-preview">
                                                <label
                                                    style="display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 10px;"><i
                                                        class="fas fa-eye" style="color: #6366f1;"></i> Vista
                                                    Previa</label>
                                                <div
                                                    style="border: 2px dashed #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; background: #f8fafc;">
                                                    <img id="preview-img" src="<?php echo $logo_src; ?>"
                                                        alt="Logo Actual"
                                                        style="max-height: 120px; max-width: 100%; object-fit: contain; display: block; margin: 0 auto;">
                                                    <div id="preview-text"
                                                        style="margin-top: 10px; font-weight: 800; color: #0061f2; font-size: 1.25rem; text-transform: uppercase; line-height: 1.1;">
                                                        <?php echo nl2br(htmlspecialchars($empresa['nombre_comercial'] ?: $empresa['razon_social'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="c-footer">
                                <button type="button" class="btn-cancel"><i class="fas fa-times"></i> Cancelar</button>
                                <button type="submit" class="btn-save-config"><i class="fas fa-save"></i> Guardar
                                    Configuración</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include $root . 'includes/scripts.php'; ?>
    <script>
        function showTab(tabId) {
            // Remove active from all buttons
            document.querySelectorAll('.c-tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            // Hide all contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Set current active
            event.currentTarget.classList.add('active');
            document.getElementById('tab-' + tabId).classList.add('active');
        }

        // Simple toggle logic for the account Switch
        document.getElementById('toggle-contabilidad').addEventListener('click', function () {
            this.classList.toggle('active');
            const input = document.getElementById('input-contabilidad');
            input.value = this.classList.contains('active') ? '1' : '0';
        });

        // Update environment badge on select change
        document.querySelector('select[name="sri_ambiente"]').addEventListener('change', function () {
            const badge = document.getElementById('env-badge');
            if (this.value == '1') {
                badge.innerHTML = '<i class="fas fa-tools"></i> Ambiente de Pruebas';
                badge.style.background = '#f59e0b';
            } else {
                badge.innerHTML = '<i class="fas fa-check-double"></i> Ambiente de Producción';
                badge.style.background = '#10b981';
            }
        });

        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : 'No se ha seleccionado ningún archivo';
            document.getElementById('file-name').textContent = fileName;

            if (input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-img').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>