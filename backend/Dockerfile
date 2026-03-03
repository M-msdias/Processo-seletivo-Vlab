# Use --platform para garantir compatibilidade com Render (amd64)
FROM --platform=linux/amd64 php:8.4-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    libpng libpng-dev \
    libzip-dev \
    zip unzip \
    oniguruma-dev \
    icu-dev \
    curl \
    git \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo pdo_mysql pdo_pgsql pgsql \
        mbstring zip exif pcntl bcmath gd intl \
    && apk del --no-network .build-deps 2>/dev/null || true \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer config --global process-timeout 600 \
    && composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist \
    || (composer update --lock --no-interaction && composer install --no-dev --optimize-autoloader --no-scripts --no-interaction)

COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9000"]