FROM php:8.2-apache

# Paquetes y extensiones PHP usuales para Laravel
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_mysql zip intl gd bcmath \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN printf "ServerName localhost\n" > /etc/apache2/conf-available/fqdn.conf \
    && a2enconf fqdn

# Copia la conf y actívala
COPY docker/apache-fqdn.conf /etc/apache2/conf-available/fqdn.conf
RUN a2enconf fqdn

# Configurar DocumentRoot a /public
ARG APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri "s#DocumentRoot /var/www/html#DocumentRoot ${APACHE_DOCUMENT_ROOT}#g" /etc/apache2/sites-available/000-default.conf \
 && sed -ri "s#<Directory /var/www/>#<Directory ${APACHE_DOCUMENT_ROOT}/>#g" /etc/apache2/apache2.conf \
 && sed -ri "s#AllowOverride None#AllowOverride All#g" /etc/apache2/apache2.conf

WORKDIR /var/www/html