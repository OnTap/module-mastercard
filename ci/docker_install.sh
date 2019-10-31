#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

export DEBIAN_FRONTEND=noninteractive

apt-get update -yqq
apt-get install -yqq apt-utils
apt-get install -yqq \
    git \
    zip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libxslt1-dev

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=/bin
php -r "unlink('composer-setup.php');"

docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

docker-php-ext-install \
    bcmath \
    gd \
    xsl
