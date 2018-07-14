# Laravel Docker

> git clone https://github.com/clotyxf/learningflow.git

> cd laravel-docker

> cp env-example .env && vim .env

example:
```
APP_CODE_PATH_HOST= <your sites path>
```

> docker-compose build php-fpm

> docker-compose build nginx

>docker-compose build redis

> docker-compose up -d php-fom nginx redis