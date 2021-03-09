FROM prestashop/prestashop:1.7.6

# Install PHP CodeSniffer.
RUN curl -L https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar > /tmp/phpcs.phar \
    && mv /tmp/phpcs.phar /usr/local/bin/phpcs \
    && chmod +x /usr/local/bin/phpcs

ADD ./.sandbox/entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
