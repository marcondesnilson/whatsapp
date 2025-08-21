FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/

# Install system dependencies
RUN apt update && apt install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libpq-dev \
    libxml2-dev \
    zip \
    unzip \
    libmemcached-dev \
    libz-dev \
    libssl-dev \
    libssl-doc \
    libsasl2-dev \
    zlib1g-dev \
    libicu-dev \
    g++ \
    libldap2-dev \
    libbz2-dev \
    libcurl4-openssl-dev \
    libgmp-dev \
    re2c \
    libwebp-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libxpm-dev \
    libvpx-dev \
    libfreetype6-dev \
    libmagick++-dev \
    libmagickwand-dev \
    libgd-dev \
    libtidy-dev \
    libxslt1-dev \
    libmagic-dev \
    libexif-dev \
    file \
    sqlite3 \
    libsqlite3-dev \
    libxslt-dev \
    libkrb5-dev \
    libssh2-1-dev \
    poppler-utils \
    ghostscript \
    libmagickwand-7.q16-dev \
    libsnmp-dev \
    libedit-dev \
    libreadline-dev \
    libsodium-dev \
    libzip-dev \
    nodejs \
    npm

# Instale a extensão PECL 'excimer'
RUN pecl install excimer \
    && docker-php-ext-enable excimer

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite pdo_mysql
RUN docker-php-ext-install opcache
RUN docker-php-ext-install mbstring exif pcntl bcmath gd
RUN docker-php-ext-install dom
RUN docker-php-ext-install xml
RUN docker-php-ext-install zip
RUN docker-php-ext-install curl

# Timezone
RUN echo "date.timezone=America/Sao_Paulo" > /usr/local/etc/php/conf.d/timezone_luanda.ini
RUN echo "memory_limit = 1024M" > /usr/local/etc/php/conf.d/memory_limit.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install pm2
RUN npm install pm2 -g

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
WORKDIR /var/www/

# Instalar dependências do Composer
RUN cd "/var/www/" && if [ -f composer.json ]; then composer install; fi

# Change current user to www
USER root

# Expose port 9000 and start php-fpm server
EXPOSE 9000

# START
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh
CMD ["sh", "/usr/local/bin/start.sh"]
