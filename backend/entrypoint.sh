#!/bin/bash

echo "DB ${DB_HOST:-localhost}"

# Проверка подключения к БД на localhost (БД на хосте, используется network_mode: host)
DB_HOST=${DB_HOST:-localhost}
echo "Проверка подключения к БД: ${DB_HOST}:3306"

STATUS=3
ATTEMPTS=0
MAX_ATTEMPTS=10

until [ "$STATUS" -eq "0" ] || [ "$ATTEMPTS" -ge "$MAX_ATTEMPTS" ]; do
    echo "STATUS: $STATUS (попытка $((ATTEMPTS+1))/$MAX_ATTEMPTS)"
    nc -zv ${DB_HOST} 3306 2>&1 || nc -zv 127.0.0.1 3306 2>&1
    STATUS=$(echo $?)
    
    if [ "$STATUS" -ne "0" ]; then
        ATTEMPTS=$((ATTEMPTS+1))
        sleep 2
    fi
done

if [ "$STATUS" -eq "0" ]; then
    echo "DB IS OK! Подключение к ${DB_HOST}:3306"
else
    echo "⚠ Не удалось подключиться к БД после $MAX_ATTEMPTS попыток"
    echo "Проверьте, что MySQL запущен на хосте и доступен на порту 3306"
fi

# Установка зависимостей если их нет
if [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "Установка зависимостей Composer..."
    
    # Проверяем синхронизацию composer.lock с composer.json
    if [ -f "/var/www/composer.lock" ]; then
        # Проверяем наличие dompdf в lock файле (если он есть в composer.json)
        if grep -q '"dompdf/dompdf"' /var/www/composer.json 2>/dev/null && ! grep -q '"name": "dompdf/dompdf"' /var/www/composer.lock 2>/dev/null; then
            echo "⚠ composer.lock не синхронизирован (dompdf отсутствует), удаляем и обновляем..."
            rm -f /var/www/composer.lock
            composer update --no-dev --optimize-autoloader --no-interaction --with-all-dependencies 2>&1 || {
                echo "⚠ composer update не удался, пробуем install без lock файла..."
                composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs 2>&1 || {
                    echo "⚠ КРИТИЧЕСКАЯ ОШИБКА: Не удалось установить зависимости!"
                    exit 1
                }
            }
        else
            # Lock файл синхронизирован, пробуем install
            composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs 2>&1 || {
                echo "⚠ composer install не удался, пробуем update..."
                rm -f /var/www/composer.lock
                composer update --no-dev --optimize-autoloader --no-interaction --with-all-dependencies 2>&1 || {
                    echo "⚠ КРИТИЧЕСКАЯ ОШИБКА: Не удалось установить зависимости!"
                    exit 1
                }
            }
        fi
    else
        # Lock файла нет, создаем через update
        echo "composer.lock не найден, создаем через composer update..."
        composer update --no-dev --optimize-autoloader --no-interaction --with-all-dependencies 2>&1 || {
            echo "⚠ composer update не удался, пробуем install..."
            composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs 2>&1 || {
                echo "⚠ КРИТИЧЕСКАЯ ОШИБКА: Не удалось установить зависимости!"
                exit 1
            }
        }
    fi
    
    # Проверяем результат
    if [ ! -f "/var/www/vendor/autoload.php" ]; then
        echo "⚠ КРИТИЧЕСКАЯ ОШИБКА: vendor/autoload.php не создан!"
        echo "Проверьте composer.json и composer.lock на наличие ошибок"
        exit 1
    fi
    
    echo "✓ Зависимости успешно установлены"
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
