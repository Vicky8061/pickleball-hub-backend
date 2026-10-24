# ==========================================
# Stage 1: Build Frontend Assets with Node
# ==========================================
FROM node:20-alpine AS node_builder

WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ==========================================
# Stage 2: Production PHP + Nginx Environment
# ==========================================
FROM richarvey/nginx-php-fpm:3.1.6

WORKDIR /var/www/html

# Copy application source
COPY . .

# Copy compiled frontend assets from Node build stage
COPY --from=node_builder /app/public/build ./public/build

# Copy custom Nginx configuration to enable Laravel routing
COPY conf/nginx/nginx-site.conf /etc/nginx/sites-available/default.conf
COPY conf/nginx/nginx-site.conf /etc/nginx/sites-enabled/default.conf

# Ensure deployment script is executable
RUN chmod +x ./scripts/00-laravel-deploy.sh

# Environment settings for richarvey/nginx-php-fpm
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV COMPOSER_ALLOW_SUPERUSER=1

# Expose default HTTP port
EXPOSE 80

CMD ["/start.sh"]
