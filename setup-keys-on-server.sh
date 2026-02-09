#!/bin/bash
# Скрипт для настройки SSH ключей на сервере
# Выполните этот скрипт на сервере от имени пользователя mack-user

echo "=== Настройка SSH ключей для автоматического деплоя ==="
echo ""

# Определяем домашнюю директорию
HOME_DIR="/var/www/mack-user/data"
SSH_DIR="$HOME_DIR/.ssh"

# Создаем директорию .ssh если её нет
echo "Создание директории .ssh..."
mkdir -p "$SSH_DIR"
chmod 700 "$SSH_DIR"

# Проверяем, есть ли уже ключи
if [ -f "$SSH_DIR/github_actions_deploy" ]; then
    echo "Ключи уже существуют в $SSH_DIR"
    read -p "Пересоздать ключи? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Используем существующие ключи"
    else
        rm -f "$SSH_DIR/github_actions_deploy" "$SSH_DIR/github_actions_deploy.pub"
        echo "Создание новых SSH ключей..."
        ssh-keygen -t ed25519 -C "github-actions-deploy" -f "$SSH_DIR/github_actions_deploy" -N ""
    fi
else
    echo "Создание новых SSH ключей..."
    ssh-keygen -t ed25519 -C "github-actions-deploy" -f "$SSH_DIR/github_actions_deploy" -N ""
fi

# Добавляем публичный ключ в authorized_keys
echo ""
echo "Добавление публичного ключа в authorized_keys..."
if [ ! -f "$SSH_DIR/authorized_keys" ]; then
    touch "$SSH_DIR/authorized_keys"
    chmod 600 "$SSH_DIR/authorized_keys"
fi

# Проверяем, не добавлен ли уже этот ключ
PUBLIC_KEY_FINGERPRINT=$(ssh-keygen -lf "$SSH_DIR/github_actions_deploy.pub" | awk '{print $2}')
if grep -q "$PUBLIC_KEY_FINGERPRINT" "$SSH_DIR/authorized_keys" 2>/dev/null; then
    echo "Ключ уже добавлен в authorized_keys"
else
    cat "$SSH_DIR/github_actions_deploy.pub" >> "$SSH_DIR/authorized_keys"
    echo "Публичный ключ добавлен в authorized_keys"
fi

chmod 600 "$SSH_DIR/authorized_keys"

echo ""
echo "=== Настройка завершена ==="
echo ""
echo "Приватный ключ находится в: $SSH_DIR/github_actions_deploy"
echo "Публичный ключ находится в: $SSH_DIR/github_actions_deploy.pub"
echo ""
echo "Для просмотра приватного ключа выполните:"
echo "cat $SSH_DIR/github_actions_deploy"
echo ""
echo "Для просмотра публичного ключа выполните:"
echo "cat $SSH_DIR/github_actions_deploy.pub"
