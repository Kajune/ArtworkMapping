FROM mysql/mysql-server:latest

ENV MYSQL_ROOT_PASSWORD akarichankawaii
ENV MYSQL_USER artworkadmin
ENV MYSQL_DATABASE artwork
ENV MYSQL_PASSWORD akagisannkawaii

#RUN apt update
#RUN apt install -y mysql-client

ADD localhost.sql /tmp
ADD init.sh /tmp
