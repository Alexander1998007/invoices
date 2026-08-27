#!/bin/sh

set -eu

cd /var/www

echo "Checking Laravel dependencies..."

if [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer install \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader
fi

echo "Checking environment..."

if [ ! -f .env ]; then
    cp .env.example .env
fi

set_env() {
    key="$1"
    value="$2"

    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        printf '\n%s=%s\n' "$key" "$value" >> .env
    fi
}

set_env APP_ENV "local"
set_env APP_DEBUG "true"
set_env APP_URL "http://localhost:8000"

set_env DB_CONNECTION "mysql"
set_env DB_HOST "db"
set_env DB_PORT "3306"
set_env DB_DATABASE "invoices"
set_env DB_USERNAME "invoice"
set_env DB_PASSWORD "invoice"

set_env SESSION_DRIVER "file"
set_env CACHE_STORE "file"

echo "Generating application key..."

php artisan key:generate --force

echo "Clearing Laravel configuration..."

php artisan config:clear

echo "Running database migrations..."

until php artisan migrate --force >/dev/null 2>&1; do
    echo "Database is not ready yet..."
    sleep 2
done

echo "Database is ready."

echo "Starting Laravel..."

exec php artisan serve \
    --host=0.0.0.0 \
    --port=8000
