#!/bin/sh

# Wait for MySQL to start.
mysql_ready() {
    mysqladmin ping --host=db --user=db_user --password=db_password
}
while !(mysql_ready); do
    sleep 1
    echo "Waiting for MySQL to finish start up..."
done

# Link module files to Prestashop.
if [ ! -d ./.modman ]; then
    su www-data -s /bin/bash -c "modman init"
fi
su www-data -s /bin/bash -c "modman link /smailyforprestashop"

docker-php-entrypoint "$@"
