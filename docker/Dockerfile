FROM php:8.2-apache

# --- Extensiones PHP y utilidades ---
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip zip libicu-dev libzip-dev libonig-dev default-mysql-client \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" intl pdo_mysql zip opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# --- Node 20 + Yarn (necesarios para Webpack Encore) ---
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && npm install -g yarn \
    && rm -rf /var/lib/apt/lists/*

# --- Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Apache: docroot en public/ ---
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Instalamos dependencias PHP primero para aprovechar la cache de capas
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-scripts --no-interaction --prefer-dist

# El resto del proyecto
COPY . .

# package.json apunta a vendor/symfony/ux-*/assets, así que yarn va DESPUÉS de composer
RUN yarn install --frozen-lockfile && yarn build

RUN composer dump-autoload --optimize \
    && mkdir -p var config/jwt \
    && chown -R www-data:www-data var config/jwt public/build

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
