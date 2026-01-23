<?php
/**
 * Logic to save or update a product
 */
require_once '../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
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
$marca = trim($_POST['marca'] ?? '');
$costo = floatval($_POST['precio_compra'] ?? 0);
$precio = floatval($_POST['precio_venta'] ?? 0);
$pvp = floatval($_POST['pvp_unidad'] ?? 0);
$costo_caja = floatval($_POST['costo_caja'] ?? 0);
$stock_actual = floatval($_POST['stock_actual'] ?? 0);
$stock_minimo = floatval($_POST['stock_minimo'] ?? 0);
$stock_maximo = floatval($_POST['stock_maximo'] ?? 0);
$fecha_caducidad = !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null;
$es_divisible = isset($_POST['es_divisible']) ? 1 : 0;
$es_psicotropico = isset($_POST['es_psicotropico']) ? 1 : 0;
$cadena_frio = isset($_POST['cadena_frio']) ? 1 : 0;
$estado = isset($_POST['estado']) ? 1 : 0;

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
                    descripcion = ?, observaciones = ?, idCategoria = ?, idLaboratorio = ?, marca = ?,
                    precioCompra = ?, precioVenta = ?, pvp = ?, costoCaja = ?,
                    stockMinimo = ?, stockMaximo = ?, fechaCaducidad = ?,
                    esDivisible = ?, esPsicotropico = ?, cadenaFrio = ?, estado = ?,
                    actualizadoDate = NOW()
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
            $marca,
            $costo,
            $precio,
            $pvp,
            $costo_caja,
            $stock_minimo,
            $stock_maximo,
            $fecha_caducidad,
            $es_divisible,
            $es_psicotropico,
            $cadena_frio,
            $estado,
            $id
        ]);
        $message = "Producto actualizado correctamente";
    } else {
        // INSERT
        $sql = "INSERT INTO productos (
                    codigoPrincipal, codigoAuxiliar, nombre, registroSanitario, 
                    descripcion, observaciones, idCategoria, idLaboratorio, marca,
                    precioCompra, precioVenta, pvp, costoCaja, stock, 
                    stockMinimo, stockMaximo, fechaCaducidad, 
                    esDivisible, esPsicotropico, cadenaFrio, estado,
                    creadoDate, anulado
                ) VALUES (
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, 
                    ?, ?, ?, ?,
                    NOW(), 0
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
            $marca,
            $costo,
            $precio,
            $pvp,
            $costo_caja,
            $stock_actual,
            $stock_minimo,
            $stock_maximo,
            $fecha_caducidad,
            $es_divisible,
            $es_psicotropico,
            $cadena_frio,
            $estado
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
