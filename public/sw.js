const CACHE_NAME = 'batistack-v1-0-0';
const ASSETS_TO_CACHE = [
    '/',
    '/css/filament/filament/app.css',
    '/js/filament/filament/app.js',
    '/manifest.json',
    '/favicon.svg',
    '/apple-touch-icon.png'
];

/**
 * Installation du Service Worker : Mise en cache des ressources critiques
 */
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

/**
 * Activation : Nettoyage des anciens caches
 */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

/**
 * Stratégie : Network First (Priorité Réseau) avec Fallback Cache
 * Idéal pour une application de gestion de paye où la donnée doit être fraîche.
 */
self.addEventListener('fetch', (event) => {
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Si on a du réseau, on met à jour le cache
                if (event.request.method === 'GET' && response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // En cas de perte de réseau, on pioche dans le cache
                return caches.match(event.request);
            })
    );
});
