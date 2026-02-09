#!/bin/bash
# Скрипт для добавления секретов через GitHub CLI
# Требуется: установленный GitHub CLI (gh) и авторизация

echo "=== Добавление секретов в GitHub через CLI ==="
echo ""

REPO="CrazyFox13/lk.mask"

# Проверка установки gh
if ! command -v gh &> /dev/null; then
    echo "GitHub CLI не установлен!"
    echo "Установите его: https://cli.github.com/"
    exit 1
fi

# Проверка авторизации
if ! gh auth status &> /dev/null; then
    echo "Требуется авторизация в GitHub CLI"
    gh auth login
fi

echo "Добавление SSH_HOST..."
echo "79.174.12.78" | gh secret set SSH_HOST --repo "$REPO"

echo "Добавление SSH_USER..."
echo "mack-user" | gh secret set SSH_USER --repo "$REPO"

echo "Добавление DEPLOY_PATH..."
echo "/var/www/mack-user/data/www/mack_serv_production/" | gh secret set DEPLOY_PATH --repo "$REPO"

echo ""
echo "Добавление SSH_PRIVATE_KEY..."
echo "Введите путь к приватному ключу на сервере (или нажмите Enter для ~/.ssh/github_actions_deploy):"
read -r KEY_PATH
KEY_PATH=${KEY_PATH:-~/.ssh/github_actions_deploy}

if [ ! -f "$KEY_PATH" ]; then
    echo "ОШИБКА: Файл не найден: $KEY_PATH"
    exit 1
fi

cat "$KEY_PATH" | gh secret set SSH_PRIVATE_KEY --repo "$REPO"

echo ""
echo "=== Все секреты добавлены! ==="
echo ""
echo "Проверьте секреты:"
gh secret list --repo "$REPO"
