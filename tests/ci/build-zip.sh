#!/bin/sh
# Builds dist/smailyforprestashop.zip the way the release workflow does: production
# dependencies only, development files left out.
set -eu
cd "$(dirname "$0")/../.."
rm -rf build dist
mkdir -p build dist
cp -r src build/smailyforprestashop
rm -rf build/smailyforprestashop/vendor
composer install --no-dev --no-interaction --no-progress --quiet --working-dir build/smailyforprestashop
(cd build && zip -qr ../dist/smailyforprestashop.zip smailyforprestashop \
    -x 'smailyforprestashop/.git*' 'smailyforprestashop/.php-cs-fixer.dist.php' 'smailyforprestashop/phpstan.neon')
echo "built dist/smailyforprestashop.zip ($(du -h dist/smailyforprestashop.zip | cut -f1))"
