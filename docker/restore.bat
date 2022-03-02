docker run --rm --volumes-from artwork_db -v %CD%:/backup busybox tar xvf /backup/backup.tar
docker restart artwork_db