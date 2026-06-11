FROM php:8.2-apache

# Install MongoDB PECL extension + system deps
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    unzip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy everything
COPY . .

# Install PHP dependencies (mongodb/mongodb + phpmailer)
RUN composer update --no-dev --optimize-autoloader --no-interaction

# Create uploads dir and fix permissions
RUN mkdir -p admin/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/admin/uploads

# Enable mod_rewrite and allow .htaccess
RUN a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

EXPOSE 80
