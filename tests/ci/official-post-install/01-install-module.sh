#!/bin/sh
# Runs inside the official image after the shop is installed: installs dist/smailyforprestashop.zip
# the way flashlight's INSTALL_MODULES_DIR does.
set -eu
unzip -qq -o /ps-modules/smailyforprestashop.zip -d /var/www/html/modules
chown -R www-data:www-data /var/www/html/modules/smailyforprestashop
runuser -g www-data -u www-data -- php -d memory_limit=-1 /var/www/html/bin/console prestashop:module --no-interaction install smailyforprestashop
