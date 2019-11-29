FROM thecodingmachine/php:7.2-v2-apache-node8

ENV PHP_EXTENSION_MONGODB=1
ENV PHP_INI_MEMORY_LIMIT=512M

ENV CRON_USER_1=docker
ENV CRON_SCHEDULE_1="*/5 * * * *"
ENV CRON_COMMAND_1="cd /var/www/html;ulimit -S -s 131072;./console.php run -v"

ENV CRON_USER_2=docker
ENV CRON_SCHEDULE_2="0 5 * * *"
ENV CRON_COMMAND_2="cd /var/www/html;./console.php get-scores -v"

# Delete files older than 30 days
ENV CRON_USER_3=docker
ENV CRON_SCHEDULE_3="23 0 * * *"
ENV CRON_COMMAND_3="find /var/downloads/ -maxdepth 2 -type d -mtime +30 | xargs rm -rf"

COPY --chown=docker:docker . .

RUN composer install
RUN cd src/views/ && npm install
