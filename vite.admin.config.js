import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/admin/css/app.css', 'resources/admin/js/app.js'],
            buildDirectory: 'build/admin',
            hotFile: 'storage/framework/vite-admin.hot',
            refresh: ['resources/views/admin/**/*.blade.php', 'resources/views/components/**/*.blade.php'],
        }),
        tailwindcss(),
    ],
    server: { port: 5174, strictPort: true },
});
