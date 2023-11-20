FROM composer/composer:2-bin AS composer_upstream

FROM php:8.1-apache

ARG DOMAIN_NAME

RUN apt-get update -qq && \
    apt-get install -qy \
    git \
    gnupg \
    unzip \
    zip \
    jq
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/*

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer_upstream --link /composer /usr/bin/composer
ADD config/apache/vhost.conf /etc/apache2/sites-enabled/000-default.conf
ADD config/apache/apache.conf /etc/apache2/conf-enabled/z-app.conf
RUN sed -i "s/DOMAIN_NAME/${DOMAIN_NAME}/g" /etc/apache2/conf-enabled/z-app.conf

ADD templates/* /app/templates/
ADD index.php /app
ADD .env /app
ADD composer.json /app
ADD composer.lock /app
ADD .htaccess /app

ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

WORKDIR /app

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /app

ENTRYPOINT ["/entrypoint.sh"]