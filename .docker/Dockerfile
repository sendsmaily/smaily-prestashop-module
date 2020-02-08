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
