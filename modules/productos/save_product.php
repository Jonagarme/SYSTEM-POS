<?php
/**
 * Logic to save or update a product
 */
session_start();
require_once '../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Auto-migration: Ensure missing columns exist
try {
    $cols_to_check = [
        'manejaLote' => "ALTER TABLE productos ADD COLUMN manejaLote TINYINT(1) DEFAULT 1 AFTER requiereSeguimiento"
    ];

    foreach ($cols_to_check as $col => $sql) {
        $check = $pdo->query("SHOW COLUMNS FROM productos LIKE '$col'");
        if (!$check->fetch()) {
            $pdo->exec($sql);
        }
    }
} catch (Exception $e) {
    // Silently continue
}

// 1. Recibir y Sanitizar Datos
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$codigo = trim($_POST['codigo'] ?? '');
$codigo_aux = trim($_POST['codigo_auxiliar'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$registro_sanitario = trim($_POST['registro_sanitario'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
$laboratorio_id = !empty($_POST['laboratorio_id']) ? intval($_POST['laboratorio_id']) : null;
$marca_id = !empty($_POST['marca_id']) ? intval($_POST['marca_id']) : null;
$costo = floatval($_POST['precio_compra'] ?? 0);
$precio = floatval($_POST['precio_venta'] ?? 0);
$pvp_unidad = floatval($_POST['pvp_unidad'] ?? 0);
$costo_caja = floatval($_POST['costo_caja'] ?? 0);
$stock_actual = floatval($_POST['stock_actual'] ?? 0);
$stock_minimo = floatval($_POST['stock_minimo'] ?? 0);
$stock_maximo = floatval($_POST['stock_maximo'] ?? 0);
$fecha_caducidad = !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null;
$es_divisible = isset($_POST['es_divisible']) ? 1 : 0;
$es_psicotropico = isset($_POST['es_psicotropico']) ? 1 : 0;
$requiere_cadena_frio = isset($_POST['cadena_frio']) ? 1 : 0;
$maneja_lote = isset($_POST['maneja_lote']) ? 1 : 0;
$activo = isset($_POST['estado']) ? 1 : 0;

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $stmt_u = $pdo->query("SELECT id FROM auth_user LIMIT 1");
    $user_id = $stmt_u->fetchColumn() ?: 1;
}

// 2. Validaciones
$errors = [];
if (empty($codigo))
    $errors[] = "El código principal es obligatorio.";
if (empty($nombre))
    $errors[] = "El nombre del producto es obligatorio.";
if ($precio <= 0)
    $errors[] = "El precio de venta debe ser mayor a 0.";

// Verificar duplicados de código
$stmt = $pdo->prepare("SELECT id FROM productos WHERE codigoPrincipal = ? AND id != ? AND anulado = 0");
$stmt->execute([$codigo, $id]);
if ($stmt->fetch()) {
    $errors[] = "Ya existe otro producto con el código: $codigo.";
}

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo->beginTransaction();

    if ($id > 0) {
        // UPDATE
        $sql = "UPDATE productos SET 
                    codigoPrincipal = ?, codigoAuxiliar = ?, nombre = ?, registroSanitario = ?,
                    descripcion = ?, observaciones = ?, idCategoria = ?, idLaboratorio = ?, idMarca = ?,
                    costoUnidad = ?, precioVenta = ?, pvpUnidad = ?, costoCaja = ?,
                    stockMinimo = ?, stockMaximo = ?, fechaCaducidad = ?,
                    esDivisible = ?, esPsicotropico = ?, requiereCadenaFrio = ?, manejaLote = ?, activo = ?,
                    editadoDate = NOW(), editadoPor = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $codigo,
            $codigo_aux,
            $nombre,
            $registro_sanitario,
            $descripcion,
            $observaciones,
            $categoria_id,
            $laboratorio_id,
            $marca_id,
            $costo,
            $precio,
            $pvp_unidad,
            $costo_caja,
            $stock_minimo,
            $stock_maximo,
            $fecha_caducidad,
            $es_divisible,
            $es_psicotropico,
            $requiere_cadena_frio,
            $maneja_lote,
            $activo,
            $user_id,
            $id
        ]);
        $message = "Producto actualizado correctamente";
    } else {
        // INSERT
        $sql = "INSERT INTO productos (
                    codigoPrincipal, codigoAuxiliar, nombre, registroSanitario, 
                    descripcion, observaciones, idCategoria, idLaboratorio, idMarca,
                    costoUnidad, precioVenta, pvpUnidad, costoCaja, stock, 
                    stockMinimo, stockMaximo, fechaCaducidad, 
                    esDivisible, esPsicotropico, requiereCadenaFrio, manejaLote, activo,
                    creadoDate, creadoPor, idTipoProducto, idClaseProducto, idSubcategoria, idSubnivel, anulado
                ) VALUES (
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, 
                    ?, ?, ?, ?, ?,
                    NOW(), ?, 1, 1, 1, NULL, 0
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $codigo,
            $codigo_aux,
            $nombre,
            $registro_sanitario,
            $descripcion,
            $observaciones,
            $categoria_id,
            $laboratorio_id,
            $marca_id,
            $costo,
            $precio,
            $pvp_unidad,
            $costo_caja,
            $stock_actual,
            $stock_minimo,
            $stock_maximo,
            $fecha_caducidad,
            $es_divisible,
            $es_psicotropico,
            $requiere_cadena_frio,
            $maneja_lote,
            $activo,
            $user_id
        ]);
        $id = $pdo->lastInsertId();
        $message = "Producto creado correctamente";

        // Kardex inicial
        if ($stock_actual > 0) {
            $stmt_kardex = $pdo->prepare("INSERT INTO kardex_movimientos (idProducto, tipoMovimiento, ingreso, saldo, detalle, fecha) VALUES (?, 'INGRESO', ?, ?, 'Saldo Inicial/Creación', NOW())");
            $stmt_kardex->execute([$id, $stock_actual, $stock_actual]);
        }
    }

    $pdo->commit();

    // Log Audit
    require_once '../../includes/audit.php';
    if ($id > 0 && strpos($message, 'actualizado') !== false) {
        registrarAuditoria('Productos', 'EDITAR', 'productos', $id, "Se actualizó el producto: $nombre (Código: $codigo)");
    } else {
        registrarAuditoria('Productos', 'CREAR', 'productos', $id, "Se creó el nuevo producto: $nombre (Código: $codigo)");
    }

    echo json_encode(['status' => 'success', 'message' => $message, 'id' => $id]);

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error en base de datos: ' . $e->getMessage()]);
}
