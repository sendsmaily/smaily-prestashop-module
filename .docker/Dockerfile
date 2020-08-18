FROM prestashop/prestashop:1.7-7.2-apache

# Set up build arguments.
ARG user_id
ARG group_id
ARG username
ARG group

# Set up local user.
RUN if grep -q ${group} /etc/group; then \
       groupmod -g ${group_id} ${group}; \
    else \
       groupadd -f -g ${group_id} ${group}; \
    fi \
    && useradd -m -u ${user_id} -g ${group_id} ${username} -s /bin/bash

# Add local www-data group to the user
RUN usermod -a -G www-data ${username}

RUN mkdir /var/www/html/modules/smailyforprestashop
WORKDIR /var/www/html/modules/smailyforprestashop
RUN mkdir controllers \
    && mkdir controllers/admin \
    && mkdir controllers/front \
    && mkdir translations \
    && mkdir upgrade \
    && mkdir views \
    && mkdir views/js \
    && mkdir views/templates \
    && mkdir views/templates/admin \
    && mkdir views/templates/hook

RUN chown -R www-data:www-data /var/www/html/modules/ \
    && find /var/www/html/modules/ -type d -exec chmod 2775 {} \+ \
    && find /var/www/html/modules/ -type f -exec chmod 664 {} \+

# Modman is used for symlinking plugin files to modules folder
RUN curl -SL https://raw.githubusercontent.com/colinmollenhour/modman/master/modman -o /usr/local/bin/modman \
    && chmod +x /usr/local/bin/modman

ADD ./entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
