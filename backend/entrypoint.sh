#!/bin/bash

echo "DB ${DB_HOST}"

# sleep while no database
STATUS=3
until [ "$STATUS" -eq "0" ]; do
    echo "STATUS: $STATUS"
    ping -c 1 db
    STATUS=$(echo $?)
    echo "Waiting for DB...$STATUS"
    sleep 1
done

STATUS=3
until [ "$STATUS" -eq "0" ]; do
    echo "STATUS: $STATUS"
    nc -zv db 3306
    STATUS=$(echo $?)
    echo "Check network...$STATUS"
    sleep 1
done

echo "DB IS OK!"

php /var/www/artisan storage:link
php /var/www/artisan config:cache
php /var/www/artisan migrate
php /var/www/artisan db:seed

php-fpm
