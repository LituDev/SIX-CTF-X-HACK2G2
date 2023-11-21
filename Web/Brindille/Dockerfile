FROM composer/composer:2-bin AS composer_upstream

FROM php:8.1-apache
ARG FLAG=IUT{SST1_C3S_F4C1L3_0u_P4s}

RUN apt-get update -qq && \
    apt-get install -qy \
    git \
    gnupg \
    unzip \
    zip
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/*
RUN a2enmod rewrite

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer_upstream --link /composer /usr/bin/composer
ADD config/apache/vhost.conf /etc/apache2/sites-enabled/000-default.conf
ADD config/apache/apache.conf /etc/apache2/conf-enabled/z-app.conf

ADD templates/ /app/templates/
ADD index.php /app/index.php
ADD composer.json /app/composer.json
ADD composer.lock /app/composer.lock
ADD .htaccess /app/.htaccess
RUN echo $FLAG > /flag.txt

WORKDIR /app

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /app