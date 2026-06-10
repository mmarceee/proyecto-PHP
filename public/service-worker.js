const CACHE_NAME = 'gendarapp-v1';

const APP_SHELL = [
  '/',
  '/manifest.webmanifest',
  '/icons/icon-192.png',
  '/icons/icon-512.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL))
  );

  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) =>
      Promise.all(
        cacheNames
          .filter((cacheName) => cacheName !== CACHE_NAME)
          .map((cacheName) => caches.delete(cacheName))
      )
    )
  );

  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') { //Esto es importante porque acciones como reservar, cancelar usan POST, PUT, PATCH. No queremos cachear ni simular esas acciones offline.
    return;
  }

  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});

/*

install: se ejecuta cuando el navegador instala el service worker. Guarda en cache algunos archivos básicos.

activate: limpia caches viejas. Por ejemplo, cuando cambiemos versiones (de gendarapp-v1 a gendarapp-v2).

fetch: intercepta pedidos GET. Primero intenta traerlos de internet; si falla, busca una copia en cache.


*/