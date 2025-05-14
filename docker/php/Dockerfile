FROM php:8.2-fpm

# install system deps, Node.js, Composer, PHP extensions
RUN apt-get update \
 && apt-get install -y \
      curl gnupg git zip unzip libonig-dev libxml2-dev libzip-dev \
 && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get install -y nodejs \
 && docker-php-ext-install pdo pdo_mysql zip \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# bring in Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
