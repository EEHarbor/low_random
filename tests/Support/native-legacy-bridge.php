<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);

$root = dirname(__DIR__, 2);
$mode = $argv[1];
$temporary = $argv[2];
$options = json_decode($argv[4], true, 512, JSON_THROW_ON_ERROR);
$_SERVER['LOW_RANDOM_NATIVE_CASE'] = 'legacy-bridge/' . $mode;
require_once __DIR__ . '/coverage.php';
define('APP_VER', $argv[3]);
define('BASEPATH', $root . '/tests/');
define('PATH_THIRD', $temporary . '/third-party/');
define('APPPATH', $temporary . '/application/');
define('AMP', '&amp;');
define('BASE', $options['base'] ?? 'https://example.test/cp?C=home');
require __DIR__ . '/Environment.php';
require __DIR__ . '/ControlPanelServices.php';
require __DIR__ . '/LegacyBridgeFixtures.php';

$ee = (object) array(
    'config' => new \Tests\Support\Configuration(),
    'session' => new \Tests\Support\LegacySession(),
    'cache' => new \Tests\Support\CacheStore(),
    'load' => new \Tests\Support\LegacyViewLoader(),
);
$GLOBALS['low_random_ee'] = $ee;
$archive = 'phar://' . $root . '/low_random/low_random.phar/';
require $archive . 'FluxCapacitor/Bridge/AbstractBridge.php';
require $archive . 'FluxCapacitor/Conduit/StaticCache.php';
require $archive . 'FluxCapacitor/Bridge/EE2.php';
$bridge = new \Low\Random\FluxCapacitor\Bridge\EE2(array('module' => 'low_random', 'module_name' => 'Low Random'));

if ($mode === 'url') {
    $ee->config->items['cp_session_type'] = $options['session_type'];
    $ee->session->userdata = $options['userdata'];
    echo json_encode(array(
        'base' => $bridge->getBaseURL($options['method'], $options['extra']),
        'module' => $bridge->moduleURL('listing', $options['variables']),
    ), JSON_THROW_ON_ERROR);
    exit(0);
}

if ($mode === 'view') {
    if ($options['directory']) {
        mkdir(PATH_THIRD . 'low_random/views', 0700, true);
    }
    $result = $bridge->view('original', array('ee_ver' => 'caller', 'zero' => 0), $options['return'], $options['string']);
    $path = PATH_THIRD . 'low_random/views/dynamic.php';
    echo json_encode(array('result' => $result, 'calls' => $ee->load->calls, 'contents' => is_file($path) ? file_get_contents($path) : null), JSON_THROW_ON_ERROR);
    exit(0);
}

if ($mode === 'route') {
    echo json_encode(array('url' => $bridge->cpURL($options['path'], $options['mode'], $options['variables'])), JSON_THROW_ON_ERROR);
    exit(0);
}

if ($mode === 'cache') {
    $ee->session->cache['other_addon'] = array('sentinel' => 0);
    $set = $bridge->cache('set', 'key', 0, $options['persistent']);
    $get = $bridge->cache('get', 'key', false, $options['persistent']);
    $before = array('persistent' => $ee->cache->values, 'request' => $ee->session->cache);
    $bridge->cache($options['delete'], $options['key'], false, $options['persistent']);
    echo json_encode(array(
        'set' => $set, 'get' => $get, 'before' => $before,
        'after' => $bridge->cache('get', 'key', false, $options['persistent']),
        'calls' => $ee->cache->calls, 'request' => $ee->session->cache,
        'unknown' => $bridge->cache('unsupported', 'key'),
    ), JSON_THROW_ON_ERROR);
    exit(0);
}

throw new InvalidArgumentException('Unknown legacy bridge mode: ' . $mode);
