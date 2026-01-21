<?php
/**
 * Script de prueba para verificar que el UPDATE de estado funcione correctamente
 */
require_once '../../includes/db.php';

// Simular una respuesta de Logifact AUTORIZADA
$external_res = [
    'estado' => 'AUTORIZADO',
    'numeroAutorizacion' => '2101202601091591260400110019990000001169258951510',
    'fechaAutorizacion' => '2026-01-21T14:55:39-05:00',
    'ambiente' => 'PRUEBAS',
    'mensajes' => [],
    'claveAcceso' => '2101202601091591260400110019990000001169258951510'
];

// ID de una factura de prueba (cambiar por una real que esté en PROCESANDO)
$idVenta = 9; // CAMBIAR ESTO

echo "<h2>Test de actualización de estado</h2>\n";
echo "<pre>\n";

echo "1. Respuesta simulada de Logifact:\n";
print_r($external_res);
echo "\n";

if ($external_res && isset($external_res['estado'])) {
    $sriEstado = strtoupper((string) $external_res['estado']);
    $authNumber = $external_res['numeroAutorizacion'] ?? $external_res['autorizacion'] ?? $external_res['claveAcceso'] ?? null;

    if (is_array($authNumber)) {
        $authNumber = json_encode($authNumber);
    }

    echo "2. Estado normalizado: $sriEstado\n";
    echo "3. Número autorización: $authNumber\n\n";

    // Normalizar estado local
    $dbEstado = 'PENDIENTE';
    if ($sriEstado === 'AUTORIZADO' || $sriEstado === 'AUTORIZADA') {
        $dbEstado = 'AUTORIZADA';
    } elseif ($sriEstado === 'DEVUELTA' || $sriEstado === 'NO AUTORIZADO' || $sriEstado === 'RECHAZADO') {
        $dbEstado = 'RECHAZADO';
    } elseif (in_array($sriEstado, ['PROCESANDO', 'RECIBIDA', 'EN PROCESO'], true)) {
        $dbEstado = 'PROCESANDO';
    }

    echo "4. Estado a guardar en DB: $dbEstado\n\n";

    // Construir UPDATE tolerante a esquema
    $setParts = ['estadoFactura = ?'];
    $paramsUpd = [$dbEstado];

    if (!empty($authNumber)) {
        $setParts[] = 'numeroAutorizacion = ?';
        $paramsUpd[] = (string) $authNumber;
    }

    if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'fechaAutorizacion')) {
        $setParts[] = "fechaAutorizacion = " . ($dbEstado === 'AUTORIZADA' ? 'NOW()' : 'NULL');
        echo "5. Columna fechaAutorizacion existe: SÍ\n";
    } else {
        echo "5. Columna fechaAutorizacion existe: NO\n";
    }

    if (function_exists('db_has_column') && db_has_column($pdo, 'facturas_venta', 'respuesta_sri')) {
        $setParts[] = 'respuesta_sri = ?';
        $paramsUpd[] = json_encode($external_res);
        echo "6. Columna respuesta_sri existe: SÍ\n\n";
    } else {
        echo "6. Columna respuesta_sri existe: NO\n\n";
    }

    $paramsUpd[] = $idVenta;
    $sqlUpd = 'UPDATE facturas_venta SET ' . implode(', ', $setParts) . ' WHERE id = ?';

    echo "7. SQL generado:\n$sqlUpd\n\n";
    echo "8. Parámetros:\n";
    print_r($paramsUpd);
    echo "\n";

    // Verificar estado actual
    $stmt = $pdo->prepare("SELECT id, numeroFactura, estadoFactura, numeroAutorizacion FROM facturas_venta WHERE id = ?");
    $stmt->execute([$idVenta]);
    $antes = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "9. ANTES del UPDATE:\n";
    print_r($antes);
    echo "\n";

    // Ejecutar UPDATE
    try {
        $stmtUpd = $pdo->prepare($sqlUpd);
        $stmtUpd->execute($paramsUpd);
        echo "10. UPDATE ejecutado exitosamente. Filas afectadas: " . $stmtUpd->rowCount() . "\n\n";

        // Verificar estado después
        $stmt->execute([$idVenta]);
        $despues = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "11. DESPUÉS del UPDATE:\n";
        print_r($despues);
        echo "\n";

        if ($despues['estadoFactura'] === 'AUTORIZADA') {
            echo "<strong style='color: green;'>✅ ÉXITO: Estado actualizado correctamente a AUTORIZADA</strong>\n";
        } else {
            echo "<strong style='color: red;'>❌ ERROR: Estado no se actualizó. Quedó en: " . $despues['estadoFactura'] . "</strong>\n";
        }

    } catch (Exception $e) {
        echo "<strong style='color: red;'>❌ ERROR al ejecutar UPDATE:</strong>\n";
        echo $e->getMessage() . "\n";
    }
}

echo "</pre>";
