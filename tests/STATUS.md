# Low Random PHPUnit campaign status

Status: **blocked / incomplete checkpoint**. Tests and reports are ready for independent exact-diff review. No production source was changed. No commits or publication have occurred yet.

## Baseline and provenance

- Repository: `/Users/tomjaeger/Sites/add-ons/low_random`; origin: `https://github.com/EEHarbor/low_random.git`.
- Clean checkout had no dirty/untracked files or other worktrees. Fetched origin, switched develop, ran `git pull --ff-only origin develop`, verified clean exact `HEAD == origin/develop`, then created `unit-tests-2026` before edits.
- Pinned baseline/head: `892b6dae8282b159e580e3c1a6076a39d9902f84`.
- Source inventory: `SOURCE_INVENTORY.json`; four ordinary PHP files, 22 archive members and executable PHAR stub; 27 source units in total. Nothing is excluded from the inventory. Hashes are recorded for every source unit and the original archive. Each complete matrix suite verifies byte-identical compile-copy hashes, including all previously unloaded archive files.
- Production invariance command: `git diff 892b6dae8282b159e580e3c1a6076a39d9902f84 -- low_random` (empty).
- Preflight initially reported no harness. Correct-branch scaffold plus approved exact local PHAR download succeeded. Resolved command: `./tests/.phpunit/run-phpunit.sh -c phpunit.xml`.

## Complete configured matrix

Command: `./tests/scripts/run-matrix.sh`. Evidence: `/Users/tomjaeger/Sites/add-ons/low_random/tests/reports/matrix.dUsVe5/` contains seven full logs, native versions, JUnit reports and exit codes. The configured suite includes all tests and native compilation/version/loader probes; no test filters or zero-test runs are counted.

| Herd runtime | PHPUnit | Tests | Assertions | Errors | Failures | Warnings | Risky | Skipped | Incomplete | Exit |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| PHP 7.4.33 | 9.6.38 | 57 | 447 | 5 | 6 | 0 | 0 | 0 | 0 | 2 |
| PHP 8.0.30 | 9.6.38 | 57 | 447 | 5 | 6 | 0 | 0 | 0 | 0 | 2 |
| PHP 8.1.33 | 9.6.38 | 57 | 446 | 6 | 6 | 0 | 0 | 0 | 0 | 2 |
| PHP 8.2.30 | 9.6.38 | 57 | 446 | 6 | 6 | 0 | 0 | 0 | 0 | 2 |
| PHP 8.3.28 | 9.6.38 | 57 | 446 | 6 | 6 | 0 | 0 | 0 | 0 | 2 |
| PHP 8.4.15 | 9.6.38 | 57 | 446 | 6 | 6 | 0 | 0 | 0 | 0 | 2 |
| PHP 8.5.0 | 9.6.38 | 57 | 446 | 6 | 6 | 0 | 0 | 0 | 0 | 2 |

`E_ALL` and deprecations are enabled in each parent and native child. The source handler throws even for diagnostics hidden by `@`; an independent suppressed E_USER_DEPRECATED probe verifies it. PHPUnit/harness emitted no separate diagnostics. A randomized PHP 8.2 suite (`--order-by=random --random-order-seed=20261023`) reproduced exactly 57 tests /446 assertions /6 errors /6 failures, without additional order-dependent failures.

## Source regressions retained red

All following paths are shipped source paths inside `low_random/low_random.phar` unless specified:

