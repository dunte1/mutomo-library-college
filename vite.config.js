import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            outDir: 'public',
            registerSW: false,
            injectRegister: false,
            manifest: false,
            workbox: {
                globDirectory: 'public',
                globPatterns: ['build/assets/**/*.{js,css,woff,woff2}', 'offline.html'],
                globIgnores: [],
                navigateFallback: '/offline.html',
                navigateFallbackDenylist: [/^\/api\//, /^\/storage\//, /^\/build\//, /^\/icons\//],
                runtimeCaching: [
                    {
                        urlPattern: /^\/api\//,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: { maxEntries: 100, maxAgeSeconds: 3600 },
                            networkTimeoutSeconds: 10,
                        },
                    },
                    {
                        urlPattern: /\.(?:png|jpg|jpeg|gif|svg|ico)$/,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'image-cache',
                            expiration: { maxEntries: 50, maxAgeSeconds: 604800 },
                        },
                    },
                ],
            },
        }),
    ],
    server: {
        host: '127.0.0.1',
    },
    build: {
        chunkSizeWarningLimit: 500,
        rollupOptions: {
            output: {
                manualChunks: function(id) {
                    if (id.includes('node_modules/alpinejs') || id.includes('node_modules/livewire-sortable')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
});
