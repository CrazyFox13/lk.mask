# Решение проблемы с добавлением SSH ключа в GitHub Secrets

## Проблема: GitHub не принимает SSH ключ

GitHub Secrets может иметь проблемы с многострочными значениями. Вот несколько решений:

## Решение 1: Правильный формат ключа (без лишних пробелов)

Убедитесь, что ключ скопирован **точно так**:

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAJiPrUfFj61HxQAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAEDSzwDe9ldrkaxW0xQY7TymActq8eqjx9ADI59Q8EY55D/Ld44EqStBmYmvK+RBsRt0OHXHyaXM15UuRTqZigmpcAAAAFWdpdGh1Yi1hY3Rpb25zLWRlcGxveQ==
-----END OPENSSH PRIVATE KEY-----
```

**Важно:**
- Нет пробелов в начале строк
- Нет лишних пробелов в конце строк
- Переносы строк должны быть правильными

## Решение 2: Использование GitHub CLI (если веб-интерфейс не работает)

Если веб-интерфейс не работает, используйте GitHub CLI:

```bash
# Установите GitHub CLI если еще не установлен
# Windows: winget install GitHub.cli
# Mac: brew install gh
# Linux: sudo apt install gh

# Авторизуйтесь
gh auth login

# Добавьте секреты через CLI
gh secret set SSH_HOST --body "79.174.12.78" --repo CrazyFox13/lk.mask
gh secret set SSH_USER --body "mack-user" --repo CrazyFox13/lk.mask
gh secret set DEPLOY_PATH --body "/var/www/mack-user/data/www/mack_serv_production/" --repo CrazyFox13/lk.mask

# Для SSH ключа сохраните его в файл и используйте:
cat > /tmp/ssh_key.txt << 'EOF'
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAJiPrUfFj61HxQAAAAtzc2gtZWQyNTUxOQAAACA/y3eOBKkrQZmJryvkQbEbdB1x8mlzNeVLkU6mYoJqXAAAAEDSzwDe9ldrkaxW0xQY7TymActq8eqjx9ADI59Q8EY55D/Ld44EqStBmYmvK+RBsRt0OHXHyaXM15UuRTqZigmpcAAAAFWdpdGh1Yi1hY3Rpb25zLWRlcGxveQ==
-----END OPENSSH PRIVATE KEY-----
EOF

gh secret set SSH_PRIVATE_KEY < /tmp/ssh_key.txt --repo CrazyFox13/lk.mask
```

## Решение 3: Проверка формата ключа на сервере

На сервере выполните:

```bash
# Проверьте формат ключа
cat ~/.ssh/github_actions_deploy

# Убедитесь, что ключ начинается и заканчивается правильно
head -1 ~/.ssh/github_actions_deploy
tail -1 ~/.ssh/github_actions_deploy

# Должно быть:
# -----BEGIN OPENSSH PRIVATE KEY-----
# -----END OPENSSH PRIVATE KEY-----
```

## Решение 4: Альтернативный способ - использование base64

Если GitHub не принимает многострочный ключ, можно закодировать его в base64:

```bash
# На сервере закодируйте ключ в base64
cat ~/.ssh/github_actions_deploy | base64 -w 0

# Скопируйте результат и добавьте в GitHub Secrets как SSH_PRIVATE_KEY_B64
# Затем в workflow нужно будет декодировать:
# echo "${{ secrets.SSH_PRIVATE_KEY_B64 }}" | base64 -d > /tmp/key && chmod 600 /tmp/key
```

Но это сложнее, лучше использовать правильный формат.

## Решение 5: Проверка через API GitHub

Можно добавить секрет через GitHub API:

```bash
# Создайте токен доступа: https://github.com/settings/tokens
# Нужны права: repo, admin:repo

GITHUB_TOKEN="ваш_токен"
REPO="CrazyFox13/lk.mask"
SECRET_NAME="SSH_PRIVATE_KEY"
SECRET_VALUE=$(cat ~/.ssh/github_actions_deploy | base64)

curl -X PUT \
  -H "Authorization: token $GITHUB_TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  "https://api.github.com/repos/$REPO/actions/secrets/$SECRET_NAME" \
  -d "{\"encrypted_value\":\"$SECRET_VALUE\",\"key_id\":\"...\"}"
```

Но для этого нужен публичный ключ репозитория, что сложнее.

## Самое простое решение

Попробуйте скопировать ключ **построчно**:

1. Откройте файл ключа на сервере: `cat ~/.ssh/github_actions_deploy`
2. Скопируйте **каждую строку отдельно** и вставьте в GitHub Secrets
3. Убедитесь, что нет лишних пробелов в начале или конце

Или используйте GitHub CLI (Решение 2) - это самый надежный способ.
