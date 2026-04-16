# ===========================================================================
# Club Atlético Deportivo Acarigua — Imagen de despliegue
# Base: PHP 8.2 + Apache
# ===========================================================================
FROM php:8.2-apache

LABEL maintainer="CADA <administracion@solopsoftware.com>"
LABEL description="Club Atlético Deportivo Acarigua - Sistema de gestión deportiva"

ENV APP_ENV=production \
    APP_DEBUG=false \
    APACHE_DOCUMENT_ROOT=/var/www/html/public \
    PHP_INI_DIR=/usr/local/etc/php

# ---------------------------------------------------------------------------
# Dependencias del sistema y extensiones PHP
# ---------------------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev libicu-dev libonig-dev \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mysqli mbstring gd zip intl opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------------------------
# Apache: habilitar mod_rewrite + headers y apuntar al /public
# ---------------------------------------------------------------------------
RUN a2enmod rewrite headers expires \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# ---------------------------------------------------------------------------
# Configuración PHP (producción)
# ---------------------------------------------------------------------------
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/php.ini $PHP_INI_DIR/conf.d/zz-cada.ini

# ---------------------------------------------------------------------------
# Composer (opcional pero recomendado para TCPDF)
# ---------------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------------------
# Código de la aplicación
# ---------------------------------------------------------------------------
WORKDIR /var/www/html

# Primero composer files para aprovechar caché de capas
COPY composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist 2>/dev/null || true

# Resto del proyecto
COPY . .

# Autoload optimizado (si composer.json es válido)
RUN composer dump-autoload --optimize --no-dev 2>/dev/null || true

# Permisos: Apache (www-data) debe poder escribir logs y uploads
RUN mkdir -p /var/www/html/storage/logs /var/www/html/storage/cache \
             /var/www/html/public/assets/uploads/atletas \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/public/assets/uploads \
    && chmod -R 775 /var/www/html/storage /var/www/html/public/assets/uploads

# ---------------------------------------------------------------------------
# Entrypoint: espera MySQL, importa BD si hace falta, levanta Apache
# ---------------------------------------------------------------------------
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://localhost/ || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
