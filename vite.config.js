import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import statamic from '@statamic/cms/vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/alt-akismet-addon.js',
                'resources/css/alt-akismet-addon.css'
            ],
            publicDirectory: 'resources/dist',
        }),
        statamic(),
    ],
});
