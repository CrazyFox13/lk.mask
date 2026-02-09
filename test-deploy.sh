#!/bin/bash
# Скрипт для тестирования деплоя вручную на сервере
# Выполните этот скрипт на сервере для проверки всех шагов деплоя

set -e  # Остановка при ошибке

echo "=== Тест деплоя ==="
echo ""

DEPLOY_PATH="/var/www/mack-user/data/www/mack_serv_production/"

# Проверка 1: Директория
echo "1. Проверка директории..."
if [ ! -d "$DEPLOY_PATH" ]; then
    echo "   Создание директории: $DEPLOY_PATH"
    mkdir -p "$DEPLOY_PATH"
fi
cd "$DEPLOY_PATH"
echo "   ✓ Директория: $(pwd)"
echo ""

# Проверка 2: Git репозиторий
echo "2. Проверка Git репозитория..."
if [ ! -d ".git" ]; then
    echo "   Клонирование репозитория..."
    git clone https://github.com/CrazyFox13/lk.mask.git .
else
    echo "   Обновление репозитория..."
    git fetch origin
    git pull origin main || git pull origin master || echo "   ⚠ git pull не выполнен"
fi
echo "   ✓ Git репозиторий готов"
echo ""

# Проверка 3: Docker
echo "3. Проверка Docker..."
if command -v docker &> /dev/null; then
    echo "   ✓ Docker установлен: $(docker --version)"
else
    echo "   ✗ Docker не установлен"
    exit 1
fi

if command -v docker-compose &> /dev/null; then
    echo "   ✓ docker-compose установлен: $(docker-compose --version)"
elif docker compose version &> /dev/null; then
    echo "   ✓ docker compose установлен: $(docker compose version)"
else
    echo "   ✗ Docker Compose не установлен"
    exit 1
fi
echo ""

# Проверка 4: docker-compose.yaml
echo "4. Проверка docker-compose.yaml..."
if [ -f "docker-compose.yaml" ]; then
    echo "   ✓ docker-compose.yaml найден"
else
    echo "   ✗ docker-compose.yaml не найден"
    exit 1
fi
echo ""

# Проверка 5: Тест docker-compose
echo "5. Тест docker-compose..."
docker-compose config > /dev/null 2>&1 || docker compose config > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "   ✓ docker-compose.yaml валиден"
else
    echo "   ⚠ Проблемы с docker-compose.yaml (может быть нормально, если нет .env)"
fi
echo ""

echo "=== Все проверки пройдены ==="
echo ""
echo "Теперь можно запускать деплой через GitHub Actions"
