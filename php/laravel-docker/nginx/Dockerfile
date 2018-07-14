FROM nginx:alpine

LABEL maintainer="cloty"

ENV site_conf /etc/nginx/sites-available/laravel.conf

# Add Scripts
ADD run.sh /run.sh

COPY nginx.conf /etc/nginx/

# If you're in China, or you need to change sources, will be set CHANGE_SOURCE to true in .env.

ARG CHANGE_SOURCE=true
RUN if [ ${CHANGE_SOURCE} = true ]; then \
    # Change application source from dl-cdn.alpinelinux.org to aliyun source
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/' /etc/apk/repositories \
;fi

RUN apk update \
    && apk upgrade \
    && apk add --no-cache bash \
    && adduser -D -H -u 1000 -s /bin/bash www-data

ARG PHP_UPSTREAM_CONTAINER=php-fpm
ARG PHP_UPSTREAM_PORT=9000

RUN echo "upstream php-upstream { server ${PHP_UPSTREAM_CONTAINER}:${PHP_UPSTREAM_PORT}; }" > /etc/nginx/conf.d/upstream.conf \
    && rm /etc/nginx/conf.d/default.conf

# Set upstream conf and remove the default conf
# RUN rm /etc/nginx/conf.d/default.conf

# Copy our nginx config
# nginx site conf
RUN mkdir -p /etc/nginx/sites-available/ && \
mkdir -p /etc/nginx/sites-enabled/ && \
mkdir -p /etc/nginx/ssl/

EXPOSE 80 443

CMD ["nginx"]

RUN chmod 755 /run.sh

# CMD ["/run.sh"]