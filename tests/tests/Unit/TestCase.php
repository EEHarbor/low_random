<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\StaticCache;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\Support\Configuration;
use Tests\Support\ControlPanel;
use Tests\Support\Database;
use Tests\Support\Diagnostics;
use Tests\Support\FileQuery;
use Tests\Support\Loader;
use Tests\Support\SchemaForge;
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
        $this->ee->config = new Configuration();
        $this->ee->cp = new ControlPanel();
        $this->ee->dbforge = new SchemaForge($this->ee->db);
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

    /**
     * Execute native probes with isolated output files and a bounded lifetime.
     *
     * @param array $arguments
     * @param array $environmentOverrides
     * @param float $timeout
     * @return array
     * @throws \RuntimeException
     */
    protected function native(array $arguments, array $environmentOverrides = array(), $timeout = 20)
    {
        if ($timeout <= 0) {
            throw new \InvalidArgumentException('Native probe timeout must be positive.');
        }

        $command = array_merge(array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=1', '-d', 'sys_temp_dir=' . $this->temporaryDirectory), $arguments);
        $extension = $environmentOverrides['LOW_RANDOM_XDEBUG_EXTENSION'] ?? getenv('LOW_RANDOM_XDEBUG_EXTENSION');

        if ($extension !== false && $extension !== '') {
            array_splice($command, 1, 0, array('-d', 'zend_extension=' . $extension, '-d', 'xdebug.mode=coverage'));
        }

        $environment = array_merge(getenv(), $environmentOverrides, array('TMPDIR' => $this->temporaryDirectory));
        $output = $this->temporaryDirectory . '/native-' . bin2hex(random_bytes(6));
        $process = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('file', $output . '.out', 'w'), 2 => array('file', $output . '.err', 'w')), $pipes, dirname(__DIR__, 3), $environment);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $deadline = hrtime(true) + ($timeout * 1000000000);

        try {
            do {
                if (hrtime(true) >= $deadline) {
                    throw new \RuntimeException('Native PHP probe exceeded its deadline.');
                }

                $status = proc_get_status($process);

                if (!$status['running']) {
                    $closed = proc_close($process);
                    $process = null;
                    $exit = $status['exitcode'] !== -1 ? $status['exitcode'] : $closed;
                    return array($exit, file_get_contents($output . '.out'), file_get_contents($output . '.err'));
                }

                usleep(10000);
            } while (true);
        } finally {
            if (is_resource($process)) {
                proc_terminate($process, 9);
                proc_close($process);
            }

            unlink($output . '.out');
            unlink($output . '.err');
        }
    }
}
