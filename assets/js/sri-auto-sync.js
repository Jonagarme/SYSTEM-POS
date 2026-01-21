/**
 * Sistema de Sincronización Automática con el SRI
 * Sincroniza el estado de las facturas electrónicas cada 20 minutos
 */

class SRIAutoSync {
    constructor() {
        this.syncInterval = 20 * 60 * 1000; // 20 minutos en milisegundos
        this.syncTimer = null;
        this.isSyncing = false;
        this.lastSyncTime = null;
        this.enabled = true;
        
        this.init();
    }

    init() {
        // Cargar el último tiempo de sincronización desde localStorage
        const lastSync = localStorage.getItem('sri_last_sync');
        if (lastSync) {
            this.lastSyncTime = new Date(lastSync);
        }

        // Iniciar el temporizador automático
        this.startAutoSync();

        // Agregar indicador visual en la página
        this.addSyncIndicator();

        console.log('✅ Sistema de sincronización automática SRI iniciado (cada 20 minutos)');
    }

    startAutoSync() {
        // Limpiar cualquier temporizador anterior
        if (this.syncTimer) {
            clearInterval(this.syncTimer);
        }

        // Ejecutar sincronización inmediatamente si han pasado más de 20 minutos
        const now = new Date();
        if (!this.lastSyncTime || (now - this.lastSyncTime) >= this.syncInterval) {
            this.performSync(true); // true = sincronización automática silenciosa
        }

        // Configurar sincronización periódica
        this.syncTimer = setInterval(() => {
            if (this.enabled && !this.isSyncing) {
                this.performSync(true);
            }
        }, this.syncInterval);
    }

    async performSync(isAutomatic = false) {
        if (this.isSyncing) {
            console.log('⚠️ Ya hay una sincronización en curso');
            return;
        }

        this.isSyncing = true;
        this.updateIndicator('syncing');

        try {
            console.log(`🔄 Iniciando sincronización ${isAutomatic ? 'automática' : 'manual'} con el SRI...`);

            const response = await fetch('cron_update_status.php?format=json');
            const data = await response.json();

            if (data.success) {
                const results = data.results;
                this.lastSyncTime = new Date();
                localStorage.setItem('sri_last_sync', this.lastSyncTime.toISOString());

                console.log(`✅ Sincronización completada:`, results);

                // Solo mostrar notificación si es automática Y hay actualizaciones
                if (isAutomatic && results.updated > 0) {
                    this.showNotification(
                        `${results.updated} factura(s) autorizada(s)`,
                        'success'
                    );
                    
                    // Recargar la página si estamos en facturas electrónicas
                    setTimeout(() => {
                        if (window.location.pathname.includes('facturas_electronicas.php')) {
                            location.reload();
                        }
                    }, 2000);
                }

                this.updateIndicator('success');
                
                return data;
            } else {
                throw new Error(data.error || 'Error en la sincronización');
            }
        } catch (error) {
            console.error('❌ Error en sincronización automática:', error);
            this.updateIndicator('error');
            
            // Solo mostrar error si es sincronización manual
            if (!isAutomatic) {
                this.showNotification('Error al sincronizar con el SRI', 'error');
            }
            
            throw error;
        } finally {
            this.isSyncing = false;
            
            // Volver al estado normal después de 3 segundos
            setTimeout(() => {
                this.updateIndicator('idle');
            }, 3000);
        }
    }

