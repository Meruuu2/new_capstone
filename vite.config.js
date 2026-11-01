import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        // Disable HMR in production or when behind reverse proxy
        hmr: process.env.NODE_ENV === 'production' ? false : {
            protocol: 'ws',
            host: 'localhost',
            port: 5173,
        },
    },
    build: {
        // Optimize build output
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
        // Increase chunk size warning
        chunkSizeWarningLimit: 1000,
    },
});
