# Low Random tests

The configured suite covers ordinary add-on behavior using finite EE collaborators and the original add-on source. The shipped PHAR remains available for normal inheritance and loading. Shared Foundation classes, archive/loader/compilation coverage, synthetic fixture/runner contracts and impossible caller states are excluded; Foundation owns its own tests.

Download the pinned PHPUnit 9.6.38 runner with `tests/.phpunit/download.sh`, then run an explicit Herd binary directly:

```sh
'/Users/tomjaeger/Library/Application Support/Herd/bin/php74' \
  -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 \
  tests/.phpunit/phpunit-9.phar -c phpunit.xml
```

Use the same command with php80, php81, php82, php83, php84 and php85. No coverage or archive inspection is needed. The direct-access guard uses a finite isolated PHP child.

The practical cleanup was checked on all seven runtimes on 2026-10-27: 17 tests and 57 assertions each. All pass with no captured diagnostics. Retained behavior covers item/tagdata selection, ranges, directory and upload-destination filters, empty or invalid string inputs, add-on metadata and its loose license view.

The removed repeated-instance and null-tagdata regressions bypassed EE Template.php, which creates one plugin instance per tag and supplies string tagdata. Non-string injected template parameters and numeric-method letter/float vectors were also removed.

Historical coverage/status/evidence files describe earlier campaign snapshots and are retained as history. Their removed collector scripts and prior test counts do not describe the current configured suite.
