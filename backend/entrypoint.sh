#!/bin/bash

echo "DB ${DB_HOST:-localhost}"

# Проверка подключения к БД
DB_HOST=${DB_HOST:-host.docker.internal}
echo "Проверка подключения к БД: ${DB_HOST}:3306"

# Если host.docker.internal не работает, пробуем gateway IP
STATUS=3
ATTEMPTS=0
MAX_ATTEMPTS=10

until [ "$STATUS" -eq "0" ] || [ "$ATTEMPTS" -ge "$MAX_ATTEMPTS" ]; do
    echo "STATUS: $STATUS (попытка $((ATTEMPTS+1))/$MAX_ATTEMPTS)"
    nc -zv ${DB_HOST} 3306 2>&1
    STATUS=$(echo $?)
    
    if [ "$STATUS" -ne "0" ]; then
        ATTEMPTS=$((ATTEMPTS+1))
        # Если host.docker.internal не работает, пробуем gateway
        if [ "$ATTEMPTS" -eq 5 ] && [ "${DB_HOST}" = "host.docker.internal" ]; then
            echo "Пробуем использовать gateway IP вместо host.docker.internal..."
            GATEWAY_IP=$(ip route | grep default | awk '{print $3}' | head -1)
            if [ -n "$GATEWAY_IP" ]; then
                DB_HOST=$GATEWAY_IP
                echo "Используем gateway IP: $DB_HOST"
                # Обновляем .env
                sed -i "s|^DB_HOST=.*|DB_HOST=$GATEWAY_IP|g" /var/www/.env 2>/dev/null || true
            fi
        fi
        sleep 2
    fi
done

if [ "$STATUS" -eq "0" ]; then
    echo "DB IS OK! Подключение к ${DB_HOST}:3306"
else
    echo "⚠ Не удалось подключиться к БД после $MAX_ATTEMPTS попыток"
    echo "Проверьте настройки DB_HOST в .env файле"
fi

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
