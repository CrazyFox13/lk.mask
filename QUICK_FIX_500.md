# Быстрое исправление ошибки 500

## Выполните на сервере:

```bash
# 1. Подключитесь к серверу
ssh mack-user@79.174.12.78
# Пароль: xR2vY9vE4m

# 2. Перейдите в директорию проекта
cd /var/www/mack-user/data/www/mack_serv_production/

# 3. Получите скрипт исправления
git pull origin main
bash fix-500-error.sh
```

## Или выполните команды вручную:

```bash
cd /var/www/mack-user/data/www/mack_serv_production/backend

# 1. Проверьте .env файл
cat .env | grep -E "APP_KEY|DB_|APP_URL"

# 2. Если APP_KEY пустой, сгенерируйте его
docker-compose exec backend php artisan key:generate --force

# 3. Очистите кеш
docker-compose exec backend php artisan config:clear
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan route:clear
docker-compose exec backend php artisan view:clear

# 4. Пересоздайте кеш
docker-compose exec backend php artisan config:cache
docker-compose exec backend php artisan route:cache
docker-compose exec backend php artisan view:cache

# 5. Проверьте права доступа
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 6. Проверьте логи
tail -50 storage/logs/laravel.log

# 7. Перезапустите контейнеры
cd ..
docker-compose restart
```

## Частые причины ошибки 500:

1. **APP_KEY не установлен** - выполните `php artisan key:generate`
2. **Проблемы с подключением к БД** - проверьте DB_HOST (может быть нужно `localhost` вместо `db`)
3. **Проблемы с правами доступа** - выполните `chmod -R 775 storage bootstrap/cache`
4. **Ошибки в коде** - проверьте логи `storage/logs/laravel.log`
5. **Проблемы с кешем** - очистите весь кеш

## Проверка подключения к БД:

```bash
docker-compose exec backend php artisan tinker
# В tinker выполните:
DB::connection()->getPdo();
# Если ошибка - проверьте DB_HOST в .env
```

## Если БД на хосте (не в Docker):

Измените в `backend/.env`:
```
DB_HOST=localhost  # вместо db
```

Затем перезапустите:
```bash
docker-compose restart backend
docker-compose exec backend php artisan config:cache
```
