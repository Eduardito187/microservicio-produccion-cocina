FROM php:8.2-apache

# Extensiones necesarias para Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql zip \
    && a2enmod rewrite

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# DocumentRoot y Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf || true

WORKDIR /var/www/html