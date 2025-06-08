#!/bin/bash

set -e

# Функция для ожидания готовности MySQL
wait_for_db() {
    local max_attempts=5
    local attempt=0
    local delay=5

    while [ $attempt -lt $max_attempts ]; do
        if mysqladmin ping -h db -u sso_user -p$password --silent; then
            echo "MySQL готов к подключению!"
            return 0
        else
            echo "MySQL не готов. Попытка $((attempt + 1))/$max_attempts..."
            sleep $delay
            attempt=$((attempt + 1))
        fi
    done

    echo "Не удалось подключиться к MySQL после $max_attempts попыток."
    exit 1
}

if [ ! -f "vendor/autoload.php" ]; then
    echo "Зависимости не найдены. Выполняется composer install..."
    composer install --no-interaction --optimize-autoloader
else
    echo "Зависимости уже установлены."
fi

if [ ! -f ".env" ]; then
    echo "Создаём .env из шаблона..."
    cp .env.example .env
    php artisan key:generate
fi

echo "Ожидание готовности MySQL..."
wait_for_db

echo "Запуск миграций"
php artisan migrate

echo "Очистка кэшей Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "Запуск Laravel сервера"
php artisan serve --host=0.0.0.0 --port=8000

echo "Завершено."
