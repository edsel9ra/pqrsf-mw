import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin.css', 'resources/js/admin.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                bunny('JetBrains Mono', {
                    weights: [400, 500],
                }),
                bunny('DM Serif Display', {
                    weights: [400],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        // ✅ HMR desde tu navegador
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            usePolling: true,
            interval: 250,
        },
    },
});
