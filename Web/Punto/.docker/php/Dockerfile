FROM php:8.2-fpm
ARG TIMEZONE

COPY .docker/php/php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

RUN rm /bin/sh && ln -s /bin/bash /bin/sh
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections

RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
    acl \
    supervisor \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc

RUN docker-php-ext-configure gd --with-jpeg --with-freetype 

RUN docker-php-ext-install \
    pdo pdo_mysql pdo_sqlite zip xsl gd intl opcache exif mbstring

RUN pecl install mongodb redis && \
    docker-php-ext-enable mongodb redis

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
    && "date"

ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 20.9.0

# Install nvm with node and npm
RUN curl https://raw.githubusercontent.com/creationix/nvm/v0.24.1/install.sh | bash

RUN source $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

# add node and npm to path so the commands are available
ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

RUN chmod +x $NVM_DIR/versions/node/v$NODE_VERSION/bin/*

RUN node -v
RUN npm -v

RUN mkdir -p /var/log/supervisor
COPY .docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY .docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/symfony

COPY . /var/www/symfony
RUN chown -R www-data:www-data /var/www/symfony
RUN rm -rf /var/www/symfony/.docker

RUN echo $FLAG_3 > /var/www/symfony/flag3
RUN echo $FLAG_4 > /flag.txt
RUN chown www-data:www-data /flag.txt

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]