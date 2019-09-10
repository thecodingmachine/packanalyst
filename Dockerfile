FROM thecodingmachine/php:7.2-v2-apache-node8

ENV PHP_EXTENSION_MONGODB=1

COPY --chown=docker:docker . .

RUN composer install