1. `Conduit/GlobalFieldSettings.php:21`: missing key in a nonempty settings array emits a hidden undefined-index warning.
2. `Conduit/GlobalFieldSettings.php:33`: malformed stored base64/serialization emits a hidden unserialize warning.
3. `Base/Pi.php:30`: static `info()` uses `$this`; execution fails on every runtime before later metadata values or a return can execute.
4. `Bridge/EE5.php:412`: a short CP URI indexes segment 3 under `@`; strict diagnostics expose the warning.
5. `Conduit/PersistentCache.php:21` → `Conduit/StaticCache.php:18`: documented array keys are passed as already processed keys and fail as illegal offsets.
6. Ordinary `pi.low_random.php:64`: null tagdata reaches deprecated `trim(null)` on PHP 8.1+ (passes PHP 7.4/8.0). This is an explicit boundary-shape regression, not a claim normal EE tagdata is null.
7. PHAR stub line 177: the advertised stream-free extraction path calls `@mkdir` on a directory already created earlier in that same extraction. Fresh native strict runs stop with `mkdir(): File exists`; no shared extraction directory is used.
8. `Conduit/PersistentCache.php:21`: set hashes static keys while get requests raw keys. Removing the disk file after set loses a supposedly cached value.
9. Ordinary `pi.low_random.php:135-140`: file candidates are appended to the prior method's set, leaking a prior item into current upload-destination selection. Exact candidate-log assertions make the failure deterministic.
10. `Conduit/StaticCache.php:18`: a stored numeric zero is returned as false.
11. `Conduit/StaticCache.php:58-60`: delete removes the key, then always reports false despite its documented success return.
12. `Conduit/StaticCache.php:108`: `array_filter` removes numeric-zero key components, causing distinct composite keys to overwrite each other.

These are source failures, not exceptions expected merely to make the suite green. Invalid user shapes for native string APIs remain explicit rejection tests. Existing helper/model behavior is verified instead of invented to cover lines. No production repair is authorized.

## Coverage and blockers

- Initial skill baseline command was attempted before tests existed and produced no Clover report (missing driver/zero tests); no baseline percentage is invented.
- Final Clover: `tests/reports/review-fixed-clover.xml`; skill helper used `--source-root low_random --include-magic --coverage-clover <absolute final-clover.xml> --reuse-clover --json`, saved in `tests/reports/review-fixed-methods.json`.
- The helper reports 8 ordinary plugin methods at 100% executable lines, but branch fields are null. Those are **not** 100% coverage milestones.
- Genuine native branch run: PHP 8.2.30 /Xdebug 3.5.3 with process-local `-d zend_extension=.../xdebug-82-arm64.so -d xdebug.mode=coverage`; full raw command is in `README.md`. Raw reports use `tests/reports/review-fixed-raw-82.PID.json`; main `91017`, plus independent native children. `tests/reports/review-fixed-branches.json` and `COVERAGE.md` show per-function line/basic-block/outgoing-exit counts, exact uncovered file lines, and unknown uninstrumented sources. No missing branch data is interpreted as 100%.
- Guaranteed reachability blocker: `Base/Pi.php:30` throws; its later metadata entries/return behavior cannot run through the actual static API without source changes. Native fallback extraction stops at stub line 177; later branches remain blocked behind this diagnostic under required strict execution. Stub Windows-only branches cannot run natively on macOS.
- Broader archive bridge/control-panel/extension/updater/fieldtype/navigation/licensing methods remain uncovered or partly covered. These are explicitly incomplete work, not claimed impossible merely because scope is large. Native syntax and version-selection checks cover loading risk but do not substitute for behavior/branches.
- Behavioral coverage currently includes constructor/setup, all plugin APIs and file filters/model chains, both license views, static/disk cache, settings serialization/precedence/persistence, version comparisons, module parameters, tab adapters, all bridge version selection, guard and missing/fallback loader branches.

## Review and publication

Independent reviewer verified every file/hash in the first immutable snapshot (`8462f73fb37a9be36b8e8a1e755740afa77d13c704379b68d1dad8f811197d3e`) and independently reproduced its complete PHP 8.2 suite. Three P2 harness findings were corrected: capture/restore the PHAR-mutated include path in finally; match native DB_result named/default/numeric/current-row/SQL NULL/empty-result contracts; skip dot directories before recursive filename traversal while preserving native readdir order. Four fixture-contract tests (24 assertions) pass independently. All seven complete suites, randomized order and genuine coverage were rerun after repairs; only the same source regressions remain. The replacement immutable snapshot is awaiting follow-up independent review. Self-review also corrected initial autoloader leakage and the template ignore list. No additional actionable harness issue is currently known. Residual risks are the documented source defects, incomplete archive branch coverage, and environment-specific PHAR loader branches.

Coordinator will receive an immutable exact staged patch plus SHA256 and copied evidence. After independent review, actionable test issues must be corrected and affected validation repeated before scoped checkpoint commits/push. The user has authorized reviewed red source-regression commits. Do not label this checkpoint complete or open a PR.
