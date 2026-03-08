/**
 * MIKHMON Agent Service Worker
 * Handles caching for Agent Portal PWA
 */
const CACHE_NAME = 'mikhmon-agent-v1';
const ASSETS_TO_CACHE = [
    '../css/mikhmon-ui.dark.min.css',
    '../css/mikhmon-ui.light.min.css',
    '../css/mikhmon-ui.blue.min.css',
    '../css/mikhmon-ui.green.min.css',
    '../css/mikhmon-ui.pink.min.css',
    '../css/font-awesome/css/font-awesome.min.css',
    '../js/jquery.min.js',
    '../img/icon-192.png',
    '../img/icon-512.png',
    '../img/favicon.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS_TO_CACHE))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    const url = new URL(event.request.url);
    if (url.pathname.endsWith('.php') || url.search.length > 0) return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});