    addSyncIndicator() {
        // Crear indicador visual en la esquina inferior derecha
        const indicator = document.createElement('div');
        indicator.id = 'sri-sync-indicator';
        indicator.innerHTML = `
            <div class="sync-status">
                <i class="fas fa-satellite-dish"></i>
                <span class="sync-text">SRI</span>
            </div>
            <div class="sync-tooltip">
                <div id="sync-status-text">Sincronización automática activa</div>
                <div id="sync-last-time" style="font-size: 0.75rem; opacity: 0.8; margin-top: 3px;"></div>
            </div>
        `;

        // Estilos del indicador
        const style = document.createElement('style');
        style.textContent = `
            #sri-sync-indicator {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 1000;
                cursor: pointer;
            }

            .sync-status {
                background: #64748b;
                color: white;
                padding: 10px 15px;
                border-radius: 25px;
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.85rem;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }

            .sync-status i {
                font-size: 1rem;
            }

            #sri-sync-indicator:hover .sync-status {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            }

            #sri-sync-indicator.syncing .sync-status {
                background: #3b82f6;
            }

            #sri-sync-indicator.syncing .sync-status i {
                animation: spin 1s linear infinite;
            }

            #sri-sync-indicator.success .sync-status {
                background: #10b981;
            }

            #sri-sync-indicator.error .sync-status {
                background: #ef4444;
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            .sync-tooltip {
                display: none;
                position: absolute;
                bottom: 100%;
                right: 0;
                margin-bottom: 10px;
                background: white;
                padding: 12px 15px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                white-space: nowrap;
                font-size: 0.85rem;
                color: #475569;
            }

            #sri-sync-indicator:hover .sync-tooltip {
                display: block;
            }

            .sync-tooltip::after {
                content: '';
                position: absolute;
                top: 100%;
                right: 20px;
                border: 6px solid transparent;
                border-top-color: white;
            }

            @media (max-width: 768px) {
                #sri-sync-indicator {
                    bottom: 15px;
                    right: 15px;
                }

                .sync-status {
                    padding: 8px 12px;
                    font-size: 0.75rem;
                }

                .sync-tooltip {
                    right: auto;
                    left: 50%;
                    transform: translateX(-50%);
                    bottom: calc(100% + 8px);
                }

                .sync-tooltip::after {
                    right: auto;
                    left: 50%;
                    transform: translateX(-50%);
                }
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(indicator);

        // Click para sincronizar manualmente
        indicator.addEventListener('click', () => {
            if (!this.isSyncing) {
                this.performSync(false).then(() => {
                    this.showNotification('Sincronización completada', 'success');
                }).catch(() => {
                    this.showNotification('Error al sincronizar', 'error');
                });
            }
        });

        this.updateLastSyncTime();
    }

    updateIndicator(state) {
        const indicator = document.getElementById('sri-sync-indicator');
        if (!indicator) return;

        indicator.className = state;

        const statusText = document.getElementById('sync-status-text');
        if (statusText) {
            switch(state) {
                case 'syncing':
                    statusText.textContent = 'Sincronizando con el SRI...';
                    break;
                case 'success':
                    statusText.textContent = '✅ Sincronización exitosa';
                    break;
                case 'error':
                    statusText.textContent = '❌ Error en sincronización';
                    break;
                default:
                    statusText.textContent = 'Sincronización automática activa';
            }
        }

        this.updateLastSyncTime();
    }

    updateLastSyncTime() {
        const lastTimeElement = document.getElementById('sync-last-time');
        if (lastTimeElement && this.lastSyncTime) {
            const minutes = Math.floor((new Date() - this.lastSyncTime) / 60000);
            if (minutes < 1) {
                lastTimeElement.textContent = 'Última sincronización: hace un momento';
            } else if (minutes === 1) {
                lastTimeElement.textContent = 'Última sincronización: hace 1 minuto';
            } else {
                lastTimeElement.textContent = `Última sincronización: hace ${minutes} minutos`;
            }
        }
    }

    showNotification(message, type = 'info') {
        // Crear notificación toast
        const toast = document.createElement('div');
        toast.className = `sri-toast sri-toast-${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;

        // Estilos del toast
        if (!document.getElementById('sri-toast-styles')) {
            const style = document.createElement('style');
            style.id = 'sri-toast-styles';
            style.textContent = `
                .sri-toast {
                    position: fixed;
                    top: 80px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 8px;
                    color: white;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 0.9rem;
                    font-weight: 500;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    z-index: 10000;
                    animation: slideIn 0.3s ease;
                }

                .sri-toast-success { background: #10b981; }
                .sri-toast-error { background: #ef4444; }
                .sri-toast-info { background: #3b82f6; }

                @keyframes slideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }

                @keyframes slideOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(toast);

        // Eliminar después de 4 segundos
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    stop() {
        if (this.syncTimer) {
            clearInterval(this.syncTimer);
            this.syncTimer = null;
        }
        this.enabled = false;
        console.log('⏸️ Sincronización automática detenida');
    }

    start() {
        this.enabled = true;
        this.startAutoSync();
        console.log('▶️ Sincronización automática reiniciada');
    }
}

// Inicializar automáticamente cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.sriAutoSync = new SRIAutoSync();
    });
} else {
    window.sriAutoSync = new SRIAutoSync();
}
