FROM php:8.2-fpm-bullseye

# ENV http_proxy=http://79.175.189.127:11107
# ENV https_proxy=http://79.175.189.127:11107

RUN apt-get update && apt-get install -y \
    librdkafka-dev \
    ffmpeg \
    apt-transport-https \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libicu-dev \
    libpq-dev \
    git \
    curl \
    unzip \
    && rm -rf /var/lib/apt/lists/*
    # php-redis \

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm

# Install PECL extensions
# RUN pecl install redis \
# 	&& docker-php-ext-enable redis

RUN docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mysqli \
    zip \
    intl \
    mbstring \
    exif \
    pcntl \
    bcmath \
    soap

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.* ./
RUN composer install --download-only
COPY . .
RUN bash -c "mkdir -p storage/framework/{sessions,views,cache}"
RUN chown -R www-data:www-data storage
RUN bash -c "DOCKER_STAGE=BUILD composer install"
RUN bash -c "DOCKER_STAGE=BUILD php artisan config:clear"
RUN bash -c "DOCKER_STAGE=BUILD php artisan optimize"
RUN bash -c "DOCKER_STAGE=BUILD php artisan storage:link"

RUN mkdir -p /app/storage/logs \
    && mkdir -p /app/bootstrap/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/storage/logs \
    && chmod -R 775 /app/storage /app/bootstrap/cache /app/storage/logs
RUN php artisan storage:link

EXPOSE 9000
USER www-data

CMD ["php-fpm"]
