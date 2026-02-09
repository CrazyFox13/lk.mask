# Где добавить секреты в GitHub

## ⚠️ ВАЖНО: Правильное место для секретов

Вы находитесь в разделе **Environment secrets** (секреты окружения), но нужно добавлять в **Repository secrets** (секреты репозитория)!

## Правильный путь:

1. Перейдите в репозиторий: https://github.com/CrazyFox13/lk.mask
2. Нажмите на вкладку **Settings** (вверху репозитория)
3. В левом меню найдите раздел **Secrets and variables**
4. Нажмите **Actions** (НЕ Environments!)
5. Вы увидите страницу **Actions secrets**
6. Нажмите кнопку **New repository secret**

## Пошаговая инструкция:

### Шаг 1: Откройте правильную страницу

Прямая ссылка: https://github.com/CrazyFox13/lk.mask/settings/secrets/actions

### Шаг 2: Добавьте секреты

Нажмите **"New repository secret"** и добавьте по очереди:

#### Секрет 1: SSH_HOST
- **Name:** `SSH_HOST`
- **Secret:** `79.174.12.78`
- Нажмите **"Add secret"**

#### Секрет 2: SSH_USER
- **Name:** `SSH_USER`
- **Secret:** `mack-user`
- Нажмите **"Add secret"**

#### Секрет 3: SSH_PRIVATE_KEY
- **Name:** `SSH_PRIVATE_KEY`
- **Secret:** (скопируйте из файла `SSH_KEY_FOR_GITHUB.txt`):
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAJiPrUfFj61HxQAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAEDSzwDe9ldrkaxW0xQY7TymActq8eqjx9ADI59Q8EY55D/Ld44EqStBmYmvK+RBsRt0OHXHyaXM15UuRTqZigmpcAAAAFWdpdGh1Yi1hY3Rpb25zLWRlcGxveQ==
-----END OPENSSH PRIVATE KEY-----
```
- Нажмите **"Add secret"**

#### Секрет 4: DEPLOY_PATH
- **Name:** `DEPLOY_PATH`
- **Secret:** `/var/www/mack-user/data/www/mack_serv_production/`
- Нажмите **"Add secret"**

## Разница между Repository secrets и Environment secrets:

- **Repository secrets** - используются для всего репозитория (это нужно!)
- **Environment secrets** - используются только для конкретного окружения (production, staging и т.д.)

Наш workflow использует **Repository secrets**, поэтому добавляйте туда!

## Визуальная подсказка:

В Settings → Secrets and variables вы увидите:
- ✅ **Actions** - здесь нужно добавлять (Repository secrets)
- ❌ **Environments** - это НЕ то место (Environment secrets)

## После добавления всех секретов:

Проверьте, что у вас есть 4 секрета:
1. SSH_HOST
2. SSH_USER
3. SSH_PRIVATE_KEY
4. DEPLOY_PATH

Затем можно тестировать деплой!
