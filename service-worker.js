/* ===========================================================
   service-worker.js — Control del modo offline y actualización
   =========================================================== */

const CACHE_NAME = 'sastreria-cache-v2';  // 🔁 cambia el número si actualizás el contenido
const OFFLINE_URL = '/sastreria_app/offline.html';

/* Archivos que se almacenarán para funcionar sin conexión */
const urlsToCache = [
  '/sastreria_app/',
  '/sastreria_app/index.php',
  '/sastreria_app/registro.php',
  '/sastreria_app/login.php',
  '/sastreria_app/dashboard.php',
  '/sastreria_app/assets/css/style.css',
  '/sastreria_app/assets/js/main.js',
  '/sastreria_app/assets/img/qr-sastreria.png',
  '/sastreria_app/assets/img/icon-192.png',
  '/sastreria_app/assets/img/icon-512.png',
  OFFLINE_URL
];

/* ========================
   INSTALACIÓN DEL SERVICE WORKER
   ======================== */
self.addEventListener('install', event => {
  console.log('🛠 Instalando Service Worker...');
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('📦 Archivos cacheados correctamente');
      return cache.addAll(urlsToCache);
    })
  );
  self.skipWaiting();
});

/* ========================
   ACTIVACIÓN Y LIMPIEZA DEL CACHE ANTIGUO
   ======================== */
self.addEventListener('activate', event => {
  console.log('⚙️ Activando nuevo Service Worker...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(name => {
          if (name !== CACHE_NAME) {
            console.log('🧹 Eliminando cache viejo:', name);
            return caches.delete(name);
          }
        })
      );
    })
  );
  self.clients.claim();
});

/* ========================
   INTERCEPTAR PETICIONES (MODO OFFLINE)
   ======================== */
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return; // sólo manejamos peticiones GET

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Si hay conexión, actualiza el caché
        const clone = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        return response;
      })
      .catch(() => {
        // Si no hay conexión, intenta servir desde el cache
        return caches.match(event.request)
          .then(response => {
            if (response) {
              return response;
            } else {
              // Si no está cacheado, mostrar la página offline
              return caches.match(OFFLINE_URL);
            }
          });
      })
  );
});

/* ========================
   SINCRONIZACIÓN EN SEGUNDO PLANO (para futuro)
   ======================== */
// Podés usar este bloque más adelante para enviar datos pendientes
// cuando el usuario vuelve a tener conexión.
self.addEventListener('sync', event => {
  if (event.tag === 'sync-datos-pendientes') {
    console.log('🔄 Sincronizando datos pendientes...');
    // Aquí podrías programar la sincronización con el servidor remoto
  }
});
