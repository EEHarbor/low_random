<?php

$coveragePath = getenv('LOW_RANDOM_RAW_COVERAGE');

if ($coveragePath !== false && $coveragePath !== '') {
    if (!function_exists('xdebug_start_code_coverage')) {
        throw new RuntimeException('Raw branch measurement requires Xdebug.');
    }

    $reportFile = $coveragePath . '.' . getmypid() . '.json';
    $reportParent = dirname($reportFile);

    if (!is_dir($reportParent) && !mkdir($reportParent, 0700, true)) {
        throw new RuntimeException('Cannot create coverage report directory: ' . $reportParent);
    }

    if (file_exists($reportFile) || is_link($reportFile)) {
        throw new RuntimeException('Coverage report already exists: ' . $reportFile);
    }

    $reportHandle = fopen($reportFile, 'x');

    if ($reportHandle === false) {
        throw new RuntimeException('Cannot reserve coverage report: ' . $reportFile);
    }

    $root = dirname(__DIR__, 2);
    xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_INCLUDE, array($root . '/low_random/', 'phar://' . $root . '/low_random/'));
    xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);
    register_shutdown_function(static function () use ($reportHandle, $root) {
        $coverage = array_filter(xdebug_get_code_coverage(), static function ($file) use ($root) {
            return strpos($file, $root . '/low_random/') === 0 || strpos($file, 'phar://' . $root . '/low_random/') === 0;
        }, ARRAY_FILTER_USE_KEY);
        xdebug_stop_code_coverage();

        foreach ($coverage as &$file) {
            foreach ($file['functions'] ?? array() as $name => $function) {
                unset($file['functions'][$name]['paths']);
            }
        }
        unset($file);

        $report = array(
            'php' => PHP_VERSION,
            'xdebug' => phpversion('xdebug'),
            'pid' => getmypid(),
            'source_root' => $root,
            'baseline' => '892b6dae8282b159e580e3c1a6076a39d9902f84',
            'archive_sha256' => hash_file('sha256', $root . '/low_random/low_random.phar'),
            'ee_version' => defined('APP_VER') ? APP_VER : null,
            'request_type' => defined('REQ') ? REQ : null,
            'native_case' => $_SERVER['LOW_RANDOM_NATIVE_CASE'] ?? null,
            'opcache' => array(
                'enable_cli' => ini_get('opcache.enable_cli'),
                'optimization_level' => ini_get('opcache.optimization_level'),
                'validate_permission' => ini_get('opcache.validate_permission'),
            ),
            'phar_stream' => in_array('phar', stream_get_wrappers(), true),
            'retained_metrics' => 'Native Xdebug lines, basic blocks and unchanged outgoing exits. Full execution paths are not required and are omitted.',
            'coverage' => $coverage,
        );
        $encoded = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($encoded === false) {
            fclose($reportHandle);
            throw new RuntimeException('Cannot encode the coverage report.');
        }

        $written = fwrite($reportHandle, $encoded);
        $closed = fclose($reportHandle);

        if ($written !== strlen($encoded) || !$closed) {
            throw new RuntimeException('Cannot write the complete coverage report.');
        }
    });
}
