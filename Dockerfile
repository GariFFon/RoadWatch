FROM php:8.4-cli

WORKDIR /var/www/html

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions needed by Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install PHP dependencies (no dev, optimised)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# Cache Laravel config/routes/views
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Storage permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE $PORT

CMD php artisan migrate --force \
    && php artisan db:seed --class=CategorySeeder --force \
    && php artisan serve --host=0.0.0.0 --port=$PORT
