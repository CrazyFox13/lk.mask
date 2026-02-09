#!/bin/bash
# Скрипт для проверки и настройки сервера
# Выполните на сервере: bash check-server.sh

echo "=== Проверка сервера ==="
echo ""

# Цвета для вывода
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

DEPLOY_PATH="/var/www/mack-user/data/www/mack_serv_production/"

# 1. Проверка директории проекта
echo "1. Проверка директории проекта..."
if [ -d "$DEPLOY_PATH" ]; then
    echo -e "${GREEN}✓${NC} Директория существует: $DEPLOY_PATH"
    cd "$DEPLOY_PATH" || exit 1
else
    echo -e "${RED}✗${NC} Директория не существует: $DEPLOY_PATH"
    echo "Создание директории..."
    mkdir -p "$DEPLOY_PATH"
    cd "$DEPLOY_PATH" || exit 1
fi
echo ""

# 2. Проверка Git репозитория
echo "2. Проверка Git репозитория..."
if [ -d ".git" ]; then
    echo -e "${GREEN}✓${NC} Git репозиторий найден"
    git remote -v
    CURRENT_BRANCH=$(git branch --show-current)
    echo "Текущая ветка: $CURRENT_BRANCH"
else
    echo -e "${YELLOW}⚠${NC} Git репозиторий не найден"
fi
echo ""

# 3. Проверка Docker
echo "3. Проверка Docker..."
if command -v docker &> /dev/null; then
    echo -e "${GREEN}✓${NC} Docker установлен: $(docker --version)"
else
    echo -e "${RED}✗${NC} Docker не установлен"
fi

if command -v docker-compose &> /dev/null; then
    echo -e "${GREEN}✓${NC} docker-compose установлен: $(docker-compose --version)"
elif docker compose version &> /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} docker compose установлен: $(docker compose version)"
else
    echo -e "${RED}✗${NC} Docker Compose не установлен"
fi
echo ""

# 4. Проверка docker-compose.yaml
echo "4. Проверка docker-compose.yaml..."
if [ -f "docker-compose.yaml" ] || [ -f "docker-compose.yml" ]; then
    echo -e "${GREEN}✓${NC} docker-compose.yaml найден"
    docker-compose config > /dev/null 2>&1 || docker compose config > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} docker-compose.yaml валиден"
    else
        echo -e "${YELLOW}⚠${NC} Проблемы с docker-compose.yaml (может быть нормально, если нет .env)"
    fi
else
    echo -e "${RED}✗${NC} docker-compose.yaml не найден"
fi
echo ""

# 5. Проверка .env файла
echo "5. Проверка .env файла..."
if [ -f "backend/.env" ]; then
    echo -e "${GREEN}✓${NC} .env файл найден"
    echo "Настройки БД:"
    grep -E "^DB_" backend/.env | sed 's/PASSWORD=.*/PASSWORD=***/'
    echo ""
    echo "Настройки приложения:"
    grep -E "^APP_" backend/.env | head -5
else
    echo -e "${YELLOW}⚠${NC} .env файл не найден"
    if [ -f "backend/.env.production" ]; then
        echo "Найден .env.production, можно скопировать"
    fi
fi
echo ""

# 6. Проверка статуса контейнеров
echo "6. Проверка статуса Docker контейнеров..."
if command -v docker-compose &> /dev/null; then
    docker-compose ps 2>/dev/null || docker compose ps 2>/dev/null
else
    echo -e "${YELLOW}⚠${NC} Docker Compose не доступен"
fi
echo ""

# 7. Проверка подключения к БД
echo "7. Проверка подключения к БД..."
if [ -f "backend/.env" ]; then
    DB_HOST=$(grep "^DB_HOST=" backend/.env | cut -d '=' -f2)
    DB_DATABASE=$(grep "^DB_DATABASE=" backend/.env | cut -d '=' -f2)
    DB_USERNAME=$(grep "^DB_USERNAME=" backend/.env | cut -d '=' -f2)
    
    echo "DB_HOST: $DB_HOST"
    echo "DB_DATABASE: $DB_DATABASE"
    echo "DB_USERNAME: $DB_USERNAME"
    
    # Попытка подключения через docker-compose
    if docker-compose ps backend 2>/dev/null | grep -q "Up"; then
        echo "Попытка подключения через контейнер backend..."
        docker-compose exec -T backend php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connection OK';" 2>&1 | head -5
    fi
fi
echo ""

# 8. Проверка портов
echo "8. Проверка открытых портов..."
netstat -tuln | grep -E ":(80|443|3000|3306|9000)" || ss -tuln | grep -E ":(80|443|3000|3306|9000)" || echo "Не удалось проверить порты"
echo ""

# 9. Проверка дискового пространства
echo "9. Проверка дискового пространства..."
df -h / | tail -1
echo ""

# 10. Проверка прав доступа
echo "10. Проверка прав доступа..."
ls -la "$DEPLOY_PATH" | head -5
echo ""

echo "=== Проверка завершена ==="
