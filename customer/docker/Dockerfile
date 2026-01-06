FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    curl zip unzip git \
    && docker-php-ext-install pdo

WORKDIR /var/www/html
