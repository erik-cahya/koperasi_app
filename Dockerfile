FROM php:5.6-apache

# Fix Debian Stretch repository (karena sudah EOL)
RUN sed -i 's|deb.debian.org|archive.debian.org|g' /etc/apt/sources.list \
 && sed -i 's|security.debian.org|archive.debian.org|g' /etc/apt/sources.list \
 && sed -i '/stretch-updates/d' /etc/apt/sources.list

RUN apt-get -o Acquire::Check-Valid-Until=false update \
    && apt-get install -y --allow-unauthenticated \
    libmcrypt-dev \
    && docker-php-ext-install mcrypt

RUN docker-php-ext-install mysql mysqli pdo pdo_mysql

RUN a2enmod rewrite

# Enable AllowOverride All agar .htaccess berfungsi
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html