#!/usr/bin/env bash
set -euo pipefail
script_dir="$(cd "$(dirname "$0")" && pwd)"
versions=(9.6.38 10.5.66 11.5.57)
for version in "${versions[@]}"; do
    major="${version%%.*}"
    destination="$script_dir/phpunit-$major.phar"
    expected="$(awk -v file="phpunit-$major.phar" '$2 == file { print $1 }' "$script_dir/SHA256SUMS")"
    if [[ -f "$destination" ]] && [[ "$(shasum -a 256 "$destination" | awk '{ print $1 }')" = "$expected" ]]; then continue; fi
    temporary="$(mktemp "$script_dir/download.XXXXXX")"
    trap 'rm -f "$temporary"' EXIT
    curl --fail --location --proto '=https' --tlsv1.2 "https://phar.phpunit.de/phpunit-$version.phar" --output "$temporary"
    actual="$(shasum -a 256 "$temporary" | awk '{ print $1 }')"
    [[ "$actual" = "$expected" ]] || { echo "Checksum mismatch for PHPUnit $version" >&2; exit 2; }
    mv "$temporary" "$destination"
    trap - EXIT
done
