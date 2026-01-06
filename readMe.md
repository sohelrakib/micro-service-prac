docker network create laravel-net
docker network ls


CUSTOMER:
===================

cd customer

Run product setup:
------------------
docker compose build setup
docker compose run --rm setup

Start product service:
----------------------
docker compose up -d --build app
docker exec -it customer php artisan key:generate

api.php file not loaded, need to run below command:

````````````
bootstrap/app.php:

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

````````````
php artisan route:clear
php artisan cache:clear


PRODUCT:
===========

cd product
docker compose build setup
docker compose run --rm setup

docker compose up -d --build app

docker exec -it product php artisan key:generate


Cause: leading blank line before <?php in api.php caused "headers already sent", and the response was being treated as HTML.


``````````````````````
    command: >
      sh -c "
      if [ ! -f artisan ]; then
        echo 'Installing Laravel 12...';
        curl -sS https://getcomposer.org/installer | php &&
        mv composer.phar /usr/local/bin/composer &&
        composer create-project laravel/laravel:^12.0 .;
      else
        echo 'Laravel already exists, skipping install.';
      fi
      "

      this code should be in Dockerfile, not docker-compose.yml
      
``````````````````````