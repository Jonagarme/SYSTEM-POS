const CACHE_NAME = 'logipharm-v3'; // Incrementado para forzar actualización cada vez que hay cambios dinámicos
// Quitamos './' e './index.php' de aquí para que no se sirvan SIEMPRE desde caché primero.
const ASSETS = [
    './assets/css/style.css',
    './assets/js/offline-manager.js',
    'https://cdn.jsdelivr.net/npm/dexie@3.2.4/dist/dexie.mjs',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'
];

// Install Event
self.addEventListener('install', event => {
    self.skipWaiting(); // Activa el nuevo SW inmediatamente
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('Caching essential assets...');
            return cache.addAll(ASSETS);
        })
    );
});

// Activate Event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        }).then(() => self.clients.claim()) // Toma control de todas las páginas inmediatamente
    );
});

// Fetch Event
self.addEventListener('fetch', event => {
    // 1. Ignorar llamadas a API (dejar que el navegador o OfflineManager las manejen)
    if (event.request.url.includes('/api/')) {
        return;
    }

    // 2. Estrategia Network-First para Navegación (HTML/PHP)
    // Esto asegura que si hay internet, el dashboard (index.php) sea SIEMPRE el más reciente.
    // Si no hay internet, cae a la caché.
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // Opcional: Guardar una copia fresca en caché para modo offline
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // 3. Estrategia Cache-First para Assets (CSS, JS, Imágenes)
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});
