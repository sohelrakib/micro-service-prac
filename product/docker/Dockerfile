FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    curl zip unzip \
    && docker-php-ext-install pdo \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
