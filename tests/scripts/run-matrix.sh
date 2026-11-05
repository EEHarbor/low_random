#!/usr/bin/env bash
set -uo pipefail
root="$(cd "$(dirname "$0")/../.." && pwd)"
report_dir="${1:-$(mktemp -d "$root/tests/reports/matrix.XXXXXX")}"
mkdir -p "$report_dir"
if [[ -n "$(find "$report_dir" -mindepth 1 -maxdepth 1 -print -quit)" ]]; then echo 'Report directory must be empty.' >&2; exit 2; fi
[[ "$report_dir" = /* ]] || { echo 'Use an absolute report path.' >&2; exit 2; }
failed=0
for version in 74 80 81 82 83 84 85; do
    php_bin="/Users/tomjaeger/Library/Application Support/Herd/bin/php$version"
    "$php_bin" -v > "$report_dir/php$version.version.txt" 2>&1
    PHP_BIN="$php_bin" "$root/tests/.phpunit/run-phpunit.sh" -c "$root/phpunit.xml" --log-junit "$report_dir/php$version.junit.xml" > "$report_dir/php$version.log" 2>&1
    status=$?
    printf '%s %s\n' "$version" "$status" | tee -a "$report_dir/exit-codes.txt"
    if [[ "$status" -ne 0 ]]; then failed=1; fi
    if ! [[ -s "$report_dir/php$version.junit.xml" ]]; then failed=1; continue; fi
    "$php_bin" -r '$xml = simplexml_load_file($argv[1]); $count = (int) $xml->testsuite["tests"]; exit($count > 0 ? 0 : 1);' "$report_dir/php$version.junit.xml" || failed=1
done
printf 'Reports: %s\n' "$report_dir"
exit "$failed"
