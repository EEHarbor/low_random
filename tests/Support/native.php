<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);

require_once __DIR__ . '/coverage.php';
$root = dirname(__DIR__, 2);
$case = $argv[1];
$_SERVER['LOW_RANDOM_NATIVE_CASE'] = $case;
$temporaryDirectory = $argv[2];

if ($case === 'direct-access') {
    include $root . '/low_random/pi.low_random.php';
    throw new RuntimeException('Direct access did not exit.');
}

if ($case === 'crypto-queue-probe') {
    // PHP 7.4 includes the pinned tool's shebang as text.
    ob_start();
    require $root . '/tests/.phpunit/phpunit-9.phar';
    $toolOutput = ob_get_clean();

    if ($toolOutput !== '' && $toolOutput !== "#!/usr/bin/env php\n") {
        throw new RuntimeException('Unexpected pinned PHPUnit bootstrap output: ' . $toolOutput);
    }

    require dirname(__DIR__) . '/bootstrap.php';
    $probe = new class('cryptoQueueProbe') extends \Tests\TestCase {
        /**
         * Mutate only this disposable process's queue while testing the real helper.
         *
         * @return void
         */
        public function verifyQueue()
        {
            parent::setUp();

            try {
                while (openssl_error_string() !== false) {
                }

                openssl_pkey_get_public('not a key');
                $expected = $this->readQueue();
                $this->assertNotEmpty($expected);
                openssl_pkey_get_public('not a key');
                $result = $this->native(array(__FILE__, 'version-check', $this->temporaryDirectory));
                $this->assertSame(0, $result[0], $result[1] . $result[2]);
                $this->assertSame($expected, $this->readQueue());
                echo 'caller queue preserved';
            } finally {
                parent::tearDown();
            }
        }

        /**
         * Read the complete queue only inside the disposable probe process.
         *
         * @return array
         */
        private function readQueue()
        {
            $errors = array();

            while (($error = openssl_error_string()) !== false) {
                $errors[] = $error;
            }

            return $errors;
        }
    };
    $probe->verifyQueue();
    exit(0);
}

define('PATH_THIRD', $case === 'missing-phar' ? $temporaryDirectory . '/' : $root . '/');
define('APP_VER', $argv[3] ?? '7.5.0');

if ($case === 'coverage-repeat') {
    require __DIR__ . '/coverage.php';
    throw new RuntimeException('A repeated collector should reject the reserved report.');
}

