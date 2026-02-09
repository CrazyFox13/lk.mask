#!/bin/bash
# Скрипт для исправления конфликта портов БД
# Выполните на сервере: bash fix-port-conflict.sh

echo "=== Исправление конфликта портов БД ==="
echo ""

DEPLOY_PATH="/var/www/mack-user/data/www/mack_serv_production/"
cd "$DEPLOY_PATH" || exit 1

# Проверка занятости порта 3306
echo "1. Проверка порта 3306..."
if netstat -tuln 2>/dev/null | grep -q ":3306 " || ss -tuln 2>/dev/null | grep -q ":3306 "; then
    echo "⚠ Порт 3306 занят!"
    echo "Процессы, использующие порт 3306:"
    netstat -tulpn 2>/dev/null | grep ":3306 " || ss -tulpn 2>/dev/null | grep ":3306 " || echo "Не удалось определить"
    echo ""
    
    echo "2. Варианты решения:"
    echo ""
    echo "ВАРИАНТ А: Использовать БД на хосте (если она там есть)"
    echo "  Измените в backend/.env:"
    echo "  DB_HOST=localhost"
    echo "  DB_PORT=3306"
    echo ""
    
    echo "ВАРИАНТ Б: Использовать БД в Docker на другом порту"
    echo "  Порт уже изменен на 3307 в docker-compose.yaml"
    echo "  Используйте DB_HOST=db (внутри Docker сети)"
    echo ""
    
    # Остановка старых контейнеров
    echo "3. Остановка старых контейнеров..."
    docker-compose down 2>/dev/null || true
    
    # Проверка docker-compose.yaml
    if grep -q "3306:3306" docker-compose.yaml 2>/dev/null; then
        echo "4. Изменение порта в docker-compose.yaml..."
        sed -i 's|"3306:3306"|"3307:3306"|g' docker-compose.yaml
        echo "✓ Порт изменен на 3307"
    fi
    
    # Если БД на хосте, обновляем .env
    echo "5. Настройка .env файла..."
    cd backend || exit 1
    if [ -f ".env" ]; then
        # Проверяем, есть ли БД на localhost
        if mysql -h localhost -u mack-bd -puN3kU4aZ9k -e "SELECT 1" 2>/dev/null; then
            echo "✓ БД найдена на localhost, обновляю .env"
            sed -i 's|^DB_HOST=.*|DB_HOST=localhost|g' .env
            sed -i 's|^DB_PORT=.*|DB_PORT=3306|g' .env
        else
            echo "БД не найдена на localhost, используем Docker контейнер"
            sed -i 's|^DB_HOST=.*|DB_HOST=db|g' .env
            sed -i 's|^DB_PORT=.*|DB_PORT=3306|g' .env
        fi
    fi
    cd ..
    
    echo ""
    echo "6. Запуск контейнеров..."
    docker-compose up -d --build
    
else
    echo "✓ Порт 3306 свободен"
    echo "Запуск контейнеров..."
    docker-compose up -d --build
fi

echo ""
echo "=== Проверка статуса ==="
docker-compose ps

echo ""
echo "=== Готово ==="
