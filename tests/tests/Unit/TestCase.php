<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\StaticCache;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\Support\Database;
use Tests\Support\Diagnostics;
use Tests\Support\FileQuery;
use Tests\Support\Loader;
use Tests\Support\Template;

abstract class TestCase extends BaseTestCase
{
    protected $ee;
    protected $temporaryDirectory;
    private $previousGlobals;
    private $autoloaders;
    private $includePath;

    /**
     * Isolate the EE container, autoloaders and per-test filesystem.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->previousGlobals = array();

        foreach (array('low_random_ee', 'low_random_services', 'low_random_forms') as $key) {
            $this->previousGlobals[$key] = array(array_key_exists($key, $GLOBALS), $GLOBALS[$key] ?? null);
        }

        $this->includePath = get_include_path();
        Diagnostics::install();
        $this->temporaryDirectory = sys_get_temp_dir() . '/low-random-' . getmypid() . '-' . bin2hex(random_bytes(6));
        mkdir($this->temporaryDirectory, 0700);
        $this->ee = (object) array('TMPL' => new Template(), 'load' => new Loader(), 'db' => new Database());
        $this->ee->config = new class {
            /**
             * Read the active test configuration value.
             *
             * @param string $key
             * @return mixed
             */
            public function item($key) { return $key === 'site_id' ? 1 : null; }
        };
        $GLOBALS['low_random_ee'] = $this->ee;
        $GLOBALS['low_random_services'] = array('Model' => new FileQuery());
        $GLOBALS['low_random_forms'] = array();
        require_once PATH_THIRD . 'low_random/pi.low_random.php';
        $this->autoloaders = spl_autoload_functions();
        StaticCache::clear();
    }

    /**
     * Restore process state even after a regression fails.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        try {
            StaticCache::clear();

            foreach (spl_autoload_functions() as $loader) {
                if (!in_array($loader, $this->autoloaders, true)) {
                    spl_autoload_unregister($loader);
                }
            }

            foreach ($this->previousGlobals as $key => $value) {
                if ($value[0]) {
                    $GLOBALS[$key] = $value[1];
                    continue;
                }

                unset($GLOBALS[$key]);
            }

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->temporaryDirectory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                    continue;
                }

                unlink($file->getPathname());
            }

            rmdir($this->temporaryDirectory);
        } finally {
            set_include_path($this->includePath);
            restore_error_handler();
        }
    }

    /**
     * Create the real plugin, including the shipped archive constructor.
     *
     * @param array $parameters
     * @param mixed $tagdata
     * @return \Low_random
     */
    protected function plugin(array $parameters = array(), $tagdata = '')
    {
        $this->ee->TMPL->tagparams = $parameters;
        $this->ee->TMPL->tagdata = $tagdata;
        return new \Low_random();
    }

    /**
     * Assert the complete set without relying on probabilistic selections.
     *
     * @param array $candidates
     * @return void
     */
    protected function assertCandidates(array $candidates)
    {
        $this->assertSame('Low Random: getting random item from:<br>' . implode('<br>', $candidates), end($this->ee->TMPL->logs));
    }
}
