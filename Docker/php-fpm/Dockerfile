FROM php:8.1-fpm

RUN apt update && apt install -y git zip

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

CMD ["php-fpm"]
