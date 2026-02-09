#!/bin/bash
# Скрипт для диагностики и исправления ошибки 500
# Выполните на сервере: bash fix-500-error.sh

set -e

echo "=== Диагностика ошибки 500 ==="
echo ""

DEPLOY_PATH="/var/www/mack-user/data/www/mack_serv_production/"

cd "$DEPLOY_PATH" || exit 1

# 1. Проверка логов Laravel
echo "1. Проверка логов Laravel..."
if [ -f "backend/storage/logs/laravel.log" ]; then
    echo "Последние ошибки из логов:"
    tail -50 backend/storage/logs/laravel.log | grep -A 5 -B 5 "ERROR\|Exception\|Error" | tail -30
else
    echo "⚠ Файл логов не найден"
fi
echo ""

# 2. Проверка .env файла
echo "2. Проверка .env файла..."
if [ -f "backend/.env" ]; then
    echo "Ключевые настройки:"
    grep -E "^APP_|^DB_" backend/.env | sed 's/PASSWORD=.*/PASSWORD=***/'
    
    # Проверка обязательных параметров
    if ! grep -q "APP_KEY=base64:" backend/.env; then
        echo "⚠ APP_KEY не установлен!"
        echo "Генерация APP_KEY..."
        cd backend
        if docker-compose ps backend 2>/dev/null | grep -q "Up"; then
            docker-compose exec -T backend php artisan key:generate --force
        fi
        cd ..
    fi
else
    echo "✗ .env файл не найден!"
    echo "Создание .env файла..."
    cd backend
    if [ -f ".env.production" ]; then
        cp .env.production .env
    elif [ -f ".env.example" ]; then
        cp .env.example .env
    fi
    
    # Настройка БД
    sed -i 's|^DB_DATABASE=.*|DB_DATABASE=lk_mack|g' .env
    sed -i 's|^DB_USERNAME=.*|DB_USERNAME=mack-bd|g' .env
    sed -i 's|^DB_PASSWORD=.*|DB_PASSWORD=uN3kU4aZ9k|g' .env
    sed -i 's|^DB_HOST=.*|DB_HOST=db|g' .env
    sed -i 's|^APP_URL=.*|APP_URL=https://lk.mack-group.ru|g' .env
    sed -i 's|^APP_ENV=.*|APP_ENV=production|g' .env
    sed -i 's|^APP_DEBUG=.*|APP_DEBUG=false|g' .env
    
    cd ..
fi
echo ""

# 3. Проверка прав доступа
echo "3. Проверка прав доступа..."
chmod -R 775 backend/storage backend/bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data backend/storage backend/bootstrap/cache 2>/dev/null || true
echo "✓ Права доступа проверены"
echo ""

# 4. Проверка подключения к БД
echo "4. Проверка подключения к БД..."
if docker-compose ps backend 2>/dev/null | grep -q "Up"; then
    echo "Тест подключения к БД..."
    docker-compose exec -T backend php artisan tinker --execute="
        try {
            \$pdo = DB::connection()->getPdo();
            echo '✓ Подключение к БД успешно\n';
            echo 'База данных: ' . DB::connection()->getDatabaseName() . '\n';
        } catch (Exception \$e) {
            echo '✗ Ошибка подключения: ' . \$e->getMessage() . '\n';
            exit(1);
        }
    " 2>&1 || echo "⚠ Проблема с подключением к БД"
else
    echo "⚠ Контейнер backend не запущен"
fi
echo ""

# 5. Очистка кеша
echo "5. Очистка кеша..."
if docker-compose ps backend 2>/dev/null | grep -q "Up"; then
    docker-compose exec -T backend php artisan config:clear || true
    docker-compose exec -T backend php artisan cache:clear || true
    docker-compose exec -T backend php artisan route:clear || true
    docker-compose exec -T backend php artisan view:clear || true
    echo "✓ Кеш очищен"
    
    # Пересоздание кеша
    echo "Создание нового кеша..."
    docker-compose exec -T backend php artisan config:cache || echo "⚠ Ошибка кеширования config"
    docker-compose exec -T backend php artisan route:cache || echo "⚠ Ошибка кеширования route"
    docker-compose exec -T backend php artisan view:cache || echo "⚠ Ошибка кеширования view"
else
    echo "⚠ Контейнер backend не запущен"
fi
echo ""

# 6. Проверка статуса контейнеров
echo "6. Статус Docker контейнеров..."
docker-compose ps
echo ""

# 7. Проверка логов контейнеров
echo "7. Последние логи backend контейнера:"
docker-compose logs --tail=30 backend 2>&1 | tail -20
echo ""

# 8. Проверка логов nginx
echo "8. Последние логи nginx:"
docker-compose logs --tail=30 nginx 2>&1 | tail -20 || echo "⚠ Логи nginx не найдены"
echo ""

# 9. Проверка конфигурации nginx
echo "9. Проверка конфигурации nginx..."
if docker-compose ps nginx 2>/dev/null | grep -q "Up"; then
    docker-compose exec -T nginx nginx -t 2>&1 || echo "⚠ Проблемы с конфигурацией nginx"
else
    echo "⚠ Контейнер nginx не запущен"
fi
echo ""

echo "=== Диагностика завершена ==="
echo ""
echo "Рекомендации:"
echo "1. Проверьте логи: tail -f backend/storage/logs/laravel.log"
echo "2. Проверьте подключение к БД (возможно нужно изменить DB_HOST на localhost)"
echo "3. Убедитесь, что все контейнеры запущены: docker-compose ps"
echo "4. Перезапустите контейнеры: docker-compose restart"
