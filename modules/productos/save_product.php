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
$codigo = trim($_POST['codigo'] ?? '');
$codigo_aux = trim($_POST['codigo_auxiliar'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$registro_sanitario = trim($_POST['registro_sanitario'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
$marca = trim($_POST['marca'] ?? '');
$laboratorio = trim($_POST['laboratorio'] ?? '');
$costo = floatval($_POST['precio_compra'] ?? 0);
$precio = floatval($_POST['precio_venta'] ?? 0);
$pvp = floatval($_POST['pvp_unidad'] ?? 0);
$stock_actual = floatval($_POST['stock_actual'] ?? 0);
$stock_minimo = floatval($_POST['stock_minimo'] ?? 5);
$stock_maximo = floatval($_POST['stock_maximo'] ?? 100);
$fecha_caducidad = $_POST['fecha_caducidad'] ?: null;
$es_divisible = isset($_POST['es_divisible']) ? 1 : 0;
$es_psicotropico = isset($_POST['es_psicotropico']) ? 1 : 0;
$cadena_frio = isset($_POST['cadena_frio']) ? 1 : 0;
$estado = isset($_POST['estado']) ? 1 : 0;

// 2. Validaciones Críticas
$errors = [];

if (empty($codigo))
    $errors[] = "El código principal es obligatorio.";
if (empty($nombre))
    $errors[] = "El nombre del producto es obligatorio.";
if ($precio <= 0)
    $errors[] = "El precio de venta debe ser mayor a 0.";
if ($costo < 0)
    $errors[] = "El costo no puede ser negativo.";

// Validación de lógica de negocio: Precio no puede ser menor que el costo (alerta)
if ($precio < $costo) {
    // Esto podría ser una advertencia, pero aquí lo pondremos como error por seguridad
    $errors[] = "El precio de venta ($precio) no puede ser menor al costo de compra ($costo).";
}

// Verificar si el código ya existe
$stmt = $pdo->prepare("SELECT id FROM productos WHERE codigo = ?");
$stmt->execute([$codigo]);
if ($stmt->fetch()) {
    $errors[] = "Ya existe un producto con el código: $codigo.";
}

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit;
}

// 3. Insertar en la Base de Datos
try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO productos (
                codigo, codigo_auxiliar, nombre, registro_sanitario, 
                descripcion, observaciones, categoria_id, marca, laboratorio,
                precio_compra, precio_venta, pvp_unidad, stock_actual, 
                stock_minimo, stock_maximo, fecha_caducidad, 
                es_divisible, es_psicotropico, cadena_frio, estado
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, 
                ?, ?, ?, 
                ?, ?, ?, ?
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
        $marca,
        $laboratorio,
        $costo,
        $precio,
        $pvp,
        $stock_actual,
        $stock_minimo,
        $stock_maximo,
        $fecha_caducidad,
        $es_divisible,
        $es_psicotropico,
        $cadena_frio,
        $estado
    ]);

    $producto_id = $pdo->lastInsertId();

    // 4. Registrar en Kardex (Saldo inicial si el stock es > 0)
    if ($stock_actual > 0) {
        $stmt_kardex = $pdo->prepare("INSERT INTO kardex (producto_id, tipo, cantidad, motivo) VALUES (?, 'Entrada', ?, 'Saldo Inicial/Creación')");
        $stmt_kardex->execute([$producto_id, $stock_actual]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Producto creado correctamente', 'id' => $producto_id]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
}
