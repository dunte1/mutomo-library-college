import { precacheAndRoute, cleanupOutdatedCaches } from 'workbox-precaching'
import { registerRoute } from 'workbox-routing'
import { NetworkFirst } from 'workbox-strategies'
import { CacheFirst } from 'workbox-strategies'
import { ExpirationPlugin } from 'workbox-expiration'

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting()
    }
})

precacheAndRoute(self.__WB_MANIFEST || [])
cleanupOutdatedCaches()

registerRoute(
    /^\/api\//,
    new NetworkFirst({
        cacheName: 'api-cache',
        networkTimeoutSeconds: 10,
        plugins: [
            new ExpirationPlugin({ maxEntries: 100, maxAgeSeconds: 3600 }),
        ],
    })
)

registerRoute(
    /\.(?:png|jpg|jpeg|gif|svg|ico)$/,
    new CacheFirst({
        cacheName: 'image-cache',
        plugins: [
            new ExpirationPlugin({ maxEntries: 50, maxAgeSeconds: 604800 }),
        ],
    })
)
