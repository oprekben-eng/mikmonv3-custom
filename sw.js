/**
 * MIKHMON Service Worker
 * Handles caching for PWA offline support (basic shell caching)
 */
const CACHE_NAME = 'mikhmon-v1';
const ASSETS_TO_CACHE = [
  './css/mikhmon-ui.dark.min.css',
  './css/mikhmon-ui.light.min.css',
  './css/mikhmon-ui.blue.min.css',
  './css/mikhmon-ui.green.min.css',
  './css/mikhmon-ui.pink.min.css',
  './css/font-awesome/css/font-awesome.min.css',
  './js/jquery.min.js',
  './js/pace.min.js',
  './img/icon-192.png',
  './img/icon-512.png',
  './img/favicon.png',
];

// Install: cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

// Fetch: network-first strategy (always try network, fallback to cache)
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') return;

  // Skip API calls and PHP pages (always need fresh data from MikroTik)
  const url = new URL(event.request.url);
  if (url.pathname.endsWith('.php') || url.search.length > 0) return;

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Cache successful responses for static assets
        if (response.ok) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // Fallback to cache if network fails
        return caches.match(event.request);
      })
  );
});
