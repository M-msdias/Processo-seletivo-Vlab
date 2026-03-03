
set -e

if [ -z "$APP_KEY" ]; then
  php artisan key:generate --ansi
fi

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

php artisan migrate --force || true

exec "$@"