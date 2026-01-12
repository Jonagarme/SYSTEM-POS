<?php
/**
 * Shared navbar component
 */
$root = $root ?? '';
?>
<header class="navbar">
    <div class="navbar-left">
        <button id="toggle-sidebar" class="toggle-btn"><i class="fas fa-bars"></i></button>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar reporte, factura, producto...">
        </div>
    </div>
    <div class="navbar-right">
        <div class="status-indicators">
            <span class="badge badge-success"><i class="fas fa-door-open"></i> Caja Abierta</span>
            <span class="badge badge-primary"><i class="fas fa-wifi"></i> Online</span>
        </div>
        <div class="user-profile" id="userDropdownTrigger"
            style="position: relative; cursor: pointer; display: flex; align-items: center; gap: 12px; padding: 5px 10px; border-radius: 8px;">
            <img src="https://ui-avatars.com/api/?name=Usuario+Administrador&background=0d6efd&color=fff" alt="User"
                style="width: 35px; border-radius: 50%;">
            <div class="user-info">
                <span class="user-name"
                    style="font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; color: #1e293b;">
                    <?php echo $_SESSION['user_name'] ?? 'Usuario Administrador'; ?> <i class="fas fa-chevron-down"
                        style="font-size: 0.7rem; color: #64748b;"></i>
                </span>
                <span class="user-role" style="font-size: 0.75rem; color: #64748b; display: block;">
                    <?php echo $_SESSION['role'] ?? 'Administrador'; ?>
                </span>
            </div>

            <!-- Profile Dropdown Menu -->
            <div class="profile-dropdown" id="userDropdownMenu">
                <div class="dropdown-header">PERFIL DE USUARIO</div>
                <a href="<?php echo $root; ?>modules/usuarios/mi_perfil.php" class="dropdown-item">
                    <i class="fas fa-user-edit"></i> Mi Perfil
                </a>

                <div class="dropdown-divider"></div>
                <div class="dropdown-header">CONFIGURACIÓN</div>
                <a href="<?php echo $root; ?>modules/config/configuracion.php" class="dropdown-item">
                    <i class="fas fa-building"></i> Configuración Empresa
                </a>
                <a href="<?php echo $root; ?>modules/config/impuestos.php" class="dropdown-item">
                    <i class="fas fa-percent"></i> Gestión de Impuestos
                </a>

                <div class="dropdown-divider"></div>
                <a href="<?php echo $root; ?>logout.php" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</header>

<style>
    .profile-dropdown {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 220px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        padding: 10px 0;
        z-index: 1000;
        display: none;
        transform-origin: top right;
        animation: dropdownAnim 0.2s ease-out;
    }

    .profile-dropdown.show {
        display: block;
    }

    @keyframes dropdownAnim {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .dropdown-header {
        padding: 8px 20px;
        font-size: 0.65rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px;
        font-size: 0.9rem;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s;
    }

    .dropdown-item:hover {
        background: #f1f5f9;
        color: #0d6efd;
    }

    .dropdown-item i {
        width: 20px;
        text-align: center;
        color: #64748b;
    }

    .dropdown-item:hover i {
        color: #0d6efd;
    }

    .dropdown-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 8px 0;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .text-danger:hover {
        background: #fff1f2 !important;
    }

    .text-danger i {
        color: #dc3545 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const trigger = document.getElementById('userDropdownTrigger');
        const menu = document.getElementById('userDropdownMenu');

        if (trigger && menu) {
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('show');
            });

            document.addEventListener('click', function () {
                menu.classList.remove('show');
            });
        }
    });
</script>