<?php
/**
 * Shared sidebar component
 * Requires $root variable to be defined for correct paths
 */
$root = $root ?? '';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-cash-register"></i>
            <span>Sistema POS</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo $root; ?>index.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
            </li>
            <li class="has-submenu <?php echo (strpos($current_page, 'producto') !== false) ? 'open active' : ''; ?>">
                <a href="#"><i class="fas fa-box"></i> <span>Productos</span> <i
                        class="fas fa-chevron-right arrow"></i></a>
                <ul class="submenu">
                    <li><a href="<?php echo $root; ?>modules/productos/index.php">Ver Productos</a></li>
                    <li><a href="<?php echo $root; ?>modules/productos/nuevo.php">Nuevo Producto</a></li>
                    <li><a href="<?php echo $root; ?>modules/productos/ingreso_xml.php">Ingreso XML</a></li>
                    <li><a href="<?php echo $root; ?>modules/productos/ubicaciones.php">Ubicaciones</a></li>
                </ul>
            </li>
            <li
                class="has-submenu <?php echo (strpos($current_page, 'ventas') !== false || strpos($current_page, 'venta_pos') !== false) ? 'open active' : ''; ?>">
                <a href="#"><i class="fas fa-shopping-cart"></i> <span>Ventas</span> <i
                        class="fas fa-chevron-right arrow"></i></a>
                <ul class="submenu">
                    <li class="submenu-header"
                        style="padding: 10px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Punto de Venta</li>
                    <li><a href="<?php echo $root; ?>modules/ventas/pos.php"><i class="fas fa-cash-register"
                                style="font-size: 0.75rem; margin-right: 8px;"></i> Punto de Venta</a></li>

                    <li class="submenu-header"
                        style="padding: 15px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Gestión de Ventas</li>
                    <li><a href="<?php echo $root; ?>modules/ventas/index.php"><i class="fas fa-list-ul"
                                style="font-size: 0.75rem; margin-right: 8px;"></i> Ver Ventas</a></li>
                    <li><a href="<?php echo $root; ?>modules/ventas/devoluciones.php"><i class="fas fa-undo"
                                style="font-size: 0.75rem; margin-right: 8px;"></i>
                            Devoluciones</a></li>
                    <li><a href="<?php echo $root; ?>modules/ventas/consolidado_ventas.php"><i class="fas fa-chart-line"
                                style="font-size: 0.75rem; margin-right: 8px;"></i>
                            Consolidado de Ventas</a></li>

                    <li class="submenu-header"
                        style="padding: 15px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Facturación</li>
                    <li><a href="<?php echo $root; ?>modules/ventas/facturas_electronicas.php"><i
                                class="fas fa-file-invoice-dollar" style="font-size: 0.75rem; margin-right: 8px;"></i>
                            Facturas Electrónicas</a></li>
                </ul>
            </li>
            <li class="has-submenu <?php echo (strpos($current_page, 'inventario') !== false) ? 'open active' : ''; ?>">
                <a href="#"><i class="fas fa-warehouse"></i> <span>Inventario</span> <i
                        class="fas fa-chevron-right arrow"></i></a>
                <ul class="submenu">
                    <li class="submenu-header"
                        style="padding: 10px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Gestión de Stock</li>
                    <li><a href="<?php echo $root; ?>modules/inventario/kardex.php">Kardex</a></li>
                    <li><a href="<?php echo $root; ?>modules/inventario/ajustes.php">Ajustes de Inventario</a></li>
                    <li><a href="<?php echo $root; ?>modules/inventario/config_stock.php">Configuración de Stock</a>
                    </li>

                    <li class="submenu-header"
                        style="padding: 15px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Compras y Órdenes</li>
                    <li><a href="<?php echo $root; ?>modules/inventario/compras.php">Compras</a></li>
                    <li><a href="<?php echo $root; ?>modules/inventario/ordenes_compra.php">Órdenes de Compra (PO)</a>
                    </li>
                    <li><a href="<?php echo $root; ?>modules/inventario/nueva_orden.php">Nueva Orden de Compra</a></li>

                    <li class="submenu-header"
                        style="padding: 15px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Transferencias</li>
                    <li><a href="<?php echo $root; ?>modules/inventario/transferencias.php">Transferencias de Stock</a>
                    </li>
                    <li><a href="<?php echo $root; ?>modules/inventario/ubicaciones.php">Gestionar Ubicaciones</a></li>

                    <li class="submenu-header"
                        style="padding: 15px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Reportes</li>
                    <li><a href="<?php echo $root; ?>modules/inventario/reportes_inventario.php">Reportes de
                            Inventario</a></li>
                    <li><a href="<?php echo $root; ?>modules/inventario/reporte_caducados.php">Productos Caducados</a>
                    </li>
                    <li><a href="<?php echo $root; ?>modules/inventario/reporte_valorado.php">Inventario Valorado</a>
                    </li>
                </ul>
            </li>
            <li class="<?php echo ($current_page == 'clientes') ? 'active' : ''; ?>">
                <a href="<?php echo $root; ?>modules/clientes/index.php"><i class="fas fa-users"></i>
                    <span>Clientes</span></a>
            </li>
            <li class="has-submenu <?php echo (strpos($current_page, 'caja') !== false) ? 'open active' : ''; ?>">
                <a href="#"><i class="fas fa-cash-register"></i> <span>Caja</span> <i
                        class="fas fa-chevron-right arrow"></i></a>
                <ul class="submenu">
                    <li class="submenu-header"
                        style="padding: 10px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Gestión de Cajas</li>
                    <li><a href="<?php echo $root; ?>modules/caja/index.php"><i class="fas fa-cog"
                                style="color: #f59e0b; margin-right: 8px;"></i> Gestión de Cajas</a></li>

                    <li class="submenu-header"
                        style="padding: 15px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Operaciones de Caja</li>
                    <li><a href="<?php echo $root; ?>modules/caja/estado.php"><i class="fas fa-eye"
                                style="color: #f59e0b; margin-right: 8px;"></i> Estado de Caja</a></li>
                    <li><a href="<?php echo $root; ?>modules/caja/movimientos.php"><i class="fas fa-exchange-alt"
                                style="color: #f59e0b; margin-right: 8px;"></i> Movimientos</a></li>
                    <li><a href="<?php echo $root; ?>modules/caja/aperturas.php"><i class="fas fa-lock-open"
                                style="color: #f59e0b; margin-right: 8px;"></i> Aperturas</a></li>
                    <li><a href="<?php echo $root; ?>modules/caja/cierres.php"><i class="fas fa-lock"
                                style="color: #f59e0b; margin-right: 8px;"></i> Cierres Diarios</a></li>
                </ul>
            </li>

            <li class="has-submenu <?php echo (strpos($current_page, 'usuarios') !== false) ? 'open active' : ''; ?>">
                <a href="#"><i class="fas fa-user-shield"></i> <span>Usuarios</span> <i
                        class="fas fa-chevron-right arrow"></i></a>
                <ul class="submenu">
                    <li class="submenu-header"
                        style="padding: 10px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Gestión de Usuarios</li>
                    <li><a href="<?php echo $root; ?>modules/usuarios/index.php"><i class="fas fa-list"
                                style="color: #64748b; margin-right: 8px;"></i> Lista de Usuarios</a></li>
                    <li><a href="<?php echo $root; ?>modules/usuarios/nuevo.php"><i class="fas fa-user-plus"
                                style="color: #64748b; margin-right: 8px;"></i> Crear Usuario</a></li>

                    <li class="submenu-header"
                        style="padding: 15px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Roles y Permisos</li>
                    <li><a href="<?php echo $root; ?>modules/usuarios/roles.php"><i class="fas fa-user-tag"
                                style="color: #64748b; margin-right: 8px;"></i> Lista de Roles</a></li>
                    <li><a href="<?php echo $root; ?>modules/usuarios/nuevo_rol.php"><i class="fas fa-plus-circle"
                                style="color: #64748b; margin-right: 8px;"></i> Crear Rol</a></li>

                    <li class="submenu-header"
                        style="padding: 15px 20px 5px 52px; font-size: 0.65rem; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        Auditoría</li>
                    <li><a href="<?php echo $root; ?>modules/usuarios/auditoria.php"><i class="fas fa-clipboard-list"
                                style="color: #64748b; margin-right: 8px;"></i> Registro de Auditoría</a></li>
                </ul>
            </li>

            <li class="<?php echo ($current_page == 'config') ? 'active' : ''; ?>">
                <a href="#"><i class="fas fa-cog"></i> <span>Configuración</span></a>
            </li>
        </ul>
    </nav>
</aside>