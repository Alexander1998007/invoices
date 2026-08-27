#!/bin/sh
set -eu

cd /var/www

if [ ! -f artisan ]; then
  echo "Bootstrapping Laravel 12..."
  rm -rf /tmp/laravel
  composer create-project laravel/laravel:^12.0 /tmp/laravel --no-interaction --prefer-dist
  cp -a /tmp/laravel/. /var/www/
  rm -rf /tmp/laravel
fi

if [ ! -f .env ]; then
  cp .env.example .env
fi

# Configure Laravel explicitly for Docker/MySQL. The generated Laravel .env
# defaults to SQLite, so relying on the generated file makes the container
# unexpectedly boot against SQLite on first start.
set_env() {
  key="$1"
  value="$2"
  if grep -q "^${key}=" .env; then
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    printf "\n%s=%s\n" "$key" "$value" >> .env
  fi
}

set_env APP_ENV local
set_env APP_DEBUG true
set_env APP_URL http://localhost:8000
set_env DB_CONNECTION mysql
set_env DB_HOST db
set_env DB_PORT 3306
set_env DB_DATABASE invoices
set_env DB_USERNAME invoice
set_env DB_PASSWORD invoice
set_env SESSION_DRIVER file
set_env CACHE_STORE file

php artisan key:generate --force
php artisan config:clear

echo "Waiting for database..."
until php artisan migrate --force >/dev/null 2>&1; do
  sleep 2
done

php artisan serve --host=0.0.0.0 --port=8000
