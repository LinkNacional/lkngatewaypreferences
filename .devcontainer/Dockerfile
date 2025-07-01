FROM php:8.1.32-zts-alpine3.21

RUN apk add --no-cache \
  git \
  npm && \
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
