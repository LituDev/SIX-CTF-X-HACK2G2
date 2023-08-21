FROM alpine:edge

WORKDIR /var/www/html

ARG APACHE_SERVER_NAME

# add repos
RUN echo "http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories

# Add basics first
RUN apk add --no-cache \
	bash \
	apache2 \
	openrc \
	php8-apache2 \
	curl \
	ca-certificates \
	openssl \
	openssh \
	git \
	mercurial \
	subversion \
	php8 \
	php8-phar \
    php8-json \
    php8-pdo_sqlite \
    php8-iconv \
	php8-openssl \
	tzdata \
	openntpd \
	unzip \
	mysql-client \
	shadow\
	php8-mbstring \
	php8-pdo \
	php8-zip \
	php8-gd \
	php8-sqlite3 \
	php8-mysqli \
	php8-xml \
	php8-xmlreader \
	php8-xmlwriter \
	php8-curl \
	php8-session \
	php8-intl \
	php8-redis \
    php8-fileinfo \
    php8-tokenizer \
    php8-opcache

RUN cp /usr/bin/php8 /usr/bin/php;
RUN rm -f /var/cache/apk/*;
RUN usermod -u 1000 apache;

# Setup apache
COPY build/virtual_host.conf /etc/apache2/httpd.conf
#Replace krhackDOMAIN with your domain
RUN sed -i "s/krhackctfDomain/${APACHE_SERVER_NAME}/g" /etc/apache2/httpd.conf

# copy files
COPY controllers/ /var/www/html/controllers/
COPY model/ /var/www/html/model/
COPY static/ /var/www/html/static/
COPY views/ /var/www/html/views/
COPY config.php /var/www/html/config.php
COPY index.php /var/www/html/index.php
COPY sport_track.db /var/www/html/sport_track.db
COPY .htaccess /var/www/html/.htaccess

RUN { \
	echo 'opcache.memory_consumption=128'; \
	echo 'opcache.interned_strings_buffer=8'; \
	echo 'opcache.max_accelerated_files=4000'; \
	echo 'opcache.max_wasted_percentage=10'; \
	echo 'opcache.enablle=1'; \
	echo 'opcache.revalidate_freq=2'; \
	echo 'opcache.fast_shutdown=1'; \
} > /etc/php8/conf.d/opcache-recommended.ini

RUN { \
	echo 'display_errors = Off'; \
	echo 'display_startup_errors = Off'; \
	echo 'log_errors = On'; \
	echo 'error_log = /dev/stderr'; \
	echo 'log_errors_max_len = 1024'; \
	echo 'ignore_repeated_errors = On'; \
	echo 'ignore_repeated_source = Off'; \
	echo 'html_errors = Off'; \
	echo 'upload_max_filesize = 16M'; \
	echo 'post_max_size = 16M'; \
	echo 'max_file_uploads = 1'; \
	echo 'realpath_cache_size=4096k'; \
	echo 'realpath_cache_ttl=7200'; \
	echo 'session.cookie_lifetime = 2592000'; \
	echo 'session.name = SESSIONID'; \
    echo 'session.cache_expire = '; \
    echo 'session.cache_limiter = '; \
} > /etc/php8/conf.d/error-logging.ini

RUN cd /var/www/html;
RUN chown -R apache:apache /var/www/html;
RUN chmod -R 775 /var/www/html;
RUN chmod 777 /var/www/html/sport_track.db;

EXPOSE 7889/udp
EXPOSE 7889/tcp

CMD ["httpd", "-D", "FOREGROUND"]