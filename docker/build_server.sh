docker build -t artwork_server docker/artwork_server
docker build -t artwork_db docker/artwork_db

docker volume create artwork_db_volume
docker run -d -v artwork_db_volume:/var/lib/mysql --name artwork_db artwork_db --default-authentication-plugin=mysql_native_password
docker run -d -p 8080:80 -v $PWD/src:/var/www/html --name artwork_server --link artwork_db:artwork_db artwork_server
chmod 777 $PWD/src/manage/tmp

