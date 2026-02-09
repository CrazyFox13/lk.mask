# Настройка production окружения

## Изменения для production сервера

### База данных
- **База данных:** `lk_mack`
- **Пользователь:** `mack-bd`
- **Пароль:** `uN3kU4aZ9k`
- **Хост:** `db` (внутри Docker) или `localhost` (если БД на хосте)

### Домен
- **Production URL:** `https://lk.mack-group.ru`
- **Директория проекта:** `/var/www/mack-user/data/www/mack_serv_production/`
- **Public директория:** `/var/www/mack-user/data/www/mack_serv_production/backend/public`

## Что было изменено:

1. **backend/.env.production** - создан файл с production настройками
2. **docker-compose.yaml** - обновлены настройки БД по умолчанию
3. **docker-compose/nginx/nginx.conf** - добавлен server_name для домена
4. **.github/workflows/deploy-ssh.yml** - обновлен для автоматической настройки .env на сервере

## Настройка на сервере

После деплоя на сервере нужно:

1. **Проверить .env файл:**
   ```bash
   cd /var/www/mack-user/data/www/mack_serv_production/backend
   cat .env | grep -E "DB_|APP_"
   ```

2. **Если БД находится на хосте (не в Docker), измените DB_HOST:**
   ```bash
   # В .env файле измените:
   DB_HOST=localhost  # вместо db
   ```

3. **Проверить подключение к БД:**
   ```bash
   docker-compose exec backend php artisan tinker
   # Затем в tinker:
   DB::connection()->getPdo();
   ```

4. **Настроить nginx на хосте (если используется):**
   ```nginx
   server {
       listen 80;
       server_name lk.mack-group.ru;
       
       root /var/www/mack-user/data/www/mack_serv_production/backend/public;
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

5. **Настроить SSL сертификат (Let's Encrypt):**
   ```bash
   sudo certbot --nginx -d lk.mack-group.ru
   ```

## Проверка после деплоя

1. Проверьте доступность сайта: https://lk.mack-group.ru
2. Проверьте API: https://lk.mack-group.ru/api
3. Проверьте админку: https://lk.mack-group.ru/admin
4. Проверьте логи:
   ```bash
   docker-compose logs backend
   docker-compose logs nginx
   ```

## Важные замечания

- ⚠️ Убедитесь, что на сервере настроен DNS для домена `lk.mack-group.ru`
- ⚠️ Если БД находится на хосте, измените `DB_HOST=db` на `DB_HOST=localhost` в .env
- ⚠️ Убедитесь, что порты 80 и 443 открыты в файрволе
- ⚠️ Настройте SSL сертификат для HTTPS
