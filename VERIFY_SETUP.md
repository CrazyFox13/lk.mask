# Проверка настройки автоматического деплоя

## Важные замечания о SSH ключе

Я заметил, что в вашем `SSH_PRIVATE_KEY` может быть проблема. SSH приватный ключ должен выглядеть так:

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAJiPrUfFj61HxQAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAEDSzwDe9ldrkaxW0xQY7TymActq8eqjx9ADI59Q8EY55D/Ld44EqStBmYmvK+RBsRt0OHXHyaXM15UuRTqZigmpcAAAAFWdpdGh1Yi1hY3Rpb25zLWRlcGxveQ==
-----END OPENSSH PRIVATE KEY-----
```

**Важно:** Ключ должен начинаться с `-----BEGIN OPENSSH PRIVATE KEY-----` и заканчиваться `-----END OPENSSH PRIVATE KEY-----`.

## Проверка правильности ключа

### На сервере выполните:

```bash
# Проверьте, что ключ существует
cat ~/.ssh/github_actions_deploy

# Ключ должен начинаться с -----BEGIN и заканчиваться -----END
```

### В GitHub Secrets:

Убедитесь, что вы скопировали **весь ключ целиком**, включая:
- Первую строку: `-----BEGIN OPENSSH PRIVATE KEY-----`
- Все строки с закодированными данными
- Последнюю строку: `-----END OPENSSH PRIVATE KEY-----`

## Тестирование деплоя

### Вариант 1: Ручной запуск через GitHub Actions

1. Перейдите в репозиторий: https://github.com/CrazyFox13/lk.mask
2. Откройте вкладку **Actions**
3. Выберите workflow **Deploy via SSH**
4. Нажмите **Run workflow**
5. Выберите ветку **main**
6. Нажмите зеленую кнопку **Run workflow**

### Вариант 2: Тест через локальный компьютер

Если у вас есть доступ к серверу с локального компьютера:

```bash
# Проверьте подключение
ssh -i ~/.ssh/github_actions_deploy mack-user@79.174.12.78 "echo 'Тест подключения'"

# Проверьте директорию
ssh -i ~/.ssh/github_actions_deploy mack-user@79.174.12.78 "ls -la /var/www/mack-user/data/www/mack_serv_production/"
```

## Проверка на сервере

Подключитесь к серверу и выполните:

```bash
# 1. Проверьте, что директория существует
cd /var/www/mack-user/data/www/mack_serv_production/
pwd

# 2. Если проект еще не клонирован, клонируйте его
if [ ! -d ".git" ]; then
    git clone https://github.com/CrazyFox13/lk.mask.git .
fi

# 3. Проверьте наличие docker-compose.yaml
ls -la docker-compose.yaml

# 4. Проверьте Docker
docker --version
docker-compose --version
```

## Типичные проблемы и решения

### Проблема: "Permission denied (publickey)"

**Решение:**
1. Убедитесь, что публичный ключ добавлен в `~/.ssh/authorized_keys` на сервере
2. Проверьте права доступа: `chmod 600 ~/.ssh/authorized_keys`
3. Убедитесь, что приватный ключ в GitHub Secrets полный и правильный

### Проблема: "No such file or directory" для DEPLOY_PATH

**Решение:**
```bash
# Создайте директорию на сервере
mkdir -p /var/www/mack-user/data/www/mack_serv_production/
```

### Проблема: "git pull failed"

**Решение:**
```bash
# На сервере убедитесь, что репозиторий клонирован
cd /var/www/mack-user/data/www/mack_serv_production/
git remote -v
# Если репозиторий не настроен:
git remote add origin https://github.com/CrazyFox13/lk.mask.git
```

## После успешной настройки

После того как всё настроено:
- Каждый `git push origin main` будет автоматически деплоить изменения
- Вы можете запускать деплой вручную через GitHub Actions
- Логи деплоя будут видны во вкладке Actions
