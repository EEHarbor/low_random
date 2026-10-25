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
                if ($file->isDir() && !$file->isLink()) {
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
     * @throws \InvalidArgumentException
     * @throws \ErrorException When an owned configuration or composition input cannot be verified.
     */
    protected function native(array $arguments, array $environmentOverrides = array(), $timeout = 20)
    {
        if ($timeout <= 0) {
            throw new \InvalidArgumentException('Native probe timeout must be positive.');
        }
        require_once dirname(__DIR__, 2) . '/Support/NativeCommand.php';
        $strict = dirname(__DIR__, 2) . '/strict-diagnostics.php';
        $plan = \Tests\Support\NativeCommand::guardArguments($arguments, $strict);
        $configuration = null;
        if ($plan['configuration_arguments'] !== null) {
            $discoveryEnvironment = $environmentOverrides;
            unset($discoveryEnvironment['LOW_RANDOM_STRICT_JOURNAL']);
            $discovery = $this->native(array_merge($plan['configuration_arguments'], array('-r',
                'require ' . var_export(dirname(__DIR__, 2) . '/Support/NativeConfiguration.php', true) . ';')),
                $discoveryEnvironment, $timeout);
            if ($discovery[0] !== 0 || $discovery[2] !== '') {
                return $discovery;
            }
            try {
                $configuration = json_decode($discovery[1], true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $error) {
                \LowRandomDiagnostics::handle(E_USER_ERROR, 'Owned native configuration discovery is not valid JSON.', __FILE__, __LINE__);
            }
            if (!is_array($configuration) || !isset($configuration['prepend_base64'], $configuration['files'])) {
                \LowRandomDiagnostics::handle(E_USER_ERROR, 'Owned native configuration discovery failed.', __FILE__, __LINE__);
            }
        }

        $prelude = null;
        $preludePath = null;
        $preludeBytes = null;
        $identity = null;
        try {
            if ($configuration !== null) {
                foreach ($configuration['files'] as $file) {
                    if (hash_file('sha256', $file['path']) !== $file['sha256']) {
                        \LowRandomDiagnostics::handle(E_USER_ERROR, 'Native configuration changed after discovery.', __FILE__, __LINE__);
                    }
                }
                $caller = base64_decode($configuration['prepend_base64'], true);
                if ($caller === false) {
                    \LowRandomDiagnostics::handle(E_USER_ERROR, 'Native configuration prelude is not lossless.', __FILE__, __LINE__);
                }
                $preludePath = dirname(__DIR__, 3) . '/.owned-native-prepend-' . bin2hex(random_bytes(12)) . '.php';
                $prelude = fopen($preludePath, 'x+b');
                if ($prelude === false) {
                    \LowRandomDiagnostics::handle(E_USER_ERROR, 'Cannot create owned native composition input.', __FILE__, __LINE__);
                }
                $identity = fstat($prelude);
                $preludeBytes = '<?php require_once ' . var_export($strict, true) . ';';
                if ($caller !== '') {
                    $preludeBytes .= 'require \\base64_decode(' . var_export($configuration['prepend_base64'], true) . ');';
                }
                if (fwrite($prelude, $preludeBytes) !== strlen($preludeBytes) || !fflush($prelude)) {
                    \LowRandomDiagnostics::handle(E_USER_ERROR, 'Cannot retain complete native composition input.', __FILE__, __LINE__);
                }
                $plan['arguments'][$plan['prepend_index']] = 'auto_prepend_file=' . $preludePath;
            }
            $result = $this->executeNative($plan['arguments'], $arguments, $environmentOverrides, $timeout);
            if ($configuration !== null) {
                foreach ($configuration['files'] as $file) {
                    if (hash_file('sha256', $file['path']) !== $file['sha256']) {
                        \LowRandomDiagnostics::handle(E_USER_ERROR, 'Native configuration changed during execution.', __FILE__, __LINE__);
                    }
                }
            }
            return $result;
        } finally {
            if (is_resource($prelude)) {
                rewind($prelude);
                $retained = stream_get_contents($prelude);
                $held = fstat($prelude);
                fclose($prelude);
                clearstatcache(true, $preludePath);
                $named = lstat($preludePath);
                if (is_link($preludePath) || $retained !== $preludeBytes || $named['nlink'] !== 1
                    || $held['dev'] !== $identity['dev'] || $held['ino'] !== $identity['ino']
                    || $named['dev'] !== $identity['dev'] || $named['ino'] !== $identity['ino']) {
                    \LowRandomDiagnostics::handle(E_USER_ERROR, 'A changed native composition input is preserved.', __FILE__, __LINE__);
                }
                unlink($preludePath);
            }
        }
    }

    /**
     * Capture one already prepared native child before joining its strict outcome.
     *
     * @param array $arguments Prepared executable options and program arguments.
     * @param array $originalArguments Original unmodified caller arguments.
     * @param array $environmentOverrides Process-local environment selections.
     * @param float $timeout Owned execution deadline in seconds.
     * @return array Native status, complete stdout and complete stderr.
     * @throws \RuntimeException When process ownership, capture or deadlines fail.
     */
    private function executeNative(array $arguments, array $originalArguments, array $environmentOverrides, $timeout)
    {
        if ($timeout <= 0) {
            throw new \InvalidArgumentException('Native probe timeout must be positive.');
        }

        $command = array_merge(array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=stderr', '-d', 'display_startup_errors=1', '-d', 'log_errors=0', '-d', 'sys_temp_dir=' . $this->temporaryDirectory), $arguments);
        $extension = $environmentOverrides['LOW_RANDOM_XDEBUG_EXTENSION'] ?? getenv('LOW_RANDOM_XDEBUG_EXTENSION');
        if ($extension !== false && $extension !== '') {
            array_splice($command, 1, 0, array('-d', 'zend_extension=' . $extension, '-d', 'xdebug.mode=coverage'));
        }

        $captureDirectory = getenv('LOW_RANDOM_STRICT_CAPTURES');
        $output = ($captureDirectory ?: $this->temporaryDirectory) . '/native-' . getmypid() . '-' . bin2hex(random_bytes(6));
        $journal = $environmentOverrides['LOW_RANDOM_STRICT_JOURNAL'] ?? $output . '.diagnostics';
        $environment = array_merge(getenv(), $environmentOverrides, array('TMPDIR' => $this->temporaryDirectory,
            'LOW_RANDOM_STRICT_JOURNAL' => $journal));
        $definition = (new \ReflectionClass($this))->getFileName();
        $capture = array('state' => 'started', 'owner_pid' => getmypid(), 'command' => $command,
            'arguments' => $originalArguments, 'timeout' => $timeout, 'journal' => $journal,
            'temporary_directory' => $this->temporaryDirectory,
            'coverage_prefix' => $environmentOverrides['LOW_RANDOM_RAW_COVERAGE'] ?? null,
            'case' => array('class' => get_class($this), 'method' => $this->getName(false),
                'file' => $definition, 'sha256' => hash_file('sha256', $definition)),
            'stdout' => $output . '.out', 'stderr' => $output . '.err');
        $capture['lint_input'] = null;
        if (count($originalArguments) === 2 && $originalArguments[0] === '-l'
            && substr($originalArguments[1], 0, 1) === '/' && is_file($originalArguments[1]) && !is_link($originalArguments[1])) {
            $capture['lint_input'] = array('path' => $originalArguments[1],
                'sha256' => hash_file('sha256', $originalArguments[1]), 'bytes' => filesize($originalArguments[1]));
        }
        if ($captureDirectory) {
            file_put_contents($output . '.json', json_encode($capture, JSON_THROW_ON_ERROR), LOCK_EX);
        }

        $process = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('file', $output . '.out', 'x'), 2 => array('file', $output . '.err', 'x')), $pipes, dirname(__DIR__, 3), $environment);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $status = proc_get_status($process);
        $capture['pid'] = $status['pid'];
        $deadline = hrtime(true) + ($timeout * 1000000000);
        $exit = null;
        $closed = null;
        $timedOut = false;
        $cleanupComplete = false;

        try {
            while ($status['running']) {
                if (hrtime(true) >= $deadline) {
                    $timedOut = true;
                    throw new \RuntimeException('Native PHP probe exceeded its deadline.');
                }
                usleep(1000);
                $status = proc_get_status($process);
            }
            $exit = $status['exitcode'];
        } finally {
            if ($status['running']) {
                proc_terminate($process, 15);
                $stop = hrtime(true) + 200000000;
                do {
                    usleep(1000);
                    $status = proc_get_status($process);
                } while ($status['running'] && hrtime(true) < $stop);
                if ($status['running']) {
                    proc_terminate($process, 9);
                    $stop = hrtime(true) + 2000000000;
                    do {
                        usleep(1000);
                        $status = proc_get_status($process);
                    } while ($status['running'] && hrtime(true) < $stop);
                }
            }
            if (!$status['running']) {
                $closed = proc_close($process);
                $cleanupComplete = true;
                if (!$timedOut && $exit === -1) {
                    $exit = $closed;
                }
            }
            if ($capture['lint_input'] !== null) {
                $input = $capture['lint_input'];
                $capture['lint_input']['unchanged'] = is_file($input['path']) && !is_link($input['path'])
                    && hash_file('sha256', $input['path']) === $input['sha256'] && filesize($input['path']) === $input['bytes'];
            }
            $capture['state'] = 'complete';
            $capture['exit'] = $exit;
            $capture['proc_close'] = $closed;
            $capture['last_status'] = $status;
            $capture['timed_out'] = $timedOut;
            $capture['cleanup_complete'] = $cleanupComplete;
            $capture['runtime_raw_base64'] = is_file($journal . '.runtime') ? base64_encode(file_get_contents($journal . '.runtime')) : '';
            $capture['diagnostics_raw_base64'] = is_file($journal) ? base64_encode(file_get_contents($journal)) : '';
            foreach (array('stdout', 'stderr') as $stream) {
                $capture[$stream . '_sha256'] = hash_file('sha256', $capture[$stream]);
                $capture[$stream . '_bytes'] = filesize($capture[$stream]);
            }
            if ($captureDirectory) {
                file_put_contents($output . '.json', json_encode($capture, JSON_THROW_ON_ERROR), LOCK_EX);
            }
            if (!$cleanupComplete) {
                exit(70);
            }
        }

        $result = array($exit, file_get_contents($output . '.out'), file_get_contents($output . '.err'));
        if (!$captureDirectory) {
            unlink($output . '.out');
            unlink($output . '.err');
        }

        return $result;
    }
}
