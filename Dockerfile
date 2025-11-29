# ========= STAGE 1: BUILD (Composer) =========
FROM composer:2 AS build
WORKDIR /app

# Copiamos composer.* primero para cachear dependencias
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts

# Ahora copiamos el resto del proyecto (TU CÓDIGO)
COPY . .

# Instala deps con autoload optimizado
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress \
 && composer dump-autoload --optimize

# ========= STAGE 2: RUNTIME (PHP 8.2 + Apache) =========
FROM php:8.2-apache
WORKDIR /var/www/html

# Extensiones necesarias (ajusta según tu app)

RUN apt-get update && apt-get install -y git unzip libpng-dev libzip-dev libonig-dev libxml2-dev && docker-php-ext-install pdo_mysql zip && a2enmod rewrite

# DocumentRoot -> public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
 && sed -ri -e 's!<Directory /var/www/>!<Directory /var/www/html/public>!g' /etc/apache2/apache2.conf || true

# Copiamos código + vendor desde la etapa build
COPY --from=build /app /var/www/html

# Permisos para Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R ug+rw storage bootstrap/cache

# Opcional: crea .env si no existe (útil para entornos efímeros)
RUN [ -f .env ] || ( [ -f .env.example ] && cp .env.example .env ) || true

# Healthcheck simple
HEALTHCHECK --interval=10s --timeout=3s --retries=5 CMD curl -fsS http://localhost/ || exit 1

# Entry: migrar sólo si te conviene; aquí lo dejamos simple
CMD ["apache2-foreground"]