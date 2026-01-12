<?php
header('Content-Type: application/json');

// Requerir conexión a la base de datos
$root = '../../';
require_once $root . 'includes/db.php';

$action = $_GET['action'] ?? 'buscar';

/**
 * Función real de búsqueda en base de datos
 */
function buscar_productos_db($pdo, $nombre, $codigo = '', $umbral = 0.75)
{
    $resultados = [];

    // 1. Buscar por código exacto (Principal o Auxiliar)
    if (!empty($codigo)) {
        // Usando nombres de columna reales detectados en index.php: codigoPrincipal, stock, precioVenta
        $stmt = $pdo->prepare("SELECT id, nombre, codigoPrincipal as codigo_principal, stock, precioVenta as precio FROM productos WHERE codigoPrincipal = ? OR codigoAuxiliar = ? LIMIT 5");
        $stmt->execute([$codigo, $codigo]);
        $exactos = $stmt->fetchAll();
        foreach ($exactos as $prod) {
            $resultados[] = array_merge($prod, [
                'score_total' => 100,
                'tipo_coincidencia' => 'Código Exacto'
            ]);
        }
    }

    // 2. Buscar por similitud de nombre si no hay suficientes exactos
    if (count($resultados) < 5 && !empty($nombre)) {
        $searchName = "%" . trim($nombre) . "%";
        $words = explode(' ', trim($nombre));
        $partialSearch = "%" . ($words[0] ?? '') . "%";

        // Preparar exclusión de IDs ya encontrados por código exacto
        $excludeIds = [0]; // Dummy zero to avoid empty IN clause
        foreach ($resultados as $r)
            $excludeIds[] = (int) $r['id'];
        $excludeStr = implode(',', $excludeIds);

        // Búsqueda simplificada para evitar errores de alias en subconsultas
        $stmt = $pdo->prepare("SELECT id, nombre, codigoPrincipal as codigo_principal, stock, precioVenta as precio 
                                FROM productos 
                                WHERE (nombre LIKE ? OR nombre LIKE ?) 
                                AND id NOT IN ($excludeStr)
                                AND anulado = 0
                                LIMIT 15");
        $stmt->execute([$searchName, $partialSearch]);
        $similares = $stmt->fetchAll();

        foreach ($similares as $prod) {
            similar_text(strtoupper($nombre), strtoupper($prod['nombre']), $perc);

            // Si la similitud es mayor al 40% (umbral flexible para medicina) o contiene palabras clave
            if ($perc / 100 >= $umbral || (isset($words[0]) && strlen($words[0]) > 3 && stripos($prod['nombre'], $words[0]) !== false)) {
                $resultados[] = array_merge($prod, [
                    'score_total' => round($perc, 1),
                    'tipo_coincidencia' => 'Similitud de Nombre'
                ]);
            }
        }
    }

    // Ordenar resultados por score descendente
    usort($resultados, function ($a, $b) {
        return $b['score_total'] <=> $a['score_total'];
    });

    return array_slice($resultados, 0, 8);
}

switch ($action) {
    case 'buscar':
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $nombre = $data['nombre'] ?? '';
        $codigo = $data['codigo'] ?? '';
        $umbral = (float) ($data['umbral'] ?? 0.75);

        if (!$nombre && !$codigo) {
            echo json_encode(['success' => false, 'error' => 'Nombre o código requerido']);
            break;
        }

        $resultados = buscar_productos_db($pdo, $nombre, $codigo, $umbral);
        echo json_encode(['success' => true, 'productos' => $resultados]);
        break;

    case 'vincular':
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $producto_id = $data['producto_id'] ?? null;
        $codigo_nuevo = $data['codigo'] ?? '';

        if ($producto_id && $codigo_nuevo) {
            try {
                // Vincular como código auxiliar si no tiene uno o reemplazar
                // Nota: Asumimos 'codigoAuxiliar' basado en el patrón camelCase detectado
                $stmt = $pdo->prepare("UPDATE productos SET codigoAuxiliar = ? WHERE id = ?");
                $stmt->execute([$codigo_nuevo, $producto_id]);

                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Código vinculado correctamente',
                    'codigo_alternativo' => ['codigo' => $codigo_nuevo, 'producto_id' => $producto_id]
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Faltan datos']);
        }
        break;

    case 'obtener':
        $codigo = $_GET['codigo'] ?? '';
        $stmt = $pdo->prepare("SELECT id, nombre, codigoPrincipal as codigo_principal, stock, precioVenta as precio FROM productos WHERE (codigoPrincipal = ? OR codigoAuxiliar = ?) AND anulado = 0");
        $stmt->execute([$codigo, $codigo]);
        $prod = $stmt->fetch();

        if ($prod) {
            echo json_encode(['success' => true, 'producto' => $prod]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No encontrado']);
        }
        break;

    case 'lote':
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $productos_entrada = $data['productos'] ?? [];
        $resultados = [];

        foreach ($productos_entrada as $prod) {
            $similares = buscar_productos_db($pdo, $prod['nombre'], $prod['codigo']);
            $resultados[] = [
                'codigo_entrada' => $prod['codigo'],
                'nombre_entrada' => $prod['nombre'],
                'similares' => $similares,
                'tiene_similares' => !empty($similares)
            ];
        }
        echo json_encode(['success' => true, 'resultados' => $resultados]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}
