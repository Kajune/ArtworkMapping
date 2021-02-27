FROM php:7.4.8-apache

RUN apt update
RUN docker-php-ext-install mysqli pdo_mysql
RUN apt -y install python3-dev python3-pip default-libmysqlclient-dev
RUN apt -y install libgl1-mesa-dev
RUN apt -y install libzip-dev zlib1g-dev && docker-php-ext-install zip
RUN apt -y install sendmail
RUN pip3 install --upgrade pip
RUN pip3 install openpyxl numpy opencv-python Pillow #Pillow is required for openpyxl
RUN pip3 install mysqlclient

ADD php.ini /usr/local/etc/php
