
FROM php:8.2-apache


RUN apt-get update && apt-get install -y \
    procps \
    sudo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*


RUN a2enmod rewrite

COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

EXPOSE 80