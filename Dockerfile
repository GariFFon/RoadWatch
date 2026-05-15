FROM php:8.4-cli

WORKDIR /var/www/html

# System dependencies + Node.js 22.x
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
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
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

# Raise PHP upload limits (default 2MB is too small for video uploads)
RUN echo "upload_max_filesize = 64M\n\
post_max_size = 128M\n\
memory_limit = 256M\n\
max_execution_time = 120\n\
max_input_time = 120" > /usr/local/etc/php/conf.d/uploads.ini


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

# Build frontend assets (generates public/build/manifest.json)
RUN npm install && npm run build

# Storage permissions
RUN chmod -R 775 storage bootstrap/cache

# Copy and use startup script
COPY docker-start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE $PORT

CMD ["/usr/local/bin/start.sh"]
