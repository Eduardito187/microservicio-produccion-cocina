#!/usr/bin/env bash
set -e

echo "[entrypoint] starting..."

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rw storage bootstrap/cache || true

if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
    echo "[entrypoint] .env created from .env.example"
  fi
fi

# Ensure DB host/port in .env (optional)
if [ -n "${DB_HOST:-}" ]; then
  grep -q "^DB_HOST=" .env && sed -i "s/^DB_HOST=.*/DB_HOST=${DB_HOST}/" .env || echo "DB_HOST=${DB_HOST}" >> .env
fi
if [ -n "${DB_PORT:-}" ]; then
  grep -q "^DB_PORT=" .env && sed -i "s/^DB_PORT=.*/DB_PORT=${DB_PORT}/" .env || echo "DB_PORT=${DB_PORT}" >> .env
fi

# Composer only if vendor missing
if [ ! -d vendor ]; then
  echo "[entrypoint] running composer install..."
  composer install --no-interaction --prefer-dist
fi

# Generate key if missing
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate || true
fi

php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear  >/dev/null 2>&1 || true
php artisan cache:clear  >/dev/null 2>&1 || true

# ✅ Start Apache NOW (do not block on migrations/seeders)
exec apache2-foreground