import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // alias: 'sans' -> alimente directement l'utilitaire Tailwind v4 `font-sans`
                bunny('Instrument Sans', {
                    alias: 'sans',
                    weights: [400, 500, 600],
                }),
                // alias: 'display' -> génère `.font-display` (titres, identité NexaSpin)
                bunny('Space Grotesk', {
                    alias: 'display',
                    weights: [500, 600, 700],
                }),
                // alias: 'mono' -> alimente directement l'utilitaire Tailwind v4 `font-mono`
                // (cotes, participants, badges — l'identité "précision" du site)
                bunny('IBM Plex Mono', {
                    alias: 'mono',
                    weights: [500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
