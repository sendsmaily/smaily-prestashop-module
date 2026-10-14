#!/bin/sh
# Starts the shop from docker-compose.yaml, lets it install dist/smailyforprestashop.zip, and checks
# the result. Prints one "ok" or "FAIL" line per check and exits non-zero when any check failed.
#
#   PS_IMAGE=prestashop/prestashop-flashlight:9.2.0-nginx tests/ci/validate.sh
#
# PS_COMPOSE picks the shop image family: docker-compose.yaml (flashlight, the default) or
# docker-compose.official.yaml (the official image, which installs the shop first and takes longer).
#
# Set KEEP=1 to leave the shop running at http://localhost:8000 (admin-dev, admin@prestashop.com /
# prestashop) for a look around.
set -u
cd "$(dirname "$0")"
COMPOSE="docker compose -f ${PS_COMPOSE:-docker-compose.yaml}"
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
    status=$(docker inspect -f '{{.State.Status}}' "$($COMPOSE ps -aq prestashop)" 2>/dev/null)
    i=$((i + 1))
    if [ "$status" != "running" ] || [ $i -ge 150 ]; then
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

echo "* Sending one API client request through the Symfony HTTP client this PrestaShop ships"
$COMPOSE exec -T prestashop php /tests/api-client-smoke.php
report $? "API client smoke check"

echo "* Checking the front controllers"
code=$(curl -s -o "$tmp/rss.xml" -w '%{http_code}' "$URL/index.php?fc=module&module=smailyforprestashop&controller=SmailyRssFeed")
[ "$code" = 200 ] && grep -q '<rss' "$tmp/rss.xml"; report $? "RSS feed answers 200 with an rss document (got $code)"

check_cron() { # $1 controller, $2 configuration key of its token, $3 reply once the token is right and the sync is off
    body=$(curl -s "$URL/index.php?fc=module&module=smailyforprestashop&controller=$1&token=wrong")
    [ "$body" = 'Access denied!' ]; report $? "$1 refuses a wrong token (got '$body')"
    token=$(sql "SELECT value FROM ps_configuration WHERE name = '$2'")
    body=$(curl -s "$URL/index.php?fc=module&module=smailyforprestashop&controller=$1&token=$token")
    [ "$body" = "$3" ]; report $? "$1 accepts its token and reports the sync is off (got '$body')"
}
check_cron SmailyCustomerCron SMAILY_CUSTOMER_CRON_TOKEN 'User synchronization disabled!'
check_cron SmailyCartCron SMAILY_CART_CRON_TOKEN 'Abandoned cart disabled!'

if [ $fails -gt 0 ]; then
    echo "* $fails check(s) failed. Last PrestaShop log lines:"
    $COMPOSE logs --no-color prestashop | tail -80
fi
teardown
[ $fails -eq 0 ]
