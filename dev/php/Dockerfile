FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
        curl \
        wget \
        mc \
        git \
        libonig-dev \
        libzip-dev \
        libcurl4-gnutls-dev \
        libicu-dev \
        libxml2-dev \
        locales \
        locales-all \
        zip \
    && locale-gen ru_RU.UTF-8 \
    && docker-php-ext-install -j$(nproc) iconv mbstring zip \
    && docker-php-ext-install -j$(nproc) curl intl json soap xml opcache bcmath \
    && pecl install xdebug && docker-php-ext-enable xdebug \
    && rm -rf /tmp/pear \
    && rm -frv /var/cache/apk/*

# Куда же без composer'а.
RUN  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Настройка PHP
RUN echo "post_max_size = 200M" > /usr/local/etc/php/conf.d/40-custom.ini \
    && echo "upload_max_filesize = 200M" >> /usr/local/etc/php/conf.d/40-custom.ini \
    && echo "date.timezone = Europe/Moscow" >> /usr/local/etc/php/conf.d/40-custom.ini \
    && echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/40-custom.ini \
    && echo "xdebug.log = \"/var/www/dev/php/xdebug.log\"" >> /usr/local/etc/php/conf.d/40-custom.ini \
    && echo "memory_limit = -1" >> /usr/local/etc/php/conf.d/40-custom.ini

ENV LANG ru_RU.UTF-8
ENV LANGUAGE ru_RU:ru
ENV LC_ALL ru_RU.UTF-8

# Указываем рабочую директорию для PHP
WORKDIR /var/www
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
# Запускаем контейнер
# Из документации: The main purpose of a CMD is to provide defaults for an executing container. These defaults can include an executable,
# or they can omit the executable, in which case you must specify an ENTRYPOINT instruction as well.
CMD ["php-fpm"]
# Пересборка контейнера
# docker-compose build php
