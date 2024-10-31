FROM php:8.0-fpm

RUN apt-get update && apt-get install -y libssl-dev
# Add other necessary libraries or configurations here

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader
