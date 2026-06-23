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
            strategies: 'injectManifest',
            srcDir: 'resources/js',
            filename: 'sw.js',
            workbox: {
                globDirectory: 'public',
                globPatterns: ['build/assets/**/*.{js,css,woff,woff2}', 'offline.html'],
                globIgnores: [],
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
