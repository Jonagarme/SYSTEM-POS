<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
session_start();

try {
    $today = date('Y-m-d');
    $firstOfMonth = date('Y-m-01');
    $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

    // 1. Stats principales
    $stats = [];

    // Ventas del día
    $stats['sales_today_total'] = $pdo->query("SELECT SUM(total) FROM facturas_venta WHERE DATE(fechaEmision) = '$today' AND anulado = 0")->fetchColumn() ?: 0;
    $stats['sales_today_count'] = $pdo->query("SELECT COUNT(*) FROM facturas_venta WHERE DATE(fechaEmision) = '$today' AND anulado = 0")->fetchColumn() ?: 0;

    // Nuevos clientes
    $stats['new_clients'] = $pdo->query("SELECT COUNT(*) FROM clientes WHERE creadoDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn() ?: 0;

    // Stock bajo
    $stats['low_stock'] = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock <= stockMinimo AND anulado = 0")->fetchColumn() ?: 0;

    // Ventas semana/mes
    $stats['sales_week'] = $pdo->query("SELECT SUM(total) FROM facturas_venta WHERE fechaEmision >= '$sevenDaysAgo' AND anulado = 0")->fetchColumn() ?: 0;
    $stats['sales_month'] = $pdo->query("SELECT SUM(total) FROM facturas_venta WHERE fechaEmision >= '$firstOfMonth' AND anulado = 0")->fetchColumn() ?: 0;

    // 2. Gráfico (últimos 7 días)
    $stmtChart = $pdo->prepare("
        SELECT DATE(fechaEmision) as fecha, SUM(total) as total 
        FROM facturas_venta 
        WHERE fechaEmision >= ? AND anulado = 0
        GROUP BY DATE(fechaEmision) 
        ORDER BY fecha ASC
    ");
    $stmtChart->execute([$sevenDaysAgo]);
    $chart_data_raw = $stmtChart->fetchAll(PDO::FETCH_KEY_PAIR);

    $chart_data = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $val = (float) ($chart_data_raw[$d] ?? 0);
        $chart_data[] = [
            'fecha' => $d,
            'label' => date('D', strtotime($d)),
            'total' => $val
        ];
    }
    $stats['chart'] = $chart_data;

    // 3. Últimas ventas
    $stmtSales = $pdo->query("
        SELECT f.numeroFactura, f.total, f.estado, 
        COALESCE(CONCAT(c.nombres, ' ', c.apellidos), 'CONSUMIDOR FINAL') as cliente_nombre 
        FROM facturas_venta f 
        LEFT JOIN clientes c ON f.idCliente = c.id 
        WHERE f.anulado = 0 
        ORDER BY f.fechaEmision DESC, f.id DESC LIMIT 8
    ");
    $stats['recent_sales'] = $stmtSales->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
