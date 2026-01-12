const CACHE_NAME = 'logipharm-v2'; // Actualizado para limpiar caché
const ASSETS = [
    './',
    './index.php',
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
    // If it's an API call, we usually don't want to cache it in SW (OfflineManager handles it)
    if (event.request.url.includes('/api/')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        }).catch(() => {
            // If both fail (offline and not in cache), return offline placeholder or something
        })
    );
});
