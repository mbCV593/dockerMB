FROM php:8.4-fpm-alpine
ENV TZ=America/Guatemala
RUN apk add --no-cache icu-dev oniguruma-dev libzip-dev libjpeg-turbo-dev libpng-dev freetype-dev git curl unzip bash tzdata
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) pdo pdo_mysql intl zip gd opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
RUN { echo "opcache.enable=1"; echo "opcache.enable_cli=1"; echo "opcache.validate_timestamps=1"; echo "opcache.revalidate_freq=0"; } > /usr/local/etc/php/conf.d/opcache.ini
