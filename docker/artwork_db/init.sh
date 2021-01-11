while ! mysqladmin ping -u artworkadmin -s; do
    sleep 1
done

sleep 5
mysql -u artworkadmin -p artwork < /tmp/localhost.sql
