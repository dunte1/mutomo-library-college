# Multi-stage build: OLLMCHS Library Management System

# ---- Build frontend assets ----
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# ---- PHP dependencies ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# ---- Production image ----
FROM php:8.3-fpm-alpine AS app

ARG APP_ENV=production
ARG APP_DEBUG=false

ENV APP_ENV=${APP_ENV} \
    APP_DEBUG=${APP_DEBUG}

RUN set -eux; \
    apk add --no-cache \
        nginx \
        supervisor \
        sqlite \
        sqlite-libs \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        postgresql-dev \
        mysql-client \
        bash \
        curl; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        gd \
        zip \
        mbstring \
        opcache \
        bcmath; \
    apk del --purge; \
    rm -rf /var/cache/apk/*

RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.validate_timestamps=0'; \
} > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
    echo 'max_execution_time=300'; \
    echo 'memory_limit=256M'; \
    echo 'upload_max_filesize=100M'; \
    echo 'post_max_size=105M'; \
    echo 'date.timezone=Africa/Nairobi'; \
} > /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY . .

RUN set -eux; \
    mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache; \
    chown -R www-data:www-data \
        storage \
        bootstrap/cache \
        public; \
    chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
