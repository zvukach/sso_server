FROM php:8.2-fpm

# Установка зависимостей ОС
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install zip pdo_mysql

# Установка pcntl (для работы с процессами)
RUN docker-php-ext-install pcntl

# Установка sockets (для RabbitMQ)
RUN docker-php-ext-install sockets

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Рабочая директория
WORKDIR /var/www/sso

# Настройка прав
RUN usermod -u 1000 www-data || true

EXPOSE 9000
