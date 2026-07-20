# piGardenWeb — web control panel for piGarden (Laravel 11 / PHP 8.3)
# Single-container image: Apache + mod_php, SQLite, ready for a home NAS.
# vendor/ is committed in the repo, so no composer step is needed at build time.
FROM php:8.3-apache

# --- System libraries + PHP extensions required by Laravel 11 + Backpack ---
# gd: image/icon handling + dompdf | zip: elfinder/backpack | pdo_sqlite: database
# intl/mbstring/exif/bcmath: framework + packages | opcache: performance
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libzip-dev libicu-dev libonig-dev \
        sqlite3 libsqlite3-dev \
        curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd zip pdo_sqlite intl mbstring exif bcmath opcache \
    && rm -rf /var/lib/apt/lists/*

# --- Apache: serve Laravel's public/ and allow .htaccess rewrites ---
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers

# --- PHP production config ---
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-pigardenweb.ini

WORKDIR /var/www/html

# --- Application code (vendor/ included; .dockerignore trims dev-only files) ---
COPY . /var/www/html

# Writable dirs for Laravel (volumes may override storage/ at runtime; the
# entrypoint recreates the skeleton if the mounted volume is empty)
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD curl -fsS http://localhost/ >/dev/null || exit 1

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["apache2-foreground"]
