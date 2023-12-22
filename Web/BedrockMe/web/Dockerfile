FROM composer/composer:2-bin AS composer_upstream

FROM php:8.1-apache

RUN apt-get update -qq && \
    apt-get install -qy \
    git \
    gnupg \
    unzip \
    zip
RUN docker-php-ext-install sockets
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/*
RUN a2enmod rewrite

WORKDIR /app

RUN chown -R www-data:www-data /app

USER www-data

COPY --from=composer_upstream /composer /usr/bin/composer
ADD config/apache/vhost.conf /etc/apache2/sites-enabled/000-default.conf
ADD config/apache/apache.conf /etc/apache2/conf-enabled/z-app.conf

ADD index.php .
ADD .htaccess .
ADD composer.json .
ADD composer.lock .

RUN composer install --no-dev --no-interaction --no-progress --no-suggest --optimize-autoloader