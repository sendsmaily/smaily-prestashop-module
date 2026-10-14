#!/bin/sh
# Builds dist/smailyforprestashop.zip from the module in src/: the files git tracks there, with the
# production Composer dependencies, minus the paths listed in .zipignore. CI installs this zip and
# the release workflow publishes it, both through `composer package`.
set -eu

root=$(cd "$(dirname "$0")/.." && pwd)
module=smailyforprestashop
stage="$root/build/$module"
zip="$root/dist/$module.zip"

composer_run() {
    if [ -n "${COMPOSER_BINARY:-}" ]; then php "$COMPOSER_BINARY" "$@"; else composer "$@"; fi
}

rm -rf "$root/build" "$root/dist"
mkdir -p "$stage" "$root/dist"

# Tracked files only, as they are in the working tree. tar fails when a listed file is missing.
git -C "$root/src" ls-files -z > "$root/build/files"
tar -C "$root/src" --null -T "$root/build/files" -cf "$root/build/files.tar"
tar -C "$stage" -xf "$root/build/files.tar"

composer_run install --working-dir "$stage" --no-dev --no-interaction --no-progress --quiet

# Every pattern must remove something, so a mistyped or outdated pattern fails the build.
while IFS= read -r pattern || [ -n "$pattern" ]; do
    pattern=$(printf '%s' "$pattern" | tr -d '\r' | sed 's/[[:space:]]*$//')
    case "$pattern" in '' | '#'*) continue ;; esac
    matches=$(cd "$stage" && find . -path "./$pattern" -prune -print)
    if [ -z "$matches" ]; then
        echo ".zipignore: '$pattern' matches no file in the module" >&2
        exit 1
    fi
    (cd "$stage" && find . -path "./$pattern" -prune -exec rm -rf {} +)
done < "$root/.zipignore"

(cd "$root/build" && zip -qrX "$zip" "$module")
rm -rf "$root/build"
echo "built dist/$module.zip ($(du -h "$zip" | cut -f1))"
