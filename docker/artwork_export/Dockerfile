FROM ubuntu:18.04

RUN apt update
RUN apt -y install python3-dev python3-pip default-libmysqlclient-dev
RUN apt -y install libgl1-mesa-dev
RUN apt -y install libonig-dev zlib1g-dev libpng-dev libzip-dev libjpeg-dev libfreetype6-dev
RUN pip3 install --upgrade pip
RUN pip3 install openpyxl numpy opencv-python Pillow #Pillow is required for openpyxl
RUN pip3 install mysqlclient

RUN apt install -y busybox-static

WORKDIR /var/www/html
CMD ["busybox", "crond", "-l", "8", "-L", "/dev/stderr", "-f"]
