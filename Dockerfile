FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        mysqli \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis

# Ensure only mpm_prefork is loaded (required by php Apache module)
RUN set -eux; \
    find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.load' ! -name 'mpm_prefork.load' -delete 2>/dev/null || true; \
    find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.conf' ! -name 'mpm_prefork.conf' -delete 2>/dev/null || true; \
    a2enmod mpm_prefork rewrite headers

# Copy Apache virtual host config
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default

# Copy startup script
COPY docker/start-apache.sh /usr/local/bin/start-apache.sh
RUN chmod +x /usr/local/bin/start-apache.sh

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies (no dev, optimised autoloader)
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Create writable directories and set permissions
RUN mkdir -p writable/{cache,logs,session,uploads,debugbar} \
    && chown -R www-data:www-data writable \
    && chmod -R 755 writable

EXPOSE 80

CMD ["/usr/local/bin/start-apache.sh"]
