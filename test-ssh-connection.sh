#!/bin/bash
# Скрипт для проверки SSH подключения
# Этот скрипт можно использовать для тестирования подключения к серверу

echo "=== Проверка SSH подключения ==="
echo ""

SSH_HOST="79.174.12.78"
SSH_USER="mack-user"
DEPLOY_PATH="/var/www/mack-user/data/www/mack_serv_production/"

echo "Хост: $SSH_HOST"
echo "Пользователь: $SSH_USER"
echo "Путь деплоя: $DEPLOY_PATH"
echo ""

# Проверка 1: Подключение к серверу
echo "1. Проверка SSH подключения..."
if ssh -o BatchMode=yes -o ConnectTimeout=5 "$SSH_USER@$SSH_HOST" "echo 'SSH подключение работает!'" 2>/dev/null; then
    echo "✓ SSH подключение успешно"
else
    echo "✗ SSH подключение не работает. Проверьте ключи."
    exit 1
fi

# Проверка 2: Существование директории деплоя
echo ""
echo "2. Проверка директории деплоя..."
if ssh "$SSH_USER@$SSH_HOST" "test -d '$DEPLOY_PATH'" 2>/dev/null; then
    echo "✓ Директория существует: $DEPLOY_PATH"
else
    echo "✗ Директория не найдена: $DEPLOY_PATH"
    echo "Создание директории..."
    ssh "$SSH_USER@$SSH_HOST" "mkdir -p '$DEPLOY_PATH'"
fi

# Проверка 3: Git репозиторий
echo ""
echo "3. Проверка Git репозитория..."
if ssh "$SSH_USER@$SSH_HOST" "cd '$DEPLOY_PATH' && git status" 2>/dev/null; then
    echo "✓ Git репозиторий найден"
else
    echo "⚠ Git репозиторий не найден. Нужно клонировать проект."
fi

# Проверка 4: Docker и Docker Compose
echo ""
echo "4. Проверка Docker..."
if ssh "$SSH_USER@$SSH_HOST" "docker --version" 2>/dev/null; then
    echo "✓ Docker установлен"
    ssh "$SSH_USER@$SSH_HOST" "docker --version"
else
    echo "✗ Docker не установлен"
fi

if ssh "$SSH_USER@$SSH_HOST" "docker-compose --version || docker compose version" 2>/dev/null; then
    echo "✓ Docker Compose установлен"
    ssh "$SSH_USER@$SSH_HOST" "docker-compose --version || docker compose version"
else
    echo "✗ Docker Compose не установлен"
fi

echo ""
echo "=== Проверка завершена ==="
