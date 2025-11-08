FROM php:8.2-apache
# Extensiones necesarias para Laravel
RUN apt-get update && apt-get install -y git unzip libpng-dev libzip-dev libonig-dev libxml2-dev && docker-php-ext-install pdo_mysql zip && a2enmod rewrite
# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# DocumentRoot y Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf || true
WORKDIR /var/www/html
# Inicializacion del proyecto
RUN printf '%s\n' \
  '#!/usr/bin/env bash' \
  'set -e' \
  'a2enconf fqdn >/dev/null 2>&1 || true' \
  'a2enmod rewrite >/dev/null 2>&1 || true' \
  'a2dissite 000-default default-ssl >/dev/null 2>&1 || true' \
  'a2ensite laravel >/dev/null 2>&1 || true' \
  'chown -R www-data:www-data storage bootstrap/cache || true' \
  'chmod -R ug+rw storage bootstrap/cache || true' \
  '[ -f .env ] || cp -n .env.example .env || true' \
  'composer install --no-interaction --prefer-dist' \
  'php artisan key:generate || true' \
  'php artisan migrate --force --no-interaction || true' \
  'php artisan db:seed || true' \
  'exec apache2-foreground' \
  > /usr/local/bin/app-entrypoint.sh && chmod +x /usr/local/bin/app-entrypoint.sh
# Ejecutar al arrancar
ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]