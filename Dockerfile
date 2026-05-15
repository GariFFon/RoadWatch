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
    libicu-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions — no GD needed (images go to S3)
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    zip \
    intl \
    opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install PHP dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# Cache Laravel config/routes (view:cache runs at startup, needs APP_KEY)
RUN php artisan config:cache \
    && php artisan route:cache

# Storage permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE $PORT

CMD php artisan view:cache \
    && php artisan migrate --force \
    && php artisan db:seed --class=CategorySeeder --force \
    && php artisan serve --host=0.0.0.0 --port=$PORT
