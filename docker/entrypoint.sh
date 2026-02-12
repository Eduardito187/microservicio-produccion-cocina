#!/bin/sh
set -e

echo "[entrypoint] starting..."

# Permissions (don’t fail container if host volume permissions are weird)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rw storage bootstrap/cache 2>/dev/null || true

# Create .env if missing
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
    echo "[entrypoint] .env created from .env.example"
  else
    echo "[entrypoint] WARN: .env missing and .env.example not found"
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
  composer install --no-interaction --prefer-dist || echo "[entrypoint] WARN: composer install failed (continuing)"
fi

# Generate key if missing
if [ -f .env ]; then
  if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "[entrypoint] generating APP_KEY..."
    php artisan key:generate || echo "[entrypoint] WARN: key:generate failed (continuing)"
  fi
fi

run_artisan () {
  echo "[entrypoint] php artisan $*"
  php artisan "$@" || echo "[entrypoint] WARN: artisan $* failed (continuing)"
}

# Clear caches (safe)
run_artisan config:clear
run_artisan route:clear
run_artisan cache:clear

# DB tasks (safe, may fail if DB not ready yet)
run_artisan migrate --force --no-interaction
if [ "${RUN_PACT_ON_START:-0}" = "1" ]; then
  run_artisan db:seed --no-interaction
fi

# Unit tests
if [ "${RUN_UNIT_TESTS_ON_START:-0}" = "1" ]; then
  echo "[entrypoint] php artisan test --testsuite=Unit ..."
  php artisan test --testsuite=Unit || echo "[entrypoint] WARN: unit tests failed (continuing)"
fi

# Start Apache in background
apache2-foreground &
APACHE_PID=$!

# Wait until app is responding locally
echo "[entrypoint] waiting for app to be ready..."
until curl -fsS http://127.0.0.1/ >/dev/null; do
  sleep 2
done
echo "[entrypoint] app is ready."

# Run Pact after app is up (optional)
if [ "${RUN_PACT_ON_START:-0}" = "1" ]; then
  export PROVIDER_VERSION="${PROVIDER_VERSION:-dev-local}"
  export PROVIDER_BASE_URL="${PROVIDER_BASE_URL:-http://laravel_app}"
  export PACT_DO_NOT_TRACK="${PACT_DO_NOT_TRACK:-true}"

  echo "[entrypoint] running pact:all ..."
  npm run pact:all || echo "[entrypoint] WARN: pact:all failed (continuing)"
fi

# Keep container alive with Apache in foreground
wait "$APACHE_PID"