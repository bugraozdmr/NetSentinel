FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod rewrite

RUN apt-get update && \
    apt-get install -y iputils-ping default-mysql-client && \
    rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

ENV APACHE_DOCUMENT_ROOT /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
