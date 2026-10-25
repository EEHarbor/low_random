<?php

define('PATH_THIRD', dirname(__DIR__, 2) . '/');
define('APP_VER', '7.5.0');
require PATH_THIRD . 'low_random/autoload.php';
$class = 'Low\\Random\\FluxCapacitor\\Base\\Pi';
$entry = $argv[3] ?? 'direct';
if ($entry === 'direct') {
    class_exists($class);
}
$mode = $argv[1];
$owned = $argv[2];
$outside = $owned . '/preserved-target';
mkdir($outside, 0700);
file_put_contents($outside . '/.preserved', 'owned sentinel');
file_put_contents($outside . '/ordinary.txt', 'owned payload');
$container = $owned . '/pharextract';
$link = $mode === 'container' ? $container : $container . '/low_random';
$original = getcwd();
$before = getenv('TMPDIR');

if ($mode !== 'container') {
    mkdir($container, 0700);
}

symlink($outside, $link);

try {
    putenv('TMPDIR=' . $owned);
    $message = null;

    try {
        if ($entry === 'fallback') {
            stream_wrapper_unregister('phar');
            class_exists($class);
        }
        if ($entry !== 'fallback' && $mode === 'cleanup') {
            chdir($owned);
            Extract_low_random::_removeTmpFiles($link . '/', $original);
        }
        if ($entry !== 'fallback' && $mode !== 'cleanup') {
            Extract_low_random::go(true);
        }
    } catch (RuntimeException $exception) {
        $message = $exception->getMessage();
    }

    echo json_encode(array(
        'hidden' => file_get_contents($outside . '/.preserved'),
        'ordinary' => file_get_contents($outside . '/ordinary.txt'),
        'message' => $message,
        'cwd' => getcwd() === $original,
        'link' => is_link($link),
    ), JSON_THROW_ON_ERROR);
} finally {
    chdir($original);
    putenv($before === false ? 'TMPDIR' : 'TMPDIR=' . $before);

    if (is_link($link)) {
        unlink($link);
    }
}
