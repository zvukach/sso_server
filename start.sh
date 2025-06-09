#!/bin/bash

set -e

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
    php artisan jwt:secret
fi

echo "Запуск Laravel сервера"
php artisan serve --host=0.0.0.0 --port=8000

echo "Завершено."
