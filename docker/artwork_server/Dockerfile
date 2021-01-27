FROM php:7.4.8-apache

RUN apt update
RUN docker-php-ext-install mysqli pdo_mysql
RUN apt -y install python3-dev python3-pip default-libmysqlclient-dev
RUN apt -y install libgl1-mesa-dev
RUN pip3 install --upgrade pip
RUN pip3 install openpyxl numpy opencv-python Pillow #Pillow is required for openpyxl
RUN pip3 install mysqlclient

ENV MYSQL_HOST artwork_db
ENV EDITMODE_HASH 8e40815dd2fbb5f31f65318cca782eecd7f8584a04d368da436aeb7c5c150b87

ADD php.ini /usr/local/etc/php
