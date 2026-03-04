#!/bin/sh
set -e

echo "Iniciando entrypoint..."

if [ ! -d "vendor" ]; then
    echo "📦 Instalando dependências..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
  echo "Gerando APP_KEY..."
  php artisan key:generate --ansi --force || true
fi

echo "📚 Gerando documentação Swagger..."
php artisan l5-swagger:generate || echo "⚠️  Falha ao gerar Swagger, mas continuando..."


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