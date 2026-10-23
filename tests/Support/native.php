<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);

require_once __DIR__ . '/coverage.php';
$root = dirname(__DIR__, 2);
$case = $argv[1];
$temporaryDirectory = $argv[2];

if ($case === 'direct-access') {
    include $root . '/low_random/pi.low_random.php';
    throw new RuntimeException('Direct access did not exit.');
}

define('PATH_THIRD', $case === 'missing-phar' ? $temporaryDirectory . '/' : $root . '/');
define('APP_VER', $argv[3] ?? '7.5.0');

if ($case === 'missing-phar') {
    require $root . '/low_random/autoload.php';

    try {
        class_exists('Low\\Random\\FluxCapacitor\\Conduit\\StaticCache');
    } catch (Exception $exception) {
        echo $exception->getMessage();
        exit(0);
    }

    throw new RuntimeException('Missing PHAR was not reported.');
}

if ($case === 'fallback-loader') {
    if (!stream_wrapper_unregister('phar')) {
        throw new RuntimeException('Could not unregister phar stream.');
    }

    require $root . '/low_random/autoload.php';
    echo class_exists('Low\\Random\\FluxCapacitor\\Conduit\\StaticCache') ? 'loaded' : 'missing';
    exit(0);
}

if ($case === 'bridge-version') {
    require $root . '/low_random/autoload.php';
    $class = new ReflectionClass('Low\\Random\\FluxCapacitor\\Bridge\\EE');
    $bridge = $class->newInstance(array('module' => 'low_random', 'module_name' => 'Low Random'));
    echo json_encode(array('parent' => $class->getParentClass()->getShortName(), 'version' => $bridge->getEEVersion(false), 'legacy' => array($bridge->is_ee2(), $bridge->is_ee3(), $bridge->is_ee4(), $bridge->is_ee5())));
    exit(0);
}

throw new RuntimeException('Unknown native case: ' . $case);
