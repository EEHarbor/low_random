<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);
$root = dirname(__DIR__, 2);
$mode = $argv[1];
$_SERVER['LOW_RANDOM_NATIVE_CASE'] = 'loader-options/' . $mode;
require __DIR__ . '/coverage.php';
define('PATH_THIRD', $root . '/');
define('APP_VER', '7.5.0');
require $root . '/low_random/autoload.php';

if ($mode === 'neighbor') {
    $calls = array();
    spl_autoload_register(static function ($class) use (&$calls) {
        $calls[] = $class;

        if ($class === 'Low\\Random\\FluxCapacitorExtra\\Widget') {
            require __DIR__ . '/NeighborLibraryFixture.php';
        }
    });
    $loaded = class_exists('Low\\Random\\FluxCapacitorExtra\\Widget');
    echo json_encode(array('loaded' => $loaded, 'calls' => $calls, 'value' => $loaded ? (new \Low\Random\FluxCapacitorExtra\Widget())->value : null), JSON_THROW_ON_ERROR);
    exit(0);
}

$loaded = class_exists('Low\\Random\\FluxCapacitor\\Base\\Pi');
$status = $mode === 'restricted' || !function_exists('opcache_get_status') ? null : opcache_get_status(false);
echo json_encode(array(
    'loaded' => $loaded,
    'file' => (new ReflectionClass('Low\\Random\\FluxCapacitor\\Base\\Pi'))->getFileName(),
    'enable' => filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN),
    'enable_cli' => filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN),
    'validate_permission' => filter_var(ini_get('opcache.validate_permission'), FILTER_VALIDATE_BOOLEAN),
    'configuration_available' => function_exists('opcache_get_configuration'),
    'extension_available' => extension_loaded('Zend OPcache'),
    'runtime_active' => is_array($status) ? $status['opcache_enabled'] : null,
    'archive_sha256' => hash_file('sha256', $root . '/low_random/low_random.phar'),
), JSON_THROW_ON_ERROR);
