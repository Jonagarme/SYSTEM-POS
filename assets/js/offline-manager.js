/**
 * Logipharm Offline Manager (V5 - Atomic Transaction Fix)
 */

// Bump version drastically to force clean schema
const db = new Dexie("Logipharm_POS_System_V5");

db.version(1).stores({
    productos: 'id, codigoPrincipal, nombre', // Simple indices
    clientes: 'id, cedula, nombre',
    ventas_pendientes: '++id, estado'
});

const OfflineManager = {
    isOnline: navigator.onLine,
    isInitialized: false,
    isSyncing: false,

    async init() {
        if (this.isInitialized) return;
        
        try {
            // Register SW quietly
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('../../sw.js').catch(e => console.log('SW Error:', e));
            }

            // Open DB
            await db.open();
            
            // Listeners
            window.addEventListener('online', () => this.handleStatusChange(true));
            window.addEventListener('offline', () => this.handleStatusChange(false));
            
            this.updateIndicator();
            this.isInitialized = true;

            // Auto-sync if online
            if (this.isOnline) {
                setTimeout(() => this.syncDataFromServer(), 500);
            }
        } catch (err) {
            console.error("Init Error:", err);
        }
    },

    async handleStatusChange(online) {
        this.isOnline = online;
        this.updateIndicator();
        if (online) {
            await this.syncDataFromServer();
            await this.syncPendingSales();
        }
    },

    updateIndicator() {
        const btn = document.getElementById('offline-sync-indicator');
        if (!btn) return;
        if (this.isOnline) {
            btn.innerHTML = '<i class="fas fa-wifi text-success"></i> <span style="font-size:0.75rem">En Línea</span>';
            btn.className = 'system-info-item bg-success-soft';
        } else {
            btn.innerHTML = '<i class="fas fa-wifi-slash text-danger"></i> <span style="font-size:0.75rem">Offline</span>';
            btn.className = 'system-info-item bg-danger-soft';
        }
    },

    async syncDataFromServer() {
        if (!this.isOnline || this.isSyncing) return;
        this.isSyncing = true;

        try {
            console.log("⬇️ Descargando catálogo...");

            // 1. NETWORK PHASE: Fetch all data first (outside transaction)
            const [prodRes, cliRes] = await Promise.all([
                fetch('../../modules/productos/api_get_all.php').catch(err => {
                    console.error('Error fetching productos:', err);
                    return null;
                }),
                fetch('../../modules/clientes/api_get_all.php').catch(err => {
                    console.error('Error fetching clientes:', err);
                    return null;
                })
            ]);

            let validProds = [];
            let validClients = [];

            if (prodRes && prodRes.ok) {
                try {
                    const rawProds = await prodRes.json();
                    if (Array.isArray(rawProds)) {
                        // Normalize data to ensure no missing keys causing DB errors
                        validProds = rawProds.filter(p => p && p.id).map(p => ({
                            id: parseInt(p.id),
                            codigoPrincipal: p.codigoPrincipal || '',
                            nombre: p.nombre || '',
                            precioVenta: parseFloat(p.precioVenta) || 0,
                            stock: parseInt(p.stock) || 0
                        }));
                    }
                } catch (err) {
                    console.error('Error parsing productos JSON:', err);
                }
            }

            if (cliRes && cliRes.ok) {
                try {
                    const rawClients = await cliRes.json();
                    console.log('📋 Raw clientes response:', rawClients);
                    if (Array.isArray(rawClients)) {
                        validClients = rawClients.filter(c => c && c.id).map(c => ({
                            id: parseInt(c.id),
                            cedula: c.cedula || '',
                            nombre: c.nombre || '',
                            direccion: c.direccion || '',
                            telefono: c.telefono || ''
                        }));
                    } else {
                        console.warn('⚠️ La respuesta de clientes no es un array:', typeof rawClients);
                    }
                } catch (err) {
                    console.error('Error parsing clientes JSON:', err);
                }
            } else {
                console.warn('⚠️ Respuesta de clientes:', cliRes ? `Status: ${cliRes.status}` : 'null');
            }

            console.log(`📦 Datos recibidos: ${validProds.length} productos, ${validClients.length} clientes.`);

            // 2. DB PHASE: Single Transaction for atomicity
            if (validProds.length > 0 || validClients.length > 0) {
                await db.transaction('rw', db.productos, db.clientes, async () => {
                    // Products
                    if (validProds.length > 0) {
                        await db.productos.clear();
                        await db.productos.bulkPut(validProds);
                    }
                    
                    // Clients
                    if (validClients.length > 0) {
                        await db.clientes.clear();
                        await db.clientes.bulkPut(validClients);
                    }
                });
                console.log("✅ Base de datos local actualizada correctamente.");
            }

        } catch (error) {
            console.error("❌ Error Sincronización:", error);
        } finally {
            this.isSyncing = false;
        }
    },

    async syncPendingSales() {
        if (!this.isOnline) return;
        // Simple logic for sync
        try {
            const pending = await db.ventas_pendientes.toArray();
            for (const sale of pending) {
                if (sale.estado === 'pendiente') {
                    const res = await fetch('../../modules/ventas/api_guardar_venta.php', {
                        method: 'POST', body: JSON.stringify(sale.data)
                    });
                    if (res.ok) await db.ventas_pendientes.delete(sale.id);
                }
            }
        } catch (e) { console.error("Sync Sales Error", e); }
    },

    async saveSale(saleData) {
        if (this.isOnline) {
            try {
                const res = await fetch('../../modules/ventas/api_guardar_venta.php', {
                    method: 'POST', body: JSON.stringify(saleData)
                });
                return await res.json();
            } catch (e) {
                return this.saveOffline(saleData);
            }
        }
        return this.saveOffline(saleData);
    },

    async saveOffline(saleData) {
        await db.ventas_pendientes.add({ fecha: new Date(), data: saleData, estado: 'pendiente' });
        return { success: true, offline: true, message: "Guardado Offline", numeroFactura: "OFF-" + Date.now() };
    },

    async searchProducts(query) {
        if (!query) return [];
        query = query.toLowerCase();
        return await db.productos
            .filter(p => p.nombre.toLowerCase().includes(query) || p.codigoPrincipal.toLowerCase().includes(query))
            .limit(20).toArray();
    },

    async searchClients(query) {
        if (!query) return [];
        query = query.toLowerCase();
        return await db.clientes.filter(c => c.nombre.toLowerCase().includes(query) || c.cedula.includes(query)).limit(10).toArray();
    }
};

document.addEventListener('DOMContentLoaded', () => OfflineManager.init());
