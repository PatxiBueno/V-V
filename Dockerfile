
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libcurl4-openssl-dev \
    libpq-dev \
    libzip-dev \
    libbz2-dev \
    libxml2-dev \
    libpng-dev \
    libonig-dev \
    && docker-php-ext-install \
        bz2 \
        curl \
        mysqli \
        pdo \
        pdo_mysql \
        pgsql \
        pdo_pgsql \
        zip

RUN a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
