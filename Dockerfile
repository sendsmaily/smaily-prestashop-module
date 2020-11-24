FROM prestashop/prestashop:1.7-7.2-apache

WORKDIR /var/www/html

ADD .sandbox/entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
