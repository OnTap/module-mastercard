FROM php:7.3-cli-stretch

WORKDIR /root/app

RUN apt-get update && apt-get install -y \
    git \
    zip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libxslt1-dev


RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --install-dir=/bin
RUN php -r "unlink('composer-setup.php');"

#RUN docker-php-ext-enable sodium
#RUN docker-php-ext-enable gd

RUN docker-php-ext-configure \
  gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    gd \
    xsl



COPY . .

ARG CI_COMPOSER_AUTH={}
ENV COMPOSER_AUTH=$CI_COMPOSER_AUTH

RUN COMPOSER_ALLOW_SUPERUSER=1 make test
