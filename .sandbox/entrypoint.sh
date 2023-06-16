#!/bin/sh

# Wait for MySQL to start.
mysql_ready() {
    mysqladmin ping --host=$DB_SERVER --user=$DB_USER --password=$DB_PASSWD > /dev/null 2>&1
}
while !(mysql_ready); do
    sleep 1
    echo "Waiting for MySQL to finish start up..."
done

# Install PrestaShop if not already.
if [ -d ./install ]; then
    echo "Installing PrestaShop. This can take a while..."

    su www-data -s /bin/bash -c "cd install; php index_cli.php \
        --timezone='Europe/Tallinn' \
        --domain='$PS_DOMAIN' \
        --db_server=$DB_SERVER \
        --db_user=$DB_USER \
        --db_password=$DB_PASSWD \
        --email='admin@smaily.sandbox' \
        --password='smailydev1'\
        --newsletter=0"

    # Run post installation steps required by PrestaShop.
    rm -fR /var/www/html/install
    mv /var/www/html/admin /var/www/html/admin1
fi

docker-php-entrypoint "$@"