if (in_array($case, array('extension-context', 'archive-navigation', 'fieldtype-context', 'fieldtype-import', 'version-check', 'updater-constructor'), true)) {
    define('BASEPATH', $root . '/tests/');
    define('PATH_CACHE', $temporaryDirectory . '/cache/');
    define('REQ', $argv[4] ?? 'PAGE');
    define('BASE', 'https://example.test/cp?S=0');
    define('AMP', '&amp;');
    require_once __DIR__ . '/Environment.php';
    require_once __DIR__ . '/ControlPanelServices.php';
    $database = new \Tests\Support\Database();
    $GLOBALS['low_random_ee'] = (object) array(
        'config' => new \Tests\Support\Configuration(),
        'db' => $database,
        'dbforge' => new \Tests\Support\SchemaForge($database),
        'load' => new \Tests\Support\Loader(),
        'cp' => new \Tests\Support\ControlPanel(),
    );
    $GLOBALS['low_random_services'] = array('CP/URL' => new \Tests\Support\UrlFactory());
    require $root . '/low_random/autoload.php';

    if ($case === 'version-check') {
        $mode = $argv[5] ?? 'default';

        if ($mode === 'development') {
            $_GET['ldev'] = '1';
            $GLOBALS['low_random_ee']->cp->footer[] = '<script>existing()</script>';
        }

        $version = new \Low\Random\FluxCapacitor\Conduit\Version();
        $database->results['modules'] = array(array('module_version' => '1.0.0'));
        $database->results['eeharbor_licenses'] = array(array('license_key' => 'local-key', 'ignore_site' => 0));
        $GLOBALS['low_random_ee']->config->items['site_license_key'] = 'local-core-key';

        if ($mode === 'long-url') {
            $GLOBALS['low_random_ee']->config->items['base_url'] = 'https://example.test/' . str_repeat('a', 1000);
        }

        $first = $version->check();
        $second = $version->check();
        echo json_encode(array('first' => $first, 'second' => $second, 'footer' => $GLOBALS['low_random_ee']->cp->footer));
        exit(0);
    }

    if ($case === 'updater-constructor') {
        require_once __DIR__ . '/UpdaterFixture.php';
        $updater = new \Low_random_test_upd();
        echo json_encode(array('version' => $updater->version, 'module' => $updater->module_name, 'footerCount' => count($GLOBALS['low_random_ee']->cp->footer), 'schema' => array_column($GLOBALS['low_random_ee']->dbforge->calls, 0)));
        exit(0);
    }

    if ($case === 'fieldtype-context' || $case === 'fieldtype-import') {
        $import = $case === 'fieldtype-import' ? $argv[5] : null;
        define('SYSPATH', $temporaryDirectory . '/system/');
        define('APPPATH', $temporaryDirectory . ($import === 'installer' ? '/installer/' : '/application/'));

        if ($import !== null) {
            $_SERVER['LOW_RANDOM_NATIVE_CASE'] .= '/' . $import;
            $paths = array(
                'system' => SYSPATH . 'ee/legacy/fieldtypes/EE_Fieldtype.php',
                'application' => APPPATH . 'fieldtypes/EE_Fieldtype.php',
                'installer' => $temporaryDirectory . '/expressionengine/fieldtypes/EE_Fieldtype.php',
            );
            $copy = $paths[$import];
            mkdir(dirname($copy), 0700, true);
            copy(__DIR__ . '/FieldtypeFixture.php', $copy);

            if (hash_file('sha256', $copy) !== hash_file('sha256', __DIR__ . '/FieldtypeFixture.php')) {
                throw new RuntimeException('The native fieldtype parent fixture copy changed.');
            }
        }

        if ($import === null) {
            require_once __DIR__ . '/FieldtypeFixture.php';
        }

        if (($argv[5] ?? '') === 'predefined') {
            define('Low\\Random\\VERSION', '4.1.1');
        }

        $GLOBALS['low_random_ee']->input = new \Tests\Support\RequestInput();
        $GLOBALS['low_random_ee']->input->post = array('channel_id' => '9');
        $GLOBALS['low_random_ee']->table = new class {
            public $rows = array();

            /**
             * Record EE2's variadic table-row contract for normal and wide rows.
             *
             * @param mixed ...$arguments
             * @return void
             */
            public function add_row(...$arguments)
            {
                $this->rows[] = $arguments;
            }
        };
        $fieldtype = new class extends \Low\Random\FluxCapacitor\Base\Ft {};
        $fieldtype->settings = array('field_channel_id' => 8);
        $fieldtype->displaySettingsRow('Title', '<input>');
        $fieldtype->displaySettingsRow('Wide', '<select>', '', true);
        echo json_encode(array(
            'channel' => $fieldtype->getChannelId(),
            'table' => $GLOBALS['low_random_ee']->table->rows,
            'rows' => $fieldtype->rows,
            'result' => $fieldtype->displaySettingsReturn(),
            'footerCount' => count($GLOBALS['low_random_ee']->cp->footer),
            'version' => $fieldtype->info['version'],
            'parent_sha256' => hash_file('sha256', (new ReflectionClass('EE_Fieldtype'))->getFileName()),
        ));
        exit(0);
    }

    if ($case === 'archive-navigation') {
        echo class_exists('Low\\Random\\FluxCapacitor\\Conduit\\McpNav') ? 'loaded' : 'missing';
        exit(0);
    }

    $extension = new \Low\Random\FluxCapacitor\Base\Ext();
    $database->calls = array();
    $extension->disable_extension();
    $menu = new class {
        public $items = array();

        /**
         * Record the legacy CP menu callback without changing its URL value.
         *
         * @param string $title
         * @param mixed $url
         * @return void
         */
        public function addItem($title, $url)
        {
            $this->items[] = array($title, (string) $url);
        }
    };
    $result = $extension->cp_custom_menu($menu);
    echo json_encode(array('deletes' => $database->calls, 'menu' => $menu->items, 'result' => $result));
    exit(0);
}

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
