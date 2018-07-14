FROM php:7.2.7-fpm-alpine

LABEL maintainer="Ric Harvey <ric@ngd.io>"

ENV fpm_conf /usr/local/etc/php-fpm.d/www.conf
ENV php_vars /usr/local/etc/php/conf.d/docker-vars.ini

# resolves #166
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

ARG CHANGE_SOURCE=true
RUN if [ ${CHANGE_SOURCE} = true ]; then \
    # Change application source from dl-cdn.alpinelinux.org to aliyun source
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/' /etc/apk/repositories \
;fi

RUN apk update \
    && apk upgrade \
    && apk add --no-cache bash

RUN mkdir -p /data/sites/laravel/

ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
RUN apk add --no-cache --repository http://dl-3.alpinelinux.org/alpine/edge/testing gnu-libiconv

RUN addgroup -S deploy \
  && adduser -D -S -h /var/cache/deploy -s /sbin/nologin -G deploy deploy \ 
  && apk add --no-cache --virtual .build-deps \
    autoconf \
    gcc \
    libc-dev \
    make \
    libressl-dev \
    pcre-dev \
    zlib-dev \
    linux-headers \
    curl \
    gnupg \
    libxslt-dev \
    gd-dev \
    geoip-dev \
    perl-dev \
    luajit-dev \
  && found='';
RUN apk add --no-cache \
    bash \
    openssh-client \
    wget \
    supervisor \
    curl \
    libcurl \
    git \
    python \
    python-dev \
    py-pip \
    augeas-dev \
    libressl-dev \
    ca-certificates \
    dialog \
    autoconf \
    make \
    gcc \
    musl-dev \
    linux-headers \
    libmcrypt-dev \
    libpng-dev \
    icu-dev \
    libpq \
    libxslt-dev \
    libffi-dev \
    freetype-dev \
    sqlite-dev \
    libjpeg-turbo-dev && \
    docker-php-ext-configure gd \
      --with-gd \
      --with-freetype-dir=/usr/include/ \
      --with-png-dir=/usr/include/ \
      --with-jpeg-dir=/usr/include/ && \
    #curl iconv session
    docker-php-ext-install iconv mysqli mbstring pdo pdo_mysql tokenizer xml pcntl pdo_sqlite gd exif intl xsl json soap dom zip opcache && \
    pecl install xdebug-2.6.0 && \
    docker-php-source delete && \
    mkdir -p /var/log/supervisor && \
    EXPECTED_COMPOSER_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig) && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '${EXPECTED_COMPOSER_SIGNATURE}') { echo 'Composer.phar Installer verified'; } else { echo 'Composer.phar Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"  && \
    pip install -U pip && \
    apk del gcc musl-dev linux-headers libffi-dev augeas-dev python-dev make autoconf
#    apk del .sys-deps

# tweak php-fpm config
RUN echo "cgi.fix_pathinfo=0" > ${php_vars} &&\
    echo "upload_max_filesize = 100M"  >> ${php_vars} &&\
    echo "post_max_size = 100M"  >> ${php_vars} &&\
    echo "variables_order = \"EGPCS\""  >> ${php_vars} && \
    echo "memory_limit = 128M"  >> ${php_vars} && \
    sed -i \
        -e "s/;catch_workers_output\s*=\s*yes/catch_workers_output = yes/g" \
        -e "s/pm.max_children = 5/pm.max_children = 4/g" \
        -e "s/pm.start_servers = 2/pm.start_servers = 3/g" \
        -e "s/pm.min_spare_servers = 1/pm.min_spare_servers = 2/g" \
        -e "s/pm.max_spare_servers = 3/pm.max_spare_servers = 4/g" \
        -e "s/;pm.max_requests = 500/pm.max_requests = 200/g" \
        -e "s/user = www-data/user = www-data/g" \
        -e "s/group = www-data/group = www-data/g" \
        -e "s/;listen.mode = 0660/listen.mode = 0755/g" \
        -e "s/;listen.owner = www-data/listen.owner = www-data/g" \
        -e "s/;listen.group = www-data/listen.group = www-data/g" \
        -e "s/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/g" \
        -e "s/^;clear_env = no$/clear_env = no/" \
        ${fpm_conf}
#    ln -s /etc/php7/php.ini /etc/php7/conf.d/php.ini && \
#    find /etc/php7/conf.d/ -name "*.ini" -exec sed -i -re 's/^(\s*)#(.*)/\1;\2/g' {} \;

# COPY ./laravel.ini /usr/local/etc/php/conf.d
# COPY ./xlaravel.pool.conf /usr/local/etc/php-fpm.d/

# supervisord
ADD conf/supervisord.conf /etc/supervisord.conf

WORKDIR "/data/sites/laravel"

EXPOSE 9000

CMD ["php-fpm"]
