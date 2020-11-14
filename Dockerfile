FROM php:7.4.12-cli-alpine3.12

ARG COMPOSER_INSTALLER_URL=https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer

RUN apk add --no-cache --virtual dependencies g++ make autoconf wget \
  && apk add --no-cache yaml-dev \
  && pecl channel-update pecl.php.net \
  && pecl install yaml-2.0.4 && docker-php-ext-enable yaml \
  && pecl install pcov-1.0.6 && docker-php-ext-enable pcov \
  && wget ${COMPOSER_INSTALLER_URL} -O - -q | php -- --install-dir=/bin --filename=composer --quiet \
  && apk del --purge dependencies

ENTRYPOINT ["/usr/local/bin/php"]
