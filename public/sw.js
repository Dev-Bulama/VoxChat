const CACHE_NAME = 'voxchat-v1';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    'https://cdn.tailwindcss.com',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
];

// Install
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS.filter(url => !url.startsWith('http')));
        })
    );
    self.skipWaiting();
});

// Activate
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/api/') || event.request.url.includes('/broadcasting/')) return;

    event.respondWith(
        caches.match(event.request).then((cached) => {
            const networkFetch = fetch(event.request).then((response) => {
                if (response.ok && event.request.url.includes('/icons/')) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                }
                return response;
            }).catch(() => cached);

            return cached || networkFetch;
        })
    );
});

// Push notifications
self.addEventListener('push', (event) => {
    if (!event.data) return;
    const data = event.data.json();

    event.waitUntil(
        self.registration.showNotification(data.title || 'VoxChat', {
            body:    data.body || 'New notification',
            icon:    '/icons/icon-192.png',
            badge:   '/icons/icon-72.png',
            data:    { url: data.url || '/chats' },
            vibrate: [200, 100, 200],
            tag:     data.tag || 'voxchat',
            actions: data.actions || [],
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/chats';
    event.waitUntil(clients.openWindow(url));
});
