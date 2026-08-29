import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/api.js',
                'resources/js/user/user-layout.js',
                'resources/js/user/bookings.js',
                'resources/css/bookings.css',
                'resources/js/user/wishlist.js',
                'resources/css/wishlist.css',
                'resources/css/owner.css',
                'resources/js/owner/dashboard.js',
                'resources/js/owner/courts.js',
                'resources/js/owner/time_slots.js',
                'resources/js/owner/bookings.js',
                'resources/js/owner/tournaments.js',
                'resources/js/owner/earnings.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
