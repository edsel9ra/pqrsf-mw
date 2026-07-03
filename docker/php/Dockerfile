FROM php:8.4.22-fpm-bookworm

ARG APP_USER=laravel
ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    git \
    curl \
    unzip \
    zip \
    ca-certificates \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    curl \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/99-custom.ini

RUN groupadd -g ${GID} ${APP_USER} \
    && useradd -u ${UID} -g ${GID} -m -s /bin/bash ${APP_USER}

WORKDIR /var/www

USER ${APP_USER}