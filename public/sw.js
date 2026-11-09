
const CACHE_NAME = 'sensor-monitor-v1';

const APP_SHELL = [
    '/offline',
    '/manifest.json',
    '/icons/icon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle same-origin GET requests.
    if (
        request.method !== 'GET' ||
        url.origin !== self.location.origin
    ) {
        return;
    }

    // Always retrieve current sensor readings from Laravel.
    if (
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/readings/')
    ) {
        event.respondWith(
            fetch(request).catch(() =>
                new Response(
                    JSON.stringify({
                        data: null,
                        offline: true,
                        message: 'Sensor readings require a connection.'
                    }),
                    {
                        status: 503,
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    }
                )
            )
        );
        return;
    }

    // Network-first navigation avoids showing an outdated dashboard.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async () => {
                const cached = await caches.match('/offline');
                return cached || new Response(
                    'You are offline. Reconnect to view the dashboard.',
                    {
                        status: 503,
                        headers: {
                            'Content-Type': 'text/plain'
                        }
                    }
                );
            })
        );
        return;
    }

    // Cache-first for the app's local shell resources.
    if (
        url.pathname === '/manifest.json' ||
        url.pathname.startsWith('/icons/')
    ) {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;

                return fetch(request).then(response => {
                    if (response.ok) {
                        const copy = response.clone();

                        caches.open(CACHE_NAME).then(cache =>
                            cache.put(request, copy)
                        );
                    }

                    return response;
                });
            })
        );
    }
});