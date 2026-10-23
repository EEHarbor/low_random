#!/usr/bin/env bash
set -euo pipefail
script_dir="$(cd "$(dirname "$0")" && pwd)"
php_bin="${PHP_BIN:-/Users/tomjaeger/Library/Application Support/Herd/bin/php82}"
[[ -x "$php_bin" ]] || { echo "Missing explicit PHP binary: $php_bin" >&2; exit 2; }
expected="$(awk '$2 == "phpunit-9.phar" { print $1 }' "$script_dir/SHA256SUMS")"
actual="$(shasum -a 256 "$script_dir/phpunit-9.phar" | awk '{ print $1 }')"
[[ "$actual" = "$expected" ]] || { echo 'PHPUnit checksum mismatch; run tests/.phpunit/download.sh.' >&2; exit 2; }
exec "$php_bin" -d error_reporting=-1 -d display_errors=1 "$script_dir/phpunit-9.phar" "$@"
