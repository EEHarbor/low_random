<?php

namespace Tests;

final class NativeProcessTest extends TestCase
{
    /**
     * Start native PHP using the same explicit Herd runtime and strict settings.
     *
     * @param array $arguments
     * @param array $environmentOverrides
     * @return array
     */
    private function process(array $arguments, array $environmentOverrides = array())
    {
        return $this->native($arguments, $environmentOverrides);
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
     * Raw collection creates fresh directories and rejects a repeated reservation.
     *
     * @return void
     */
    public function testRawCoverageReportLifecycle()
    {
        $prefix = $this->temporaryDirectory . '/new/reports/graph';
        $architecture = php_uname('m') === 'arm64' ? 'arm64' : 'x86';
        $extension = '/Applications/Herd.app/Contents/Resources/xdebug/xdebug-' . PHP_MAJOR_VERSION . PHP_MINOR_VERSION . '-' . $architecture . '.so';
        $environment = array('LOW_RANDOM_RAW_COVERAGE' => $prefix, 'LOW_RANDOM_XDEBUG_EXTENSION' => $extension, 'XDEBUG_MODE' => 'coverage');
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'bridge-version', $this->temporaryDirectory), $environment);
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $reports = glob($prefix . '.*.json');
        $this->assertCount(1, $reports);
        $payload = json_decode(file_get_contents($reports[0]), true);
        $this->assertSame(PHP_VERSION, $payload['php']);
        $this->assertNotEmpty($payload['coverage']);
        $repeat = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'coverage-repeat', $this->temporaryDirectory), $environment);
        $this->assertSame(255, $repeat[0]);
        $this->assertStringContainsString('Coverage report already exists:', $repeat[1] . $repeat[2]);
        $this->assertCount(2, glob($prefix . '.*.json'));
    }

    /**
     * Compile loose add-on sources while preserving the complete source inventory.
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

        foreach ($inventory['sources'] as $path => $metadata) {
            if (strpos($path, '!') !== false) {
                continue;
            }

            $source = file_get_contents($root . '/' . $path);

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
