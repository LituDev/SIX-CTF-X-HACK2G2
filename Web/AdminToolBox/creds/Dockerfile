FROM composer/composer:2-bin AS composer_upstream

FROM php:8.1-apache

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
COPY --from=composer_upstream /composer /usr/bin/composer
ADD config/apache/vhost.conf /etc/apache2/sites-enabled/000-default.conf
ADD config/apache/apache.conf /etc/apache2/conf-enabled/z-app.conf

ADD index.php /app/
ADD composer.json /app/
ADD composer.lock /app/
ADD .htaccess /app/
ADD routes /app/routes
ADD entrypoint.sh /bin/entrypoint.sh
RUN chmod +x /bin/entrypoint.sh

WORKDIR /app

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /app

ENTRYPOINT ["/bin/entrypoint.sh"]