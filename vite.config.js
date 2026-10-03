import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/front/css/app.css', 'resources/front/js/app.js'],
            buildDirectory: 'build/front',
            hotFile: 'storage/framework/vite-front.hot',
            refresh: ['resources/views/front/**/*.blade.php', 'resources/views/components/front/**/*.blade.php'],
        }),
        tailwindcss(),
    ],
    server: { port: 5173, strictPort: true },
});
