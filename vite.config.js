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
                'resources/css/admin.css',
                'resources/js/owner/dashboard.js',
                'resources/js/owner/courts.js',
                'resources/js/owner/time_slots.js',
                'resources/js/owner/bookings.js',
                'resources/js/owner/tournaments.js',
                'resources/js/owner/earnings.js',
                'resources/js/owner/reviews.js',
                'resources/js/owner/profile.js',
                'resources/js/user/become_owner.js',
                'resources/js/admin/dashboard.js',
                'resources/js/admin/owner_applications.js',
                'resources/js/admin/courts.js',
                'resources/js/admin/users.js',
                'resources/js/admin/banners.js',
                'resources/js/admin/bookings.js',
                'resources/js/admin/payouts.js',
                'resources/js/admin/reviews.js',
                'resources/js/admin/tournaments.js',
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
