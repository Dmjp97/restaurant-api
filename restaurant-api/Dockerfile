FROM php:8.2-apache

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
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Ensure only one MPM is loaded and enable mod_rewrite
RUN set -eux; \
    find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.load' ! -name 'mpm_prefork.load' -delete; \
    find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.conf' ! -name 'mpm_prefork.conf' -delete; \
    a2enmod mpm_prefork rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Start Apache through a wrapper that normalizes the loaded MPM modules first
COPY docker/start-apache.sh /usr/local/bin/start-apache.sh
RUN chmod +x /usr/local/bin/start-apache.sh
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf || true

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p /var/www/html/writable && chown -R www-data:www-data /var/www/html/writable

EXPOSE 80

CMD ["start-apache.sh"]
