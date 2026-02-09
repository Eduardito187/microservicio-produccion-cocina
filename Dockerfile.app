FROM php:8.2-apache

# System deps + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl ca-certificates gnupg \
    libpng-dev libzip-dev libonig-dev libxml2-dev \
 && docker-php-ext-install pdo_mysql zip bcmath \
 && docker-php-ext-install sockets \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# Node 20 + npm (NodeSource)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get update && apt-get install -y --no-install-recommends nodejs \
 && node -v && npm -v \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html

# Apache configs
COPY docker/apache-fqdn.conf /etc/apache2/conf-available/fqdn.conf
COPY docker/laravel.conf /etc/apache2/sites-available/laravel.conf

RUN a2enconf fqdn || true \
 && a2dissite 000-default.conf || true \
 && a2ensite laravel.conf || true \
 && apache2ctl -t

# Install node deps once at build time (global node_modules in project root)
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

# Entrypoint (normalize CRLF -> LF to avoid "exec format error")
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/app-entrypoint.sh \
 && chmod +x /usr/local/bin/app-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]
