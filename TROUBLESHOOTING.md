# Устранение проблем с деплоем

## Типичные ошибки и решения

### Ошибка 1: "Permission denied (publickey)"

**Причина:** SSH ключ не настроен правильно или публичный ключ не добавлен в authorized_keys

**Решение:**

1. На сервере проверьте наличие публичного ключа в authorized_keys:
   ```bash
   cat ~/.ssh/authorized_keys | grep github-actions-deploy
   ```

2. Если ключа нет, добавьте его:
   ```bash
   cat ~/.ssh/github_actions_deploy.pub >> ~/.ssh/authorized_keys
   chmod 600 ~/.ssh/authorized_keys
   ```

3. Проверьте права доступа:
   ```bash
   chmod 700 ~/.ssh
   chmod 600 ~/.ssh/authorized_keys
   chmod 600 ~/.ssh/github_actions_deploy
   ```

### Ошибка 2: "No such file or directory" для DEPLOY_PATH

**Причина:** Директория не существует на сервере

**Решение:**

```bash
# Создайте директорию на сервере
mkdir -p /var/www/mack-user/data/www/mack_serv_production/
chown -R mack-user:mack-user /var/www/mack-user/data/www/mack_serv_production/
```

### Ошибка 3: "git pull failed" или "Not a git repository"

**Причина:** Проект не клонирован или не настроен Git

**Решение:**

```bash
cd /var/www/mack-user/data/www/mack_serv_production/

# Если директория пустая, клонируйте проект
if [ ! -d ".git" ]; then
  git clone https://github.com/CrazyFox13/lk.mask.git .
fi

# Проверьте remote
git remote -v
# Если remote не настроен:
git remote add origin https://github.com/CrazyFox13/lk.mask.git
```

### Ошибка 4: "docker-compose: command not found"

**Причина:** Docker Compose не установлен или используется другая команда

**Решение:**

```bash
# Проверьте версию Docker
docker --version

# В новых версиях Docker используется команда без дефиса
docker compose version

# Если docker-compose не установлен, установите его или используйте docker compose
```

### Ошибка 5: Проблемы с форматом SSH ключа в GitHub Secrets

**Причина:** Ключ скопирован неправильно (с лишними пробелами, без BEGIN/END маркеров)

**Решение:**

1. На сервере получите правильный ключ:
   ```bash
   cat ~/.ssh/github_actions_deploy
   ```

2. Убедитесь, что ключ содержит:
   - Первую строку: `-----BEGIN OPENSSH PRIVATE KEY-----`
   - Все строки с base64 данными (могут быть с переносами)
   - Последнюю строку: `-----END OPENSSH PRIVATE KEY-----`

3. В GitHub Secrets вставляйте ключ **точно так, как он есть**, включая все переносы строк

### Ошибка 6: "Host key verification failed"

**Решение:**

Добавьте опцию в workflow (уже добавлено в обновленной версии) или на сервере:

```bash
ssh-keyscan -H 79.174.12.78 >> ~/.ssh/known_hosts
```

## Проверка настройки

### Шаг 1: Проверка SSH ключей на сервере

```bash
# Подключитесь к серверу
ssh mack-user@79.174.12.78

# Проверьте наличие ключей
ls -la ~/.ssh/github_actions_deploy*

# Должны быть оба файла:
# github_actions_deploy (приватный)
# github_actions_deploy.pub (публичный)

# Проверьте, что публичный ключ в authorized_keys
grep "github-actions-deploy" ~/.ssh/authorized_keys
```

### Шаг 2: Проверка подключения без пароля

```bash
# С локального компьютера (если есть приватный ключ)
ssh -i ~/.ssh/github_actions_deploy mack-user@79.174.12.78 "echo 'Тест подключения'"

# Должно работать без запроса пароля
```

### Шаг 3: Проверка GitHub Secrets

Убедитесь, что все 4 секрета созданы:

1. `SSH_HOST` = `79.174.12.78`
2. `SSH_USER` = `mack-user`
3. `SSH_PRIVATE_KEY` = (полный ключ с BEGIN и END)
4. `DEPLOY_PATH` = `/var/www/mack-user/data/www/mack_serv_production/`

### Шаг 4: Проверка логов GitHub Actions

1. Перейдите: https://github.com/CrazyFox13/lk.mask/actions
2. Откройте последний запуск workflow
3. Посмотрите логи, особенно секцию "Deploy to server"
4. Найдите строки с ошибками (обычно начинаются с "Error:" или "Failed:")

## Полная переустановка SSH ключей

Если ничего не помогает, пересоздайте ключи:

```bash
# На сервере
cd ~/.ssh
rm -f github_actions_deploy github_actions_deploy.pub

# Создайте новый ключ
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy -N ""

# Добавьте публичный ключ в authorized_keys
cat ~/.ssh/github_actions_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Покажите приватный ключ для копирования в GitHub
echo "=== ПРИВАТНЫЙ КЛЮЧ (скопируйте для GitHub Secrets) ==="
cat ~/.ssh/github_actions_deploy
```

## Получение помощи

Если проблема не решена:

1. Скопируйте полный текст ошибки из логов GitHub Actions
2. Проверьте, что все шаги из инструкции выполнены
3. Убедитесь, что на сервере установлены все необходимые инструменты (Git, Docker, Docker Compose)
