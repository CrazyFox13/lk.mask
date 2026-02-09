# Dockerfile
FROM node:18-alpine as pre-build

# create destination directory
RUN mkdir -p /app
WORKDIR /app

# update and install dependency
RUN apk update && apk upgrade
#RUN apk add git
COPY package.json /app
COPY package-lock.json /app

RUN npm install
RUN npm install --ignore-scripts=false --foreground-scripts --verbose sharp
RUN npm install --platform=linuxmusl --arch=x64 sharp

FROM pre-build as local

VOLUME /app/.nuxt
VOLUME /app/.output

ENV HOST=0.0.0.0
EXPOSE 24678
EXPOSE 3000

CMD ["npm","run","dev"]

FROM pre-build as build-stage

COPY . /app

RUN npm run build

# Использование сборки для производственной стадии
FROM node:18-alpine as production

# Создание директории приложения
WORKDIR /app

# Копирование зависимостей и сборочных файлов из предыдущего этапа
COPY --from=build-stage /app /app

VOLUME /app/.nuxt
VOLUME /app/.output

# Установка зависимостей для production
# (может быть опущено, если все зависимости уже установлены в предыдущих слоях)

EXPOSE 3000

ENV HOST=0.0.0.0
ENV PORT=3000

# Запуск сборки Nuxt
CMD [ "npm", "start" ]