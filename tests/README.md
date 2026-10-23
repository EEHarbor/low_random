# Low Random tests

These tests use the shipped plugin and its immutable PHAR. They intentionally remain red for confirmed source defects. This is an incomplete campaign checkpoint, not a passing release gate.

Run the pinned local PHPUnit 9.6.38 across every supported Herd runtime:

```sh
./tests/.phpunit/download.sh
./tests/scripts/run-matrix.sh
```

The runner executes PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 and 8.5 by their absolute Herd binaries; it changes no Herd runtime, service or php.ini. PHPUnit 9.6.38 is shared across the matrix to preserve one compatible test/configuration contract. PHPUnit 10.5.66 and 11.5.57 are also locally pinned for skill preflight compatibility. Their PHAR files are ignored, verified against `SHA256SUMS`, and downloaded from exact version URLs.

A focused explicit-runtime command is:

```sh
PHP_BIN='/Users/tomjaeger/Library/Application Support/Herd/bin/php74' ./tests/.phpunit/run-phpunit.sh -c phpunit.xml --filter PluginTest
```

Native child processes use `PHP_BINARY`, inherit strict E_ALL diagnostics and set unique `TMPDIR`/`sys_temp_dir`. Every shipped PHP file, each archive member and the PHAR stub is syntax-checked from a byte-identical, SHA256-verified temporary copy. Behavioral calls execute the original shipped source. The fallback loader executes the original PHAR directly. Test cleanup restores cache paths, globals, diagnostic handlers, include path and added autoloaders. Native probes use unique output files and a monotonic deadline. Licensing crypto runs in children to preserve the caller's OpenSSL error queue; updater method tests initialize valid post-constructor state while a separate native probe exercises its real constructor.

The EE template double follows `EE_Template::fetch_param()` in `/Users/tomjaeger/Sites/ee_repo_2/system/ee/legacy/libraries/Template.php:1901`, including its only ignored parameter (`url_title`), missing/null defaults and native switch comparisons. Numeric template parameter vectors use strings where normal EE tags do; the explicit integer-zero vector records EE's PHP 7.4 loose-comparison normalization. Database query methods match native DB_result empty arrays, SQL NULL, missing-column row fallback, numeric/current-row selection and requested row types; File model filters preserve the shipped `UploadDestination` relationship and builder calls. Form helpers record arguments to assert this view's responsibilities without claiming EE's escaping implementation was tested.

For genuine branch instrumentation, use a new absolute prefix on each run:

```sh
LOW_RANDOM_RAW_COVERAGE='/absolute/new/report-prefix' \
LOW_RANDOM_XDEBUG_EXTENSION='/Applications/Herd.app/Contents/Resources/xdebug/xdebug-82-arm64.so' \
'/Users/tomjaeger/Library/Application Support/Herd/bin/php82' \
  -d zend_extension=/Applications/Herd.app/Contents/Resources/xdebug/xdebug-82-arm64.so \
  -d xdebug.mode=coverage tests/.phpunit/phpunit-9.phar -c phpunit.xml
python3 tests/scripts/summarize-branches.py /absolute/new/report-prefix.PID.json
```

Each main/native process writes a separate report. Do not merge different runtimes, different conditional class graphs, or extracted/stub and stream-loader graphs. Clover is useful for executable line reporting through the skill helper, but its missing branch metrics never prove branch coverage. `COVERAGE.md` lists all 27 source units and all 262 function/graph rows, including uninstrumented sources.

The current incomplete phase 3 matrix is recorded in `MATRIX_RESULTS.json` (173 cases on every runtime). `STATUS.md` separates its pending review from both already published checkpoints. Source failures remain red. Run Clover separately from the raw collector because PHPUnit controls Xdebug during Clover generation; the raw collector is the source of branch/basic-block/exit evidence.

Navigation component native probes supply a normal optional add-on navigation extension in a unique installation; all library classes execute from the original PHAR using a component loader, while copied setup/JSON/autoloader bytes are hash verified. These tests cover the conduit API and do not claim package loader integration. Native system/application/installer fieldtype imports verify their compatible parent fixture copies before loading the actual archive code. Eighty raw runtime/context graphs are retained separately; LC_CTYPE is recorded for the qualified PHP7.4 UTF-8 normalization regression.

The current EE `db` service returns a fresh query builder. The fixture shares live backend results and a journal while separating pending calls and schema caches. Settings tests observe the owning builder and preserve an unfinished primary query/cache; no stale cache is inserted into a fresh builder just to cover a guard. This models the current consumed service contract and leaves historical/full SQL integration explicit.
