# Инструкция по настройке автоматического деплоя

## Данные вашего сервера:
- **IP адрес:** 79.174.12.78
- **Пользователь:** mack-user
- **Пароль:** xR2vY9vE4m
- **Путь к проекту:** /var/www/mack-user/data/www/mack_serv_production/

## Шаг 1: Создание SSH ключа

### Вариант A: Windows PowerShell

1. Откройте PowerShell
2. Выполните команду для создания SSH ключа:
   ```powershell
   ssh-keygen -t ed25519 -C "github-actions-deploy" -f $env:USERPROFILE\.ssh\github_actions_deploy -N '""'
   ```
3. Или запустите скрипт:
   ```powershell
   .\setup-ssh.ps1
   ```

### Вариант B: Linux/Mac

1. Выполните команду:
   ```bash
   ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_deploy -N ""
   ```
2. Или запустите скрипт:
   ```bash
   chmod +x setup-ssh.sh
   ./setup-ssh.sh
   ```

## Шаг 2: Копирование публичного ключа на сервер

### Windows PowerShell:

```powershell
# Получите содержимое публичного ключа
$publicKey = Get-Content "$env:USERPROFILE\.ssh\github_actions_deploy.pub" -Raw

# Скопируйте ключ на сервер (введите пароль когда попросит)
$publicKey | ssh mack-user@79.174.12.78 "mkdir -p ~/.ssh && chmod 700 ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys"
```

**Пароль:** `xR2vY9vE4m`

### Linux/Mac:

```bash
ssh-copy-id -i ~/.ssh/github_actions_deploy.pub mack-user@79.174.12.78
```

**Пароль:** `xR2vY9vE4m`

### Альтернативный способ (если ssh-copy-id не работает):

```bash
cat ~/.ssh/github_actions_deploy.pub | ssh mack-user@79.174.12.78 "mkdir -p ~/.ssh && chmod 700 ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys"
```

## Шаг 3: Проверка подключения

Проверьте, что SSH подключение работает без пароля:

```bash
ssh -i ~/.ssh/github_actions_deploy mack-user@79.174.12.78 "echo 'SSH работает!'"
```

Или на Windows:
```powershell
ssh -i $env:USERPROFILE\.ssh\github_actions_deploy mack-user@79.174.12.78 "echo 'SSH работает!'"
```

## Шаг 4: Настройка секретов в GitHub

1. Перейдите в репозиторий: https://github.com/CrazyFox13/lk.mask
2. Нажмите **Settings** → **Secrets and variables** → **Actions**
3. Нажмите **New repository secret** и добавьте следующие секреты:

### SSH_HOST
- **Имя:** `SSH_HOST`
- **Значение:** `79.174.12.78`

### SSH_USER
- **Имя:** `SSH_USER`
- **Значение:** `mack-user`

### SSH_PRIVATE_KEY
- **Имя:** `SSH_PRIVATE_KEY`
- **Значение:** Скопируйте **полное содержимое** файла `~/.ssh/github_actions_deploy` (приватный ключ)
  - На Windows: `Get-Content $env:USERPROFILE\.ssh\github_actions_deploy`
  - На Linux/Mac: `cat ~/.ssh/github_actions_deploy`

### DEPLOY_PATH
- **Имя:** `DEPLOY_PATH`
- **Значение:** `/var/www/mack-user/data/www/mack_serv_production/`

### SSH_PORT (опционально)
- **Имя:** `SSH_PORT`
- **Значение:** `22` (если используется стандартный порт, можно не добавлять)

## Шаг 5: Подготовка сервера

Подключитесь к серверу и выполните:

```bash
ssh mack-user@79.174.12.78
```

На сервере:

```bash
# Перейдите в директорию проекта
cd /var/www/mack-user/data/www/mack_serv_production/

# Если проект еще не клонирован, выполните:
git clone https://github.com/CrazyFox13/lk.mask.git .

# Или если проект уже есть, обновите его:
git pull origin main

# Убедитесь, что Docker и Docker Compose установлены
docker --version
docker-compose --version

# Создайте .env файл (если его еще нет)
# Скопируйте .env.example и настройте переменные окружения
cp backend/.env.example backend/.env
nano backend/.env  # или используйте другой редактор

# Первый запуск (если еще не запускали)
docker-compose up -d
```

## Шаг 6: Проверка работы

1. Сделайте небольшое изменение в проекте (например, добавьте комментарий в README)
2. Закоммитьте и запушьте изменения:
   ```bash
   git add .
   git commit -m "Test deployment"
   git push origin main
   ```
3. Перейдите в **Actions** в репозитории GitHub
4. Вы должны увидеть запущенный workflow **Deploy via SSH**
5. Дождитесь завершения деплоя

## Ручной запуск деплоя

Вы можете запустить деплой вручную в любой момент:

1. Перейдите в **Actions** → **Deploy via SSH**
2. Нажмите **Run workflow**
3. Выберите ветку **main**
4. Нажмите **Run workflow**

## Устранение проблем

### Ошибка "Permission denied (publickey)"

- Убедитесь, что публичный ключ скопирован на сервер
- Проверьте права доступа: `chmod 600 ~/.ssh/authorized_keys` на сервере
- Убедитесь, что используете правильный приватный ключ в GitHub Secrets

### Ошибка "Host key verification failed"

- Добавьте опцию `-o StrictHostKeyChecking=no` при первом подключении
- Или добавьте сервер в `~/.ssh/known_hosts`

### Ошибка "git pull failed"

- Убедитесь, что на сервере настроен Git
- Проверьте, что репозиторий клонирован в правильную директорию
- Убедитесь, что путь `DEPLOY_PATH` указан правильно

### Ошибка "docker-compose: command not found"

- Установите Docker Compose на сервере
- Или используйте `docker compose` (без дефиса) в новых версиях Docker

## Безопасность

⚠️ **Важно:**
- Никогда не коммитьте пароли или приватные ключи в репозиторий
- Используйте SSH ключи вместо паролей для автоматизации
- Регулярно обновляйте зависимости и систему на сервере
- Используйте файрвол для ограничения доступа к SSH порту
