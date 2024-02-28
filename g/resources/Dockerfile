FROM composer:lts as build

WORKDIR /app

COPY composer.json composer.lock /app/

RUN composer install --ignore-platform-reqs --no-interaction --no-plugins --no-scripts --prefer-dist

FROM php:8.1.0-alpine

WORKDIR /app

COPY . /app/

COPY --from=build /app/vendor /app/vendor

RUN cp .env.example .env

RUN php artisan key:generate

RUN php artisan migrate

RUN apk add --no-cache npm
RUN npm install

RUN npm run dev

EXPOSE 80

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]