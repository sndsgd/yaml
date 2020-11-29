FROM php:7.4-cli-alpine3.12

ARG COMPOSER_PHAR_URL=https://github.com/composer/composer/releases/download/2.0.7/composer.phar

RUN apk add --no-cache --virtual dependencies g++ make autoconf wget \
  && apk add --no-cache yaml-dev \
  && pecl channel-update pecl.php.net \
  && pecl install yaml-2.0.4 && docker-php-ext-enable yaml \
  && pecl install pcov-1.0.6 && docker-php-ext-enable pcov \
  && wget -O /bin/composer ${COMPOSER_PHAR_URL} \
  && chmod +x /bin/composer \
  && apk del --purge dependencies

ENTRYPOINT ["/usr/local/bin/php"]
