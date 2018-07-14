FROM node:alpine

# Create app directory
RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

ARG CHANGE_SOURCE=true
RUN if [ ${CHANGE_SOURCE} = true ]; then \
    # Change application source from dl-cdn.alpinelinux.org to aliyun source
    sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/' /etc/apk/repositories \
;fi

# Install app dependencies
COPY package.json /usr/src/app/

RUN apk add --update \
    python \
    python-dev \
    py-pip \
    build-base

RUN npm install

# Bundle app source
COPY laravel-echo-server.json /usr/src/app/laravel-echo-server.json

EXPOSE 3000
CMD [ "npm", "start" ]
