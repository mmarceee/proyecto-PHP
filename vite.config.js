import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/solicitudes-profesionales.js',
                    'resources/js/postularse-profesional.js',
                    'resources/js/agenda-profesional.js',
                    'resources/js/profile.js'],
            refresh: true,
        }),
    ],
});