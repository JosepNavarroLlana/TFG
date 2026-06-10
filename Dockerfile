# --- Stage 1: Compilar assets de Vue con Node ---
FROM node:20 AS frontend-builder

WORKDIR /app

COPY package*.json ./
RUN npm install --legacy-peer-deps
RUN npm rebuild lightningcss --update-binary

COPY . .
RUN npm run build


# --- Stage 2: Servidor PHP/Apache para producción ---
FROM php:8.3-apache

# Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# Instalar dependencias del sistema y extensiones de PHP requeridas
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip \
    && rm -rf /var/lib/apt/lists/*

# Cambiar el Document Root de Apache a /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el proyecto
WORKDIR /var/www/html
COPY . .

# Copiar assets compilados del frontend
COPY --from=frontend-builder /app/public/build ./public/build

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80