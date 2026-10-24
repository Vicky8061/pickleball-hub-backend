#!/usr/bin/env bash
set -e

echo "==> Running production startup tasks..."

# Ensure custom Nginx configuration with Laravel routing is active
if [ -f "/var/www/html/conf/nginx/nginx-site.conf" ]; then
    echo "==> Applying Laravel Nginx configuration..."
    cp /var/www/html/conf/nginx/nginx-site.conf /etc/nginx/sites-available/default.conf
    cp /var/www/html/conf/nginx/nginx-site.conf /etc/nginx/sites-enabled/default.conf
fi

# Install Composer dependencies if not already cached
if [ ! -d "/var/www/html/vendor" ]; then
    echo "==> Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Ensure storage directory structure and permissions
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} /var/www/html/storage/logs
chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink if missing
echo "==> Linking storage..."
php artisan storage:link || true

# Cache Laravel configurations
echo "==> Caching config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "==> Running database migrations..."
php artisan migrate --force || true

echo "==> Production initialization complete."
