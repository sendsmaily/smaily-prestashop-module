#!/bin/sh
# Starts the shop from docker-compose.yaml, lets it install dist/smailyforprestashop.zip, and checks
# the result. Prints one "ok" or "FAIL" line per check and exits non-zero when any check failed.
#
#   PS_IMAGE=prestashop/prestashop-flashlight:9.2.0-alpine-nginx tests/ci/validate.sh
#
# Set KEEP=1 to leave the shop running at http://localhost:8000 (admin-dev, admin@prestashop.com /
# prestashop) for a look around.
set -u
cd "$(dirname "$0")"
COMPOSE="docker compose -f docker-compose.yaml"
URL="http://localhost:8000"
fails=0
tmp=$(mktemp -d)

report() {
    if [ "$1" -eq 0 ]; then echo "  ok   $2"; else echo "  FAIL $2"; fails=$((fails + 1)); fi
}
sql() {
    $COMPOSE exec -T mysql mariadb -uprestashop -pprestashop -N -s -e "$1" prestashop
}
teardown() {
    if [ "${KEEP:-}" = 1 ]; then echo "* Shop left running at $URL (KEEP=1)"; else $COMPOSE down -v >/dev/null 2>&1; fi
    rm -rf "$tmp"
}

echo "* Starting ${PS_IMAGE:-the default image}"
$COMPOSE up -d --quiet-pull || { echo "docker compose up failed"; teardown; exit 1; }

echo "* Waiting for the storefront (the shop installs the module before it starts serving)"
i=0
until curl -sfo /dev/null "$URL/"; do
    status=$(docker inspect -f '{{.State.Status}}' "$($COMPOSE ps -q prestashop)" 2>/dev/null)
    i=$((i + 1))
    if [ "$status" != "running" ] || [ $i -ge 90 ]; then
        echo "PrestaShop did not come up (container status: ${status:-gone}). Last log lines:"
        $COMPOSE logs --no-color prestashop | tail -60
        teardown
        exit 1
    fi
    sleep 5
done

echo "* Checking the installation"
expected=$(sed -n "s/.*\$this->version = '\([^']*\)'.*/\1/p" ../../src/smailyforprestashop.php)
installed=$(sql "SELECT version FROM ps_module WHERE name='smailyforprestashop' AND active=1")
[ "$installed" = "$expected" ]; report $? "module installed and active with version $expected (got '${installed:-none}')"

hooks=$(sql "SELECT COUNT(DISTINCT h.name) FROM ps_hook_module hm JOIN ps_hook h ON h.id_hook = hm.id_hook JOIN ps_module m ON m.id_module = hm.id_module WHERE m.name = 'smailyforprestashop' AND h.name IN ('actionCustomerAccountAdd', 'actionCustomerAccountUpdate', 'actionNewsletterRegistrationAfter')")
[ "$hooks" = 3 ]; report $? "the three hooks are registered (got ${hooks:-none})"

[ -n "$(sql "SHOW TABLES LIKE 'ps_smaily_cart'")" ]; report $? "the smaily_cart table exists"

$COMPOSE exec -T prestashop test -f /var/www/html/override/modules/ps_emailsubscription/ps_emailsubscription.php
report $? "the newsletter module override is in place"

echo "* Running the API client checks against the Symfony HTTP client this PrestaShop ships"
$COMPOSE exec -T prestashop sh -c 'cd /tests/api-client && (php -S 127.0.0.1:8765 mock.php >/dev/null 2>&1 &) && sleep 1 && AUTOLOAD=/var/www/html/vendor/autoload.php API_PHP=/var/www/html/modules/smailyforprestashop/lib/Api.php php test.php'
report $? "API client checks"

echo "* Checking the front controllers"
code=$(curl -s -o "$tmp/rss.xml" -w '%{http_code}' "$URL/index.php?fc=module&module=smailyforprestashop&controller=SmailyRssFeed")
[ "$code" = 200 ] && grep -q '<rss' "$tmp/rss.xml"; report $? "RSS feed answers 200 with an rss document (got $code)"

code=$(curl -s -o /dev/null -w '%{http_code}' "$URL/index.php?fc=module&module=smailyforprestashop&controller=SmailyCustomerCron&token=wrong")
[ "$code" = 200 ]; report $? "customer sync cron with a wrong token answers 200, not a server error (got $code)"

if [ $fails -gt 0 ]; then
    echo "* $fails check(s) failed. Last PrestaShop log lines:"
    $COMPOSE logs --no-color prestashop | tail -80
fi
teardown
[ $fails -eq 0 ]
