<?php

namespace Tests;

final class NativeProcessTest extends TestCase
{
    /**
     * Start native PHP using the same explicit Herd runtime and strict settings.
     *
     * @param array $arguments
     * @return array
     */
    private function process(array $arguments)
    {
        $command = array_merge(array(PHP_BINARY, '-d', 'error_reporting=-1', '-d', 'display_errors=1', '-d', 'sys_temp_dir=' . $this->temporaryDirectory), $arguments);
        $extension = getenv('LOW_RANDOM_XDEBUG_EXTENSION');

        if ($extension !== false && $extension !== '') {
            array_splice($command, 1, 0, array('-d', 'zend_extension=' . $extension, '-d', 'xdebug.mode=coverage'));
        }

        $environment = getenv();
        $environment['TMPDIR'] = $this->temporaryDirectory;
        $process = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, dirname(__DIR__, 3), $environment);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return array(proc_close($process), $stdout, $stderr);
    }

    /**
     * Deny direct plugin access before the archive can be loaded.
     *
     * @return void
     */
    public function testDirectAccessGuard()
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'direct-access', $this->temporaryDirectory));
        $this->assertSame(array(0, 'No direct script access allowed', ''), $result);
    }

    /**
     * Missing archives produce a clear loader error.
     *
     * @return void
     */
    public function testLoaderReportsMissingArchive()
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'missing-phar', $this->temporaryDirectory));
        $this->assertSame(0, $result[0], $result[2]);
        $this->assertSame('Add-on PHAR file at location ' . $this->temporaryDirectory . '/low_random/low_random.phar not found.', $result[1]);
        $this->assertSame('', $result[2]);
    }

    /**
     * Native version selection loads each supported bridge independently.
     *
     * @return void
     */
    public function testEveryBridgeVersionLoadsInAFreshProcess()
    {
        foreach (array('2.9.3' => 'EE2', '3.5.17' => 'EE3', '4.3.8' => 'EE4', '5.4.0' => 'EE5', '6.4.0' => 'EE6', '7.5.0' => 'EE6') as $version => $parent) {
            $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'bridge-version', $this->temporaryDirectory, $version));
            $this->assertSame(0, $result[0], $result[2]);
            $expected = array();

            foreach (array(2, 3, 4, 5) as $legacyVersion) {
                $expected[] = substr($version, 0, 1) === (string) $legacyVersion;
            }

            $this->assertSame(array('parent' => $parent, 'version' => $version, 'legacy' => $expected), json_decode($result[1], true));
            $this->assertSame('', $result[2]);
        }
    }

    /**
     * The promised extension-free loader should work under strict diagnostics.
     *
     * @return void
     */
    public function testFallbackLoaderDoesNotEmitDiagnostics()
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'fallback-loader', $this->temporaryDirectory));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('loaded', $result[1]);
        $this->assertSame('', $result[2]);
    }

    /**
     * Compile every ordinary file, archive member and stub without modification.
     *
     * @return void
     */
    public function testEveryShippedPhpSourceCompilesNatively()
    {
        require_once dirname(__DIR__, 2) . '/scripts/inventory.php';
        $inventory = lowRandomInventory();
        $pinnedInventory = json_decode(file_get_contents(dirname(__DIR__, 2) . '/SOURCE_INVENTORY.json'), true);
        $this->assertSame($pinnedInventory, $inventory, 'Shipped source differs from pinned baseline.');
        $root = dirname(__DIR__, 3);
        $archive = new \Phar($root . '/low_random/low_random.phar');

        foreach ($inventory['sources'] as $path => $metadata) {
            if (strpos($path, '!/') !== false) {
                $source = $archive[explode('!/', $path, 2)[1]]->getContent();
            } else {
                $source = substr($path, -5) === '!stub' ? $archive->getStub() : file_get_contents($root . '/' . $path);
            }

            $this->assertSame($metadata['sha256'], hash('sha256', $source), $path);
            $copy = $this->temporaryDirectory . '/' . hash('sha256', $path) . '.php';
            file_put_contents($copy, $source);
            $this->assertSame($metadata['sha256'], hash_file('sha256', $copy), $path);
            $result = $this->process(array('-l', $copy));
            $this->assertSame(0, $result[0], $path . ': ' . $result[1] . $result[2]);
            $this->assertSame('', $result[2], $path);
            $this->assertSame('No syntax errors detected in ' . $copy . "\n", $result[1], $path);
        }
    }
}
