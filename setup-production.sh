#!/bin/bash
# Скрипт для настройки production окружения на сервере
# Выполните на сервере: bash setup-production.sh

set -e

echo "=== Настройка production окружения ==="
echo ""

DEPLOY_PATH="/var/www/mack-user/data/www/mack_serv_production/"

# Переход в директорию проекта
cd "$DEPLOY_PATH" || exit 1

# 1. Настройка .env файла
echo "1. Настройка .env файла..."
cd backend || exit 1

if [ -f ".env.production" ]; then
    echo "Копирование .env.production в .env..."
    cp .env.production .env
elif [ -f ".env.example" ]; then
    echo "Копирование .env.example в .env..."
    cp .env.example .env
fi

# Обновление настроек БД
echo "Обновление настроек БД..."
sed -i 's|^DB_DATABASE=.*|DB_DATABASE=lk_mack|g' .env
sed -i 's|^DB_USERNAME=.*|DB_USERNAME=mack-bd|g' .env
sed -i 's|^DB_PASSWORD=.*|DB_PASSWORD=uN3kU4aZ9k|g' .env
sed -i 's|^DB_HOST=.*|DB_HOST=db|g' .env

# Обновление настроек приложения
echo "Обновление настроек приложения..."
sed -i 's|^APP_URL=.*|APP_URL=https://lk.mack-group.ru|g' .env
sed -i 's|^APP_ENV=.*|APP_ENV=production|g' .env
sed -i 's|^APP_DEBUG=.*|APP_DEBUG=false|g' .env

echo "✓ .env файл настроен"
cat .env | grep -E "^DB_|^APP_URL|^APP_ENV|^APP_DEBUG"
echo ""

cd ..

# 2. Генерация APP_KEY если его нет
echo "2. Проверка APP_KEY..."
if ! grep -q "APP_KEY=base64:" backend/.env; then
    echo "Генерация APP_KEY..."
    if docker-compose ps backend 2>/dev/null | grep -q "Up"; then
        docker-compose exec -T backend php artisan key:generate --force
    else
        echo "⚠ Контейнер backend не запущен, APP_KEY будет сгенерирован при первом запуске"
    fi
else
    echo "✓ APP_KEY уже установлен"
fi
echo ""

# 3. Проверка Docker контейнеров
echo "3. Проверка Docker контейнеров..."
if command -v docker-compose &> /dev/null; then
    echo "Остановка старых контейнеров..."
    docker-compose down || true
    
    echo "Сборка и запуск контейнеров..."
    docker-compose up -d --build
    
    echo "Ожидание запуска контейнеров..."
    sleep 15
    
    echo "Статус контейнеров:"
    docker-compose ps
else
    echo "⚠ Docker Compose не найден"
fi
echo ""

# 4. Установка зависимостей
echo "4. Установка зависимостей Composer..."
if docker-compose ps backend 2>/dev/null | grep -q "Up"; then
    docker-compose exec -T backend composer install --no-dev --optimize-autoloader
    echo "✓ Зависимости установлены"
else
    echo "⚠ Контейнер backend не запущен"
fi
echo ""

# 5. Выполнение миграций
echo "5. Выполнение миграций..."
if docker-compose ps backend 2>/dev/null | grep -q "Up"; then
    docker-compose exec -T backend php artisan migrate --force || echo "⚠ Ошибка при выполнении миграций"
else
    echo "⚠ Контейнер backend не запущен"
fi
echo ""

# 6. Кеширование конфигурации
echo "6. Кеширование конфигурации..."
if docker-compose ps backend 2>/dev/null | grep -q "Up"; then
    docker-compose exec -T backend php artisan config:cache || echo "⚠ Ошибка при кешировании config"
    docker-compose exec -T backend php artisan route:cache || echo "⚠ Ошибка при кешировании route"
    docker-compose exec -T backend php artisan view:cache || echo "⚠ Ошибка при кешировании view"
    echo "✓ Кеширование выполнено"
else
    echo "⚠ Контейнер backend не запущен"
fi
echo ""

# 7. Проверка подключения к БД
echo "7. Проверка подключения к БД..."
if docker-compose ps backend 2>/dev/null | grep -q "Up"; then
    echo "Тест подключения..."
    docker-compose exec -T backend php artisan tinker --execute="try { DB::connection()->getPdo(); echo '✓ Подключение к БД успешно'; } catch (Exception \$e) { echo '✗ Ошибка подключения: ' . \$e->getMessage(); }" 2>&1
else
    echo "⚠ Контейнер backend не запущен"
fi
echo ""

echo "=== Настройка завершена ==="
echo ""
echo "Проверьте статус:"
echo "  docker-compose ps"
echo "  docker-compose logs backend"
echo ""
echo "Сайт должен быть доступен по адресу: https://lk.mack-group.ru"
