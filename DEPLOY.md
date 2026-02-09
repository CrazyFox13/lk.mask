# Инструкция по настройке автоматического деплоя

Этот проект поддерживает автоматический деплой на хостинг через GitHub Actions.

## Варианты деплоя

### Вариант 1: Деплой на VPS через SSH (рекомендуется)

Этот вариант подходит, если у вас есть VPS с SSH доступом.

#### Настройка:

1. **Создайте SSH ключ для деплоя** (если еще нет):
   ```bash
   ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions
   ```

2. **Скопируйте публичный ключ на сервер**:
   ```bash
   ssh-copy-id -i ~/.ssh/github_actions.pub user@your-server.com
   ```

3. **Добавьте секреты в GitHub**:
   - Перейдите в Settings → Secrets and variables → Actions
   - Добавьте следующие секреты:
     - `SSH_HOST` - IP адрес или домен вашего сервера
     - `SSH_USER` - имя пользователя для SSH
     - `SSH_PRIVATE_KEY` - содержимое приватного ключа (начинается с `-----BEGIN OPENSSH PRIVATE KEY-----`)
     - `SSH_PORT` - порт SSH (обычно 22, можно не указывать)
     - `DEPLOY_PATH` - путь к проекту на сервере (например, `/var/www/astt_serv`)

4. **Настройте сервер**:
   - Убедитесь, что на сервере установлены: Git, Docker, Docker Compose
   - Клонируйте репозиторий на сервер:
     ```bash
     git clone https://github.com/CrazyFox13/lk.mask.git /var/www/astt_serv
     cd /var/www/astt_serv
     ```

5. **Создайте `.env` файл на сервере** с необходимыми переменными окружения

6. **Первый запуск**:
   ```bash
   docker-compose up -d
   ```

После этого каждый push в ветку `main` будет автоматически деплоить изменения на сервер.

---

### Вариант 2: Деплой через Docker Hub

Если ваш хостинг использует образы из Docker Hub.

#### Настройка:

1. **Создайте аккаунт на Docker Hub** (если еще нет)

2. **Добавьте секреты в GitHub**:
   - `DOCKERHUB_USERNAME` - ваш username на Docker Hub
   - `DOCKERHUB_TOKEN` - токен доступа (Settings → Security → New Access Token)

3. **Настройте хостинг** для использования образов:
   - `your-username/astt-backend:latest`
   - `your-username/astt-frontend:latest`
   - `your-username/astt-web:latest`

---

### Вариант 3: Webhook деплой

Если ваш хостинг поддерживает webhooks (например, некоторые панели управления).

#### Настройка:

1. **Получите URL webhook** от вашего хостинга

2. **Добавьте секрет в GitHub**:
   - `DEPLOY_WEBHOOK_URL` - URL вашего webhook

---

## Ручной деплой

Вы также можете запустить деплой вручную:

1. Перейдите в Actions → Deploy via SSH
2. Нажмите "Run workflow"
3. Выберите ветку и нажмите "Run workflow"

---

## Переменные окружения

Убедитесь, что на сервере создан файл `.env` в корне проекта со следующими переменными:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# App
APP_ENV=production
APP_DEBUG=false
APP_KEY=your_app_key

# Другие необходимые переменные...
```

---

## Проверка деплоя

После деплоя проверьте:

1. Статус контейнеров:
   ```bash
   docker-compose ps
   ```

2. Логи:
   ```bash
   docker-compose logs -f
   ```

3. Доступность приложения по указанному домену/IP

---

## Откат изменений

Если что-то пошло не так, можно откатиться к предыдущему коммиту:

```bash
cd /var/www/astt_serv
git checkout <previous-commit-hash>
docker-compose up -d --build
```
