import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Minify with esbuild (faster than terser, good compression)
        minify: 'esbuild',
        // Warn when chunks exceed 500KB
        chunkSizeWarningLimit: 500,
        rollupOptions: {
            output: {
                // Split vendor code into separate cacheable chunk
                manualChunks(id) {
                    if (id.includes('node_modules/alpinejs')) return 'alpine';
                    if (id.includes('node_modules/axios')) return 'axios';
                },
                // Hashed filenames for long-term caching
                entryFileNames:   'assets/[name]-[hash].js',
                chunkFileNames:   'assets/[name]-[hash].js',
                assetFileNames:   'assets/[name]-[hash].[ext]',
            },
        },
        // Enable CSS code splitting
        cssCodeSplit: true,
    },
});
