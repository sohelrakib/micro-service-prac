FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    curl zip unzip git \
    && docker-php-ext-install pdo

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
