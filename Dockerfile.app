FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl ca-certificates \
    libpng-dev libzip-dev libonig-dev libxml2-dev \
 && docker-php-ext-install pdo_mysql zip \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html

# Copy apache configs into image (so enable commands work during build)
COPY docker/apache-fqdn.conf /etc/apache2/conf-available/fqdn.conf
COPY docker/laravel.conf /etc/apache2/sites-available/laravel.conf

RUN a2enconf fqdn || true \
 && a2dissite 000-default.conf || true \
 && a2ensite laravel.conf || true \
 && apache2ctl -t

COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]