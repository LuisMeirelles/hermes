FROM php:8.5-cli

ARG GID=1000
ARG UID=1000

RUN groupadd admin -g $GID \
    && useradd -m admin -u $UID -g $GID -s /bin/bash

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    gnupg2 \
    libzip-dev \
    libpq-dev

RUN docker-php-ext-install pgsql pdo_pgsql zip pcntl

RUN pecl install pcov xdebug && docker-php-ext-enable pcov xdebug

RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs

RUN  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir -p /opt/phpstorm-coverage && chown admin:admin /opt/phpstorm-coverage

WORKDIR /var/www

USER admin
