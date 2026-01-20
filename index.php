<?php
/**
 * Main index file for the POS system
 */
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch real stats usando fechas calculadas en PHP para evitar descuadres de zona horaria
$today = date('Y-m-d');
$firstOfMonth = date('Y-m-01');
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

// 1. Ventas del día
$sales_today = $pdo->query("SELECT SUM(total) FROM facturas_venta WHERE DATE(fechaEmision) = '$today' AND anulado = 0")->fetchColumn() ?: 0;
$sales_count = $pdo->query("SELECT COUNT(*) FROM facturas_venta WHERE DATE(fechaEmision) = '$today' AND anulado = 0")->fetchColumn();

// 2. Nuevos clientes (Columna: creadoDate)
$new_clients = $pdo->query("SELECT COUNT(*) FROM clientes WHERE creadoDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn() ?: 0;

// 3. Stock bajo (Columnas: stock, stockMinimo)
$low_stock = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock <= stockMinimo AND anulado = 0")->fetchColumn() ?: 0;

// 4. Ventas de la semana y el mes
$sales_week = $pdo->query("SELECT SUM(total) FROM facturas_venta WHERE fechaEmision >= '$sevenDaysAgo' AND anulado = 0")->fetchColumn() ?: 0;
$sales_month = $pdo->query("SELECT SUM(total) FROM facturas_venta WHERE fechaEmision >= '$firstOfMonth' AND anulado = 0")->fetchColumn() ?: 0;

// 5. Datos para el gráfico (últimos 7 días)
$stmtChart = $pdo->prepare("
    SELECT DATE(fechaEmision) as fecha, SUM(total) as total 
    FROM facturas_venta 
    WHERE fechaEmision >= ? AND anulado = 0
    GROUP BY DATE(fechaEmision) 
    ORDER BY fecha ASC
");
$stmtChart->execute([$sevenDaysAgo]);
$chart_data = $stmtChart->fetchAll(PDO::FETCH_KEY_PAIR);

// Rellenar días faltantes con 0
$daily_sales = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $daily_sales[$d] = $chart_data[$d] ?? 0;
}

// 6. Últimas ventas
$stmtSales = $pdo->query("
    SELECT f.*, 
    COALESCE(CONCAT(c.nombres, ' ', c.apellidos), 'CONSUMIDOR FINAL') as cliente_nombre 
    FROM facturas_venta f 
    LEFT JOIN clientes c ON f.idCliente = c.id 
    WHERE f.anulado = 0 
    ORDER BY f.fechaEmision DESC, f.id DESC LIMIT 8
");
$recent_sales = $stmtSales->fetchAll();

$_SESSION['user_name'] = "Usuario Administrador";
$_SESSION['role'] = "Administrador";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema POS | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <div class="app-container">
        <?php
        $root = './';
        $current_page = 'dashboard';
        include 'includes/sidebar.php';
        ?>

        <main class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <div class="content-wrapper">
                <div class="page-header">
                    <h1>Dashboard</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </nav>
                </div>

                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-details">
                            <h3 id="stat-sales-today">$ <?php echo number_format($sales_today, 2); ?></h3>
                            <p>Ventas del Día</p>
                        </div>
                        <div class="stat-trend positive">Hoy</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-details">
                            <h3 id="stat-sales-count"><?php echo number_format($sales_today > 0 ? $sales_count : 0); ?>
                            </h3>
                            <p>Ventas Realizadas</p>
                        </div>
                        <div class="stat-trend positive">Hoy</div>
                    </div>
                    <div class="stat-card cyan">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-details">
                            <h3 id="stat-new-clients"><?php echo number_format($new_clients); ?></h3>
                            <p>Nuevos Clientes</p>
                        </div>
                        <div class="stat-trend">Últimos 30 días</div>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div class="stat-details">
                            <h3 id="stat-low-stock"><?php echo number_format($low_stock); ?></h3>
                            <p>Stock Bajo</p>
                        </div>
                        <div class="stat-trend negative">Alerta</div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="card main-chart">
                        <div class="card-header">
                            <h2>Ventas de la Semana</h2>
                            <div style="display: flex; gap: 15px;">
                                <div style="text-align: right;">
                                    <span
                                        style="display: block; font-size: 0.7rem; color: #64748b; font-weight: 600;">ESTA
                                        SEMANA</span>
                                    <strong id="stat-sales-week" style="color: #0061f2;">$
                                        <?php echo number_format($sales_week, 2); ?></strong>
                                </div>
                                <div style="text-align: right; border-left: 1px solid #e2e8f0; padding-left: 15px;">
                                    <span
                                        style="display: block; font-size: 0.7rem; color: #64748b; font-weight: 600;">ESTE
                                        MES</span>
                                    <strong id="stat-sales-month" style="color: #10b981;">$
                                        <?php echo number_format($sales_month, 2); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="dashboard-sales-chart">
                            <div class="weekly-bars-container"
                                style="display: flex; align-items: flex-end; justify-content: space-between; height: 180px; padding: 20px 10px; gap: 10px;">
                                <?php
                                $max_val = max($daily_sales) ?: 1;
                                foreach ($daily_sales as $date => $total):
                                    $height = ($total / $max_val) * 100;
                                    $day_name = date('D', strtotime($date));
                                    ?>
                                    <div class="bar-item"
                                        style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; height: 100%;">
                                        <div class="bar-value"
                                            style="font-size: 0.65rem; font-weight: 700; color: #1e293b;">
                                            $<?php echo number_format($total, 0); ?></div>
                                        <div class="bar-fill"
                                            style="width: 100%; height: <?php echo $height; ?>%; background: linear-gradient(to top, #0061f2, #60a5fa); border-radius: 4px; min-height: 4px; transition: height 0.3s ease;">
                                        </div>
                                        <div class="bar-label"
                                            style="font-size: 0.65rem; color: #64748b; font-weight: 600; text-transform: uppercase;">
                                            <?php echo $day_name; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card recent-sales">
                        <div class="card-header">
                            <h2>Últimas Ventas</h2>
                            <a href="modules/ventas/index.php" class="view-all">Ver todas</a>
                        </div>
                        <div class="card-body">
                            <table class="table" id="table-recent-sales">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_sales as $sale): ?>
                                        <tr>
                                            <td style="font-weight: 600; color: #0061f2; font-size: 0.8rem;">
                                                <?php echo $sale['numeroFactura']; ?>
                                            </td>
                                            <td style="font-size: 0.8rem; font-weight: 500;">
                                                <?php echo htmlspecialchars($sale['cliente_nombre']); ?>
                                            </td>
                                            <td style="font-weight: 700; font-size: 0.8rem;">
                                                $<?php echo number_format($sale['total'], 2); ?></td>
                                            <td><span class="badge badge-success"><?php echo $sale['estado']; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php include 'includes/scripts.php'; ?>
    <script>
        /**
         * Real-time dashboard update
         */
        async function refreshDashboardStats() {
            try {
                const response = await fetch('modules/dashboard/api_dashboard_stats.php');
                const res = await response.json();

                if (res.success) {
                    const data = res.data;

                    // Update main cards
                    document.getElementById('stat-sales-today').innerText = '$ ' + parseFloat(data.sales_today_total).toLocaleString('en-US', { minimumFractionDigits: 2 });
                    document.getElementById('stat-sales-count').innerText = data.sales_today_count;
                    document.getElementById('stat-new-clients').innerText = data.new_clients;
                    document.getElementById('stat-low-stock').innerText = data.low_stock;

                    // Update week/month totals
                    document.getElementById('stat-sales-week').innerText = '$ ' + parseFloat(data.sales_week).toLocaleString('en-US', { minimumFractionDigits: 2 });
                    document.getElementById('stat-sales-month').innerText = '$ ' + parseFloat(data.sales_month).toLocaleString('en-US', { minimumFractionDigits: 2 });

                    // Update Chart
                    const chartContainer = document.getElementById('dashboard-sales-chart').querySelector('.weekly-bars-container');
                    const maxVal = Math.max(...data.chart.map(d => d.total)) || 1;

                    const bars = chartContainer.querySelectorAll('.bar-item');
                    data.chart.forEach((day, index) => {
                        if (bars[index]) {
                            const barFill = bars[index].querySelector('.bar-fill');
                            const barValue = bars[index].querySelector('.bar-value');
                            const height = (day.total / maxVal) * 100;

                            barFill.style.height = height + '%';
                            barValue.innerText = '$' + Math.round(day.total).toLocaleString();
                        }
                    });

                    // Update Recent Sales Table
                    const tbody = document.getElementById('table-recent-sales').querySelector('tbody');
                    let newHtml = '';
                    data.recent_sales.forEach(sale => {
                        newHtml += `
                            <tr>
                                <td style="font-weight: 600; color: #0061f2; font-size: 0.8rem;">${sale.numeroFactura}</td>
                                <td style="font-size: 0.8rem; font-weight: 500;">${sale.cliente_nombre}</td>
                                <td style="font-weight: 700; font-size: 0.8rem;">$ ${parseFloat(sale.total).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                                <td><span class="badge badge-success">${sale.estado}</span></td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = newHtml;
                }
            } catch (error) {
                console.warn('Dashboard fetch error:', error);
            }
        }

        // Poll every 10 seconds
        setInterval(refreshDashboardStats, 10000);
    </script>
</body>

</html>