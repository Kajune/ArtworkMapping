docker build -t artwork_server docker/artwork_server
docker build -t artwork_db docker/artwork_db

docker volume create artwork_db_volume
docker run -d -v artwork_db_volume:/var/lib/mysql --name artwork_db artwork_db --default-authentication-plugin=mysql_native_password
docker run -d -p 15010:80 -v %CD%/src:/var/www/html --name artwork_server --link artwork_db:artwork_db -h artwork.local artwork_server
