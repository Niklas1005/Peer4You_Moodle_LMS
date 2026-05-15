FROM php:8.3-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libxml2-dev \
        libxslt1-dev \
        libonig-dev \
        libldap2-dev \
        libsasl2-dev \
        libgmp-dev \
        libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        gmp \
        intl \
        ldap \
        mysqli \
        opcache \
        pdo_mysql \
        soap \
        xsl \
        zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/moodle.apache.conf /etc/apache2/sites-available/000-default.conf

RUN { \
      echo "memory_limit=512M"; \
      echo "max_execution_time=300"; \
      echo "max_input_vars=5000"; \
      echo "upload_max_filesize=128M"; \
      echo "post_max_size=128M"; \
      echo "opcache.enable=1"; \
      echo "zend.exception_ignore_args=On"; \
    } > /usr/local/etc/php/conf.d/moodle.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
