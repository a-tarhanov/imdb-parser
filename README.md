# IMDB Parser

## Clone Repo

```bash
git clone https://github.com/a-tarhanov/imdb-parser.git
cd imdb-parser
```

## Build & Run

Copy .env.example to .env

Copy docker/mysql/init/createdb.sql.example to docker/mysql/init/createdb.sql

```bash
docker-compose up --build -d
```

## Install App

Copy app/.env.example to app/.env

```bash
docker-compose exec php bash

# laravel 
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate
```

## Update App

```bash
docker-compose exec php bash

composer install
php artisan migrate
```

Navigate to [http://localhost:80](http://localhost:80)
