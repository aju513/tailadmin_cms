import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/front/css/app.css', 'resources/front/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
