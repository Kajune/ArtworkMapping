FROM php:7.4.8-apache

RUN apt update
RUN apt -y install python3-dev python3-pip default-libmysqlclient-dev
RUN apt -y install libgl1-mesa-dev
RUN apt -y install libonig-dev zlib1g-dev libpng-dev libzip-dev libjpeg-dev libfreetype6-dev
#RUN apt -y install libmcrypt-dev
RUN docker-php-ext-install mysqli pdo_mysql exif zip
RUN docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/
RUN docker-php-ext-install -j$(nproc) gd
RUN apt -y install sendmail
RUN pip3 install --upgrade pip
RUN pip3 install openpyxl numpy opencv-python Pillow #Pillow is required for openpyxl
RUN pip3 install mysqlclient

ADD php.ini /usr/local/etc/php
