# Add-on PHP compatibility scope

Foundation changes are excluded from this candidate. The required distributed PHAR is restored byte-for-byte to original tests baseline `29a34def6c8466b485e29fd13e87208fde0221b5`, which has the same production bytes as locally held develop `892b6dae8282b159e580e3c1a6076a39d9902f84`. The generated autoloader is restored too. No shared library is rebuilt, replaced, patched or moved into a loose add-on helper.

The retained add-on source fixes cover: String-compatible item/separator arguments, one-time separator conversion, empty-separator validation, NULL tagdata and per-file invocation state.

Configured tests now target add-on behavior and its test tools. Dedicated Foundation suites and direct Foundation methods in mixed suites are removed, including archive-member/stub compilation inputs and the archive license-view iteration. All add-on plugin cases, the loose license view and normal add-on loading through the real PHAR remain selected. Full source inventories and identity checks remain; native lint executes loose add-on PHP only. Test history remains in Git. Historical pre-cleanup matrices, published checkpoints, native faults and source snapshots are preserved under the campaign evidence and Git history; they do not validate this candidate.

Run the complete configured suite with an explicit Herd binary:

```sh
PHP_BIN='/Users/tomjaeger/Library/Application Support/Herd/bin/php74' ./tests/.phpunit/run-phpunit.sh -c phpunit.xml --do-not-cache-result
```

Repeat for php80, php81, php82, php83, php84 and php85. The checked runner retains strict startup settings, prethrow diagnostics, complete process streams and actual native outcomes. Caught or ignored diagnostics remain rejection conditions. Generic authority controls use the add-on metadata entry point rather than relying on a changed Foundation cache. Deliberate authority faults are tool controls, never clean product compatibility credit.

Strict startup binds the checked native capture directory and its filesystem identity before discovery and providers. Caller environment changes cannot redirect or disable native retention, including inherited child capture selection. The parent verifies that directory before joining issued outcomes. Both `--log-junit /absolute/fresh.xml` and `--log-junit=/absolute/fresh.xml` preserve the caller's fresh report destination.

Failures in retained add-on behavior tests must remain visible in the complete matrix; they do not authorize test skips, diagnostic filters, expected-warning product assertions or Foundation fixes. No complete compatibility or publication approval is claimed. The scope-correction review packet records the exact cleanup tree and final seven-version results. No live EE installation/database/browser or coverage-completion claim is made.
