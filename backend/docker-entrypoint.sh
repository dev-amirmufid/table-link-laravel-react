#!/bin/sh
set -e

echo "========================================="
echo "  TableLink Laravel Setup Script"
echo "========================================="

# Change to app directory
cd /var/www/backend

# 1. Copy .env.example to .env if it doesn't exist
if [ ! -f .env ]; then
    echo "[1/6] Copying .env.example to .env..."
    cp .env.example .env
else
    echo "[1/6] .env already exists, skipping..."
fi

# 2. Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "[2/6] Generating application key..."
    php artisan key:generate --force
else
    echo "[2/6] Application key already exists, skipping..."
fi

# 3. Run composer install
echo "[3/6] Running composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 4. Generate Swagger API documentation
echo "[4/6] Generating Swagger API documentation..."
php artisan l5-swagger:generate

# 5. Run migrations (always run to handle new migrations)
echo "[5/6] Running database migrations..."
php artisan migrate --force

# 6. Clear and cache configurations
echo "[6/6] Optimizing Laravel application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data .

echo "========================================="
echo "  Setup completed successfully!"
echo "========================================="

# Execute the original command (supervisord)
exec "$@"
