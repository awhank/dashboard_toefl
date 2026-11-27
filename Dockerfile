FROM php:8.3-fpm

#Install dependensi sistem & ekstensi PHP untuk Laravel
RUN apt-get update && apt-get install -y git curl libpng-dev libonig-dev libxml2-dev zip unzip && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmathgd && apt-get clean && rm -rf /var/lib/apt/lists/*

#Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

#Atur hak akses folder storage & cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache