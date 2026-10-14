#!/bin/sh
# Builds dist/smailyforprestashop.zip from the module in src/: the files git tracks there, with the
# production Composer dependencies, minus the paths listed in .zipignore. CI installs this zip and
# the release workflow publishes it, both through `composer package`.
set -eu

root=$(cd "$(dirname "$0")/.." && pwd)
module=smailyforprestashop
stage="$root/build/$module"
zip="$root/dist/$module.zip"
if [ -n "${COMPOSER_BINARY:-}" ]; then composer="php $COMPOSER_BINARY"; else composer=composer; fi

rm -rf "$root/build" "$root/dist"
mkdir -p "$stage" "$root/dist"

# Tracked files only, as they are in the working tree.
git -C "$root/src" ls-files -z | (cd "$root/src" && xargs -0 tar -cf - --) | tar -xf - -C "$stage"

$composer install --working-dir "$stage" --no-dev --no-interaction --no-progress --quiet

while IFS= read -r pattern || [ -n "$pattern" ]; do
    case "$pattern" in '' | '#'*) continue ;; esac
    (cd "$stage" && find . -path "./$pattern" -prune -exec rm -rf {} +)
done < "$root/.zipignore"

(cd "$root/build" && zip -qrX "$zip" "$module")
rm -rf "$root/build"
echo "built dist/$module.zip ($(du -h "$zip" | cut -f1))"
