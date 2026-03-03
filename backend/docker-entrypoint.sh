set -e

echo "Iniciando entrypoint..."

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
  echo "Gerando APP_KEY..."
  php artisan key:generate --ansi --force || true
fi

echo "Cacheando config..."
php artisan config:cache || true

echo "Cacheando routes..."
php artisan route:cache || true

echo "Cacheando views..."
php artisan view:cache || true

echo "Rodando migrações..."
php artisan migrate --force --no-interaction || true

echo "Entry point concluído. Iniciando comando principal: $@"

exec "$@"