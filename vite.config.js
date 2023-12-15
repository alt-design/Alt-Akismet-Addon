import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/alt-akismet-addon.js',
                'resources/css/alt-akismet-addon.css'
            ],
            publicDirectory: 'resources/dist',
        }),
        vue(),
    ],
});
