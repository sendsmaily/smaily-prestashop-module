#!/bin/sh

echo "Adding MailHog config.."
/usr/local/bin/php /var/www/html/bin/console prestashop:config set PS_MAIL_SERVER --value mailhog
/usr/local/bin/php /var/www/html/bin/console prestashop:config set PS_MAIL_SMTP_PORT --value 1025
/usr/local/bin/php /var/www/html/bin/console prestashop:config set PS_MAIL_METHOD --value 2
