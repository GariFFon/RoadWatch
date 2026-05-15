#!/bin/bash
set -e

echo "=== RoadWatch Startup ==="

# Validate critical env vars
if [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    echo "ERROR: MySQL environment variables are not set!"
    echo "  DB_HOST     = '${DB_HOST}'"
    echo "  DB_DATABASE = '${DB_DATABASE}'"
    echo "  DB_USERNAME = '${DB_USERNAME}'"
    echo ""
    echo "Please set DB_CONNECTION=mysql, DB_HOST, DB_PORT, DB_DATABASE,"
    echo "DB_USERNAME, and DB_PASSWORD in Railway environment variables."
    exit 1
fi

echo "[1/5] Caching config..."
php artisan config:cache

echo "[2/5] Caching routes..."
php artisan route:cache

echo "[3/5] Linking storage..."
php artisan storage:link || true

echo "[4/5] Running migrations..."
php artisan migrate --force

echo "[5/5] Seeding categories..."
php artisan db:seed --class=CategorySeeder --force

echo "=== Starting server on port ${PORT:-8080} ==="
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
