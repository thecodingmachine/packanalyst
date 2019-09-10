FROM thecodingmachine/php:7.2-v1-apache-node6

COPY --chown=docker:docker . .

RUN composer install
