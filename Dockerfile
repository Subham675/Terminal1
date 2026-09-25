FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    default-mysql-client \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer (for optional PHPMailer / dompdf / PHPUnit)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/terminal1

COPY . .

RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader || true; fi

RUN chown -R www-data:www-data /var/www/terminal1 \
    && chmod -R 755 /var/www/terminal1 \
    && chmod -R 775 /var/www/terminal1/storage

EXPOSE 9000
CMD ["php-fpm"]
