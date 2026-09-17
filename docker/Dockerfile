FROM composer:2 AS composer

FROM php:8.2-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        default-mysql-client \
        postgresql-client \
    && docker-php-ext-install \
        intl \
        mbstring \
        pdo \
        pdo_pgsql \
        pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# SQLite support is compiled into the official PHP CLI images (pdo_sqlite).
RUN php -m | grep -qi pdo_sqlite && echo 'pdo_sqlite available'

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1

CMD ["bash"]
