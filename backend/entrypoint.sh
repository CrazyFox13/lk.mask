#!/bin/bash

echo "DB ${DB_HOST:-localhost}"

# Проверка подключения к БД
DB_HOST=${DB_HOST:-host.docker.internal}
echo "Проверка подключения к БД: ${DB_HOST}:3306"

STATUS=3
until [ "$STATUS" -eq "0" ]; do
    echo "STATUS: $STATUS"
    nc -zv ${DB_HOST} 3306 2>&1
    STATUS=$(echo $?)
    echo "Check database connection...$STATUS"
    if [ "$STATUS" -ne "0" ]; then
        sleep 2
    fi
done
echo "DB IS OK!"

# Установка зависимостей если их нет
if [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "Установка зависимостей Composer..."
    composer install --no-dev --optimize-autoloader --no-interaction || echo "⚠ Ошибка установки зависимостей"
fi

# Генерация APP_KEY если его нет
if ! grep -q "APP_KEY=base64:" /var/www/.env 2>/dev/null; then
    echo "Генерация APP_KEY..."
    php /var/www/artisan key:generate --force || echo "⚠ Ошибка генерации ключа"
fi

# Выполнение команд Laravel только если зависимости установлены
if [ -f "/var/www/vendor/autoload.php" ]; then
    php /var/www/artisan storage:link || echo "⚠ Ошибка создания симлинка storage"
    php /var/www/artisan config:cache || echo "⚠ Ошибка кеширования config"
    php /var/www/artisan migrate --force || echo "⚠ Ошибка миграций"
    php /var/www/artisan db:seed || echo "⚠ Ошибка сидинга (может быть нормально)"
else
    echo "⚠ Зависимости не установлены, пропускаю команды artisan"
fi

# Запуск PHP-FPM
exec php-fpm
