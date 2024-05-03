FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

COPY . /var/www/html/

## nginx(www-data)로 소유자 변경
RUN chown -R www-data:www-data /var/www/html/storage

## update package
RUN apk update

## install curl
RUN apk add --no-cache curl

## install zip
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-install zip

## install gd
RUN apk add --no-cache \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

## install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

## install nodejs and npm
RUN apk add --no-cache nodejs npm

## install npm packages
RUN npm install

## use 9000 port
EXPOSE 9000

## change owner of the bootstrap directory
RUN chown www-data:www-data ./bootstrap

## build npm
RUN npm run build

## cache routes and views
RUN php artisan route:cache && php artisan view:cache

## publish vendor files
RUN php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
RUN php artisan vendor:publish --provider="Vyuldashev\LaravelOpenApi\OpenApiServiceProvider" --tag="openapi-config"

## generate l5-swagger
RUN php artisan openapi:generate

## run php-fpm
CMD ["php-fpm"]
