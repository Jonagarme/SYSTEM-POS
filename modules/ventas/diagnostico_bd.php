<?php
/**
 * Diagnóstico de estructura de BD
 */
require_once '../../includes/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Diagnóstico de Base de Datos - facturas_venta</h2>\n";
echo "<pre>\n";

// 1. Verificar si la función existe
echo "1. Función db_has_column existe: " . (function_exists('db_has_column') ? "✅ SÍ" : "❌ NO") . "\n\n";

// 2. Ver estructura de la tabla
try {
    $stmt = $pdo->query("DESCRIBE facturas_venta");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "2. Columnas de facturas_venta:\n";
    foreach ($columnas as $col) {
        $nombre = $col['Field'];
        $tipo = $col['Type'];
        $null = $col['Null'];
        $key = $col['Key'];
        $default = $col['Default'];
        
        echo sprintf("   %-25s %-20s Null: %-3s Key: %-3s Default: %s\n", 
            $nombre, $tipo, $null, $key, $default ?? 'NULL');
    }
    echo "\n";
    
    // 3. Verificar columnas específicas
    $columnasClave = ['estadoFactura', 'numeroAutorizacion', 'fechaAutorizacion', 'respuesta_sri'];
    echo "3. Verificación de columnas clave:\n";
    foreach ($columnasClave as $col) {
        $existe = false;
        foreach ($columnas as $c) {
            if ($c['Field'] === $col) {
                $existe = true;
                break;
            }
        }
        echo "   $col: " . ($existe ? "✅ EXISTE" : "❌ NO EXISTE") . "\n";
        
        if (function_exists('db_has_column')) {
            $detectada = db_has_column($pdo, 'facturas_venta', $col);
            echo "      (db_has_column detecta: " . ($detectada ? "SÍ" : "NO") . ")\n";
        }
    }
    echo "\n";
    
    // 4. Ver una factura de ejemplo
    echo "4. Ejemplo de factura (última):\n";
    $stmt = $pdo->query("SELECT id, numeroFactura, estadoFactura, numeroAutorizacion FROM facturas_venta ORDER BY id DESC LIMIT 1");
    $ejemplo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($ejemplo) {
        print_r($ejemplo);
    } else {
        echo "   No hay facturas en la BD\n";
    }
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>ERROR:</strong> " . $e->getMessage() . "\n";
}

echo "</pre>";
