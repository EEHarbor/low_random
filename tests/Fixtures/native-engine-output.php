<?php

$metadata = require dirname(__DIR__, 2) . '/low_random/addon.setup.php';
if ($metadata['namespace'] !== 'Low\\Random') {
    throw new RuntimeException('The distributed add-on metadata did not execute.');
}

$mode = $argv[1];
if (strpos($mode, 'startup-') === 0) {
    echo 'source-kept';
    return;
}
/**
 * Emit a native warning without invoking the strict callback handler.
 *
 * @return void
 */
$warning = static function () use ($mode) {
    $display = ini_set('display_errors', '1');
    $format = ini_set('html_errors', $mode === 'html' || $mode === 'late' ? '1' : '0');
    set_error_handler(static function () { return false; });
    try {
        $unused = (string) array('owned native output');
    } finally {
        restore_error_handler();
        ini_set('display_errors', $display);
        ini_set('html_errors', $format);
    }
};
if ($mode === 'late') {
    register_shutdown_function($warning);
} else {
    $warning();
}
echo 'source-kept';
