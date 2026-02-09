# Используем официальный образ PHP с предустановленным расширением FPM
FROM php:8.2-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    net-tools \
    iputils-ping \
    curl \
    netcat-openbsd


# Установка и включение расширений PHP, необходимых для Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка Node.js и NPM для фронтенда (если необходимо)
#RUN curl -sL https://deb.nodesource.com/setup_14.x | bash - && apt-get install -y nodejs

# Указываем рабочую директорию внутри контейнера
WORKDIR /var/www

# Копирование кода приложения в контейнер отключено, поскольку мы будем использовать том

# Команда для запуска PHP-FPM
#CMD ["php-fpm"]

# Открытие порта 9000 для соединений
EXPOSE 9000

ENTRYPOINT ["bash","./entrypoint.sh"]
