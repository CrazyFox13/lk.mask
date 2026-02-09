# Исправление ошибки "ssh: no key found"

## Проблема

Ошибка `ssh: no key found` означает, что SSH ключ в GitHub Secrets имеет неправильный формат или содержит лишние символы.

## Решение: Проверьте формат ключа в GitHub Secrets

### Шаг 1: Удалите старый секрет SSH_PRIVATE_KEY

1. Перейдите: https://github.com/CrazyFox13/lk.mask/settings/secrets/actions
2. Найдите секрет `SSH_PRIVATE_KEY`
3. Нажмите на иконку корзины (Delete)
4. Подтвердите удаление

### Шаг 2: Получите правильный ключ с сервера

На сервере выполните:

```bash
# Подключитесь к серверу
ssh mack-user@79.174.12.78

# Покажите ключ
cat ~/.ssh/github_actions_deploy
```

### Шаг 3: Скопируйте ключ БЕЗ лишних пробелов

Ключ должен выглядеть **точно так** (без пробелов в начале/конце строк):

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAJiPrUfFj61HxQAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAEDSzwDe9ldrkaxW0xQY7TymActq8eqjx9ADI59Q8EY55D/Ld44EqStBmYmvK+RBsRt0OHXHyaXM15UuRTqZigmpcAAAAFWdpdGh1Yi1hY3Rpb25zLWRlcGxveQ==
-----END OPENSSH PRIVATE KEY-----
```

### Шаг 4: Добавьте ключ заново в GitHub Secrets

1. Перейдите: https://github.com/CrazyFox13/lk.mask/settings/secrets/actions
2. Нажмите **"New repository secret"**
3. **Name:** `SSH_PRIVATE_KEY`
4. **Secret:** Вставьте ключ из шага 3 (скопируйте ВЕСЬ ключ целиком)
5. **Важно:** 
   - НЕ добавляйте пробелы в начале или конце
   - НЕ добавляйте `SSH_PRIVATE_KEY =` перед ключом
   - Вставляйте только сам ключ (с BEGIN и END строками)
6. Нажмите **"Add secret"**

### Шаг 5: Проверьте формат ключа

После добавления убедитесь, что:
- Первая строка: `-----BEGIN OPENSSH PRIVATE KEY-----` (без пробелов)
- Последняя строка: `-----END OPENSSH PRIVATE KEY-----` (без пробелов)
- Между ними одна строка с base64 данными

## Альтернативное решение: Использование GitHub CLI

Если веб-интерфейс продолжает иметь проблемы, используйте GitHub CLI:

```bash
# Установите GitHub CLI (если еще не установлен)
# Windows: winget install GitHub.cli

# Авторизуйтесь
gh auth login

# Получите ключ с сервера и сохраните в файл
# На сервере:
cat ~/.ssh/github_actions_deploy > /tmp/key.txt

# Скопируйте файл на локальный компьютер, затем:
cat key.txt | gh secret set SSH_PRIVATE_KEY --repo CrazyFox13/lk.mask
```

## Проверка после исправления

После обновления секрета:

1. Перейдите: https://github.com/CrazyFox13/lk.mask/actions
2. Запустите workflow вручную: **Run workflow**
3. Проверьте логи - ошибка `ssh: no key found` должна исчезнуть

## Если проблема сохраняется

Проверьте на сервере:

```bash
# Убедитесь, что публичный ключ добавлен
grep "github-actions-deploy" ~/.ssh/authorized_keys

# Если ключа нет, добавьте:
cat ~/.ssh/github_actions_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```
