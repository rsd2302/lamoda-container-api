# start with the official Composer image and name it
FROM composer AS composer

FROM php:7.3.7-fpm-alpine3.10

RUN apk add --no-cache --update --virtual buildDeps autoconf
RUN apk --update add gcc make g++ zlib-dev
RUN pecl install mongodb \
	&& docker-php-ext-enable mongodb

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY src /app

RUN chmod 777 -R /app/storage
RUN rm -f /app/.env
RUN mv /app/.production.env /app/.env

RUN cd /app && composer install

EXPOSE 9000

CMD ["php-fpm"]