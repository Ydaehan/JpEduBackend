FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

COPY . /var/www/html/

## nginx(www-data)로 소유자 변경
RUN chown -R www-data:www-data /var/www/html/storage

## update package
RUN apk update

## install curl
RUN apk add curl

RUN apk add nodejs npm

## install pdo postgresql
RUN apk add postgresql-dev \
		&& docker-php-ext-install pdo_pgsql
	
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

## install zip
RUN apk add --no-cache libzip-dev \
	&& docker-php-ext-install zip

## install composer
RUN curl -sS https://getcomposer.org/installer | php

## move file to /usr/bin/composer
RUN my composer.phar /usr/bin/composer

## install packages
RUN composer install --optimize-autoloader --no-dev

RUN npm install

## use 9000 port
EXPOSE 9000

RUN chown www-data:www-data ./bootstrap

RUN npm run build

RUN php artisan route:cache

RUN php artisan view:cache

RUN php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

RUN php artisan l5-swagger:generate

## run php-fpm
CMD ["php-fpm"]