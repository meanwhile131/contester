FROM php:8.4-fpm
RUN apt-get update && apt-get install -y libpq-dev python3 \
&& docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
&& docker-php-ext-install pgsql
CMD ["php-fpm"]