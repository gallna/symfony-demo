FROM php:7.1-apache

RUN apt-get update && apt-get install -y \
    git \
    build-essential \
    libssl-dev

# Compile Data Structures extension
RUN git clone https://github.com/php-ds/extension "php-ds" \
    && cd php-ds \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable ds

RUN docker-php-ext-install pdo_mysql && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install zip

RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

RUN apt-get install -y curl libcurl4-openssl-dev && \
    docker-php-ext-install curl

RUN apt-get install -y libmemcached-dev \
    && git clone --branch php7 https://github.com/php-memcached-dev/php-memcached "memcached" \
    && cd memcached \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable memcached

RUN apt-get install -y libicu-dev && \
    docker-php-ext-install intl

RUN apt-get install -y libmcrypt-dev && \
    docker-php-ext-install mcrypt

RUN apt-get install -y libxml2-dev && \
    docker-php-ext-install soap

RUN apt-get install -y memcached

# Compile Redis extension
RUN git clone --branch php7 https://github.com/phpredis/phpredis "php-redis" \
    && cd php-redis \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-enable redis

# PHP config
COPY php.ini /usr/local/etc/php/php.ini

# Apache config
COPY prod.conf /etc/apache2/sites-enabled/prod.conf
COPY dev.conf /etc/apache2/sites-enabled/dev.conf
RUN echo "Listen 8080" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite headers
RUN service apache2 restart
