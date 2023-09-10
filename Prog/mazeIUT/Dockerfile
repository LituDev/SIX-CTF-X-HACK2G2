FROM php:8.1-alpine

RUN curl -L https://getcomposer.org/installer | php
RUN mv composer.phar /usr/bin/composer
RUN chmod o+x /usr/bin/composer

ADD . /app

WORKDIR /app

RUN composer install --no-dev --no-interaction --no-suggest --optimize-autoloader

CMD ["php", "/app/src/labyrinth.php"]