#!/bin/sh

# Wait for MySQL to start.
mysql_ready() {
    mysqladmin ping --host=db --user=db_user --password=db_password
}
while !(mysql_ready); do
    sleep 1
    echo "Waiting for MySQL to finish start up..."
done

# Link module files to OpenCart installation.
if [ ! -d ./.modman ]; then
    modman init
fi
modman link /var/www/html/smailyforprestashop

docker-php-entrypoint "$@"
