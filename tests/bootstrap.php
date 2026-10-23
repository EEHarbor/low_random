<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
define('BASEPATH', dirname(__DIR__) . '/tests/');
define('PATH_THIRD', dirname(__DIR__) . '/');
define('APP_VER', '7.5.0');
define('REQ', 'PAGE');
define('PATH_CACHE', sys_get_temp_dir() . '/low-random-tests-' . getmypid() . '/');
define('DIR_WRITE_MODE', 0700);
define('FILE_WRITE_MODE', 0600);

require_once __DIR__ . '/Support/coverage.php';
require_once __DIR__ . '/Support/Environment.php';
require_once __DIR__ . '/tests/Unit/TestCase.php';

\Tests\Support\Diagnostics::install();
