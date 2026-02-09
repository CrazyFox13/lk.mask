#!/bin/bash
# Скрипт для настройки SSH ключа на сервере
# Запустите этот скрипт на вашем локальном компьютере

echo "=== Настройка SSH ключа для автоматического деплоя ==="
echo ""

# Параметры сервера
SERVER_IP="79.174.12.78"
SERVER_USER="mack-user"
SERVER_PASSWORD="xR2vY9vE4m"
DEPLOY_PATH="/var/www/mack-user/data/www/mack_serv_production/"

# Проверка наличия sshpass (для передачи пароля)
if ! command -v sshpass &> /dev/null; then
    echo "Установка sshpass..."
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew install hudochenkov/sshpass/sshpass
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        sudo apt-get update && sudo apt-get install -y sshpass
    else
        echo "Пожалуйста, установите sshpass вручную"
        exit 1
    fi
fi

# Генерация SSH ключа, если его нет
SSH_KEY_PATH="$HOME/.ssh/github_actions_deploy"
if [ ! -f "$SSH_KEY_PATH" ]; then
    echo "Создание нового SSH ключа..."
    ssh-keygen -t ed25519 -C "github-actions-deploy" -f "$SSH_KEY_PATH" -N ""
    echo "SSH ключ создан: $SSH_KEY_PATH"
else
    echo "Использование существующего SSH ключа: $SSH_KEY_PATH"
fi

# Копирование публичного ключа на сервер
echo ""
echo "Копирование публичного ключа на сервер..."
sshpass -p "$SERVER_PASSWORD" ssh-copy-id -i "$SSH_KEY_PATH.pub" -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_IP"

# Проверка подключения
echo ""
echo "Проверка SSH подключения..."
ssh -i "$SSH_KEY_PATH" -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_IP" "echo 'SSH подключение успешно!'"

echo ""
echo "=== Настройка завершена ==="
echo ""
echo "Приватный ключ находится в: $SSH_KEY_PATH"
echo "Скопируйте содержимое этого файла в GitHub Secrets как SSH_PRIVATE_KEY"
echo ""
echo "Для просмотра ключа выполните:"
echo "cat $SSH_KEY_PATH"
