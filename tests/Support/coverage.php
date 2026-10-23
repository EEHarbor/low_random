<?php

$coveragePath = getenv('LOW_RANDOM_RAW_COVERAGE');

if ($coveragePath !== false && $coveragePath !== '') {
    if (!function_exists('xdebug_start_code_coverage')) {
        throw new RuntimeException('Raw branch measurement requires Xdebug.');
    }

    $root = dirname(__DIR__, 2);
    xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_INCLUDE, array($root . '/low_random/', 'phar://' . $root . '/low_random/'));
    xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);
    register_shutdown_function(static function () use ($coveragePath, $root) {
        $coverage = array_filter(xdebug_get_code_coverage(), static function ($file) use ($root) {
            return strpos($file, $root . '/low_random/') === 0 || strpos($file, 'phar://' . $root . '/low_random/') === 0;
        }, ARRAY_FILTER_USE_KEY);
        xdebug_stop_code_coverage();
        $report = array('php' => PHP_VERSION, 'xdebug' => phpversion('xdebug'), 'pid' => getmypid(), 'coverage' => $coverage);
        file_put_contents($coveragePath . '.' . getmypid() . '.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    });
}
