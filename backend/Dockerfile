FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm \
    fcgi \
    supervisor

# Create supervisor log directory
RUN mkdir -p /var/log/supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/backend

# Copy all application files
COPY . .

# Install dependencies during build (ignore scripts first to avoid artisan error)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
    && composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set proper permissions
RUN chmod -R 755 storage bootstrap/cache \
    && chown -R www-data:www-data .

# Expose port 9000 (PHP-FPM)
EXPOSE 9000

# Start entrypoint script which will handle setup then start supervisor
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
