FROM php:7.4.8-apache

RUN apt update
RUN docker-php-ext-install mysqli pdo_mysql
RUN apt -y install python3-dev python3-pip default-libmysqlclient-dev
RUN apt -y install libsm6 libxrender1 libxext-dev
RUN pip3 install openpyxl numpy opencv-python pillow
RUN pip3 install mysqlclient
