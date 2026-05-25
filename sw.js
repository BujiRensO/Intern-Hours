const CACHE_NAME = "ojt-tracker-cache-v1";
const ASSETS = ["index.php", "manifest.json", "assets/images/logo.svg"];

// Install Service Worker and cache essential assets
self.addEventListener("install", (e) => {
  e.waitUntil(
    caches
      .open(CACHE_NAME)
      .then((cache) => {
        return cache.addAll(ASSETS);
      })
      .then(() => self.skipWaiting()),
  );
});

// Activate Service Worker and clear old caches
self.addEventListener("activate", (e) => {
  e.waitUntil(
    caches
      .keys()
      .then((keys) => {
        return Promise.all(
          keys.map((key) => {
            if (key !== CACHE_NAME) {
              return caches.delete(key);
            }
          }),
        );
      })
      .then(() => self.clients.claim()),
  );
});

// Fetch interception to serve cached shells instantly while prioritizing live updates
self.addEventListener("fetch", (e) => {
  // Skip POST requests (clock-ins, updates) and API calls
  if (e.request.method !== "GET" || e.request.url.includes("/api/")) {
    return;
  }

  e.respondWith(
    caches.match(e.request).then((cachedResponse) => {
      if (cachedResponse) {
        // Fetch in background to update cache for next load
        fetch(e.request)
          .then((networkResponse) => {
            if (networkResponse.status === 200) {
              caches.open(CACHE_NAME).then((cache) => {
                cache.put(e.request, networkResponse.clone());
              });
            }
          })
          .catch(() => {});
        return cachedResponse;
      }
      // Not in cache — fetch from network, gracefully handle offline/missing
      return fetch(e.request).catch(() => {
        // Return empty 503 response rather than an unhandled rejection
        return new Response('', { status: 503, statusText: 'Service Unavailable' });
      });
    }),
  );
});
