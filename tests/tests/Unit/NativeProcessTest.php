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
     * Extension menu and removal behavior differs between native EE2 and EE7.
     *
     * @dataProvider extensionContexts
     * @param string $version
     * @param string $request
     * @return void
     */
    public function testExtensionContextsInFreshProcesses($version, $request)
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'extension-context', $this->temporaryDirectory, $version, $request));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $data = json_decode($result[1], true);
        $expected = array(
            array('where', array('class', 'Low\\Random\\FluxCapacitor\\Base\\Ext')),
            array('delete', array('extensions')),
        );
        $legacy = substr($version, 0, 1) === '2';

        if (!$legacy) {
            $expected[] = array('where', array('data', 'Low\\Random\\FluxCapacitor\\Base\\Ext'));
            $expected[] = array('delete', array('menu_items'));
        }

        $this->assertSame($expected, $data['deletes']);
        $this->assertSame($request === 'PAGE' ? true : null, $data['result']);
        $this->assertCount($request === 'PAGE' ? 0 : 1, $data['menu']);

        if ($request === 'CP') {
            $this->assertSame('Low Random', $data['menu'][0][0]);
            $this->assertStringContainsString($legacy ? 'module=low_random' : 'addons/settings/low_random/index', $data['menu'][0][1]);
        }
    }

    /**
     * Keep old-version and request variations as independently executed cases.
     *
     * @return array
     */
    public static function extensionContexts()
    {
        return array(array('2.9.3', 'PAGE'), array('2.9.3', 'CP'), array('2.5.5', 'CP'), array('7.5.0', 'PAGE'), array('7.5.0', 'CP'));
    }

    /**
     * The shipped navigation base should load when an optional custom nav is absent.
     *
     * @return void
     */
    public function testNavigationBaseLoadsWithoutOptionalAddonNavigation()
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'archive-navigation', $this->temporaryDirectory));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('loaded', $result[1]);
        $this->assertSame('', $result[2]);
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
     * Native EE2 and EE7 fieldtypes honor framework row and request differences.
     *
     * @dataProvider fieldtypeContexts
     * @param string $version
     * @param string $request
     * @param string $predefined
     * @return void
     */
    public function testFieldtypeContextsInFreshProcesses($version, $request, $predefined)
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'fieldtype-context', $this->temporaryDirectory, $version, $request, $predefined));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $data = json_decode($result[1], true);
        $legacy = substr($version, 0, 1) === '2';
        $this->assertSame($legacy ? '9' : 8, $data['channel']);
        $this->assertSame('4.1.1', $data['version']);
        $this->assertSame($request === 'CP' ? 2 : 0, $data['footerCount']);

        if ($legacy) {
            $this->assertSame(array(array('Title', '<input>'), array(array('colspan' => '2', 'data' => 'Wide<select>'))), $data['table']);
            $this->assertSame(array(), $data['rows']);
            $this->assertTrue($data['result']);
            return;
        }

        $this->assertSame(array(), $data['table']);
        $this->assertCount(2, $data['rows']);
        $this->assertSame($data['rows'], $data['result']['field_options_low_random']['settings']);
    }

    /**
     * Keep request and predeclared-version graphs separate in native children.
     *
     * @return array
     */
    public static function fieldtypeContexts()
    {
        return array(array('2.5.5', 'PAGE', ''), array('2.5.5', 'CP', ''), array('7.5.0', 'PAGE', ''), array('7.5.0', 'CP', ''), array('7.5.0', 'PAGE', 'predefined'));
    }

    /**
     * Load the actual procedural parent imports from each supported EE layout.
     *
     * @dataProvider fieldtypeImports
     * @param string $layout
     * @param string $version
     * @return void
     */
    public function testFieldtypeImportsFromNativeLayouts($layout, $version)
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'fieldtype-import', $this->temporaryDirectory, $version, 'PAGE', $layout));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $data = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(hash_file('sha256', dirname(__DIR__, 2) . '/Support/FieldtypeFixture.php'), $data['parent_sha256']);
        $this->assertSame(substr($version, 0, 1) === '2' ? '9' : 8, $data['channel']);
        $this->assertSame('4.1.1', $data['version']);
        $this->assertSame(0, $data['footerCount']);
    }

    /**
     * Match modern system, legacy application and installer fallback layouts.
     *
     * @return array
     */
    public static function fieldtypeImports()
    {
        return array(array('system', '7.5.0'), array('application', '2.5.5'), array('installer', '7.5.0'));
    }

    /**
     * Real updater construction performs schema verification and one license check.
     *
     * @return void
     */
    public function testUpdaterConstructorInFreshProcess()
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'updater-constructor', $this->temporaryDirectory));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('version' => '4.1.1', 'module' => 'Low_random_test', 'footerCount' => 2, 'schema' => array('add_field', 'add_key', 'add_key', 'create_table')), json_decode($result[1], true));
    }

    /**
     * Real encryption emits one pixel and script while repeated checks stay idempotent.
     *
     * @return void
     */
    public function testLicenseCheckBuildsEncryptedFooterInFreshProcess()
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'version-check', $this->temporaryDirectory));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $data = json_decode($result[1], true);
        $this->assertNull($data['first']);
        $this->assertTrue($data['second']);
        $this->assertCount(2, $data['footer']);
        $this->assertStringContainsString('<div id="low_random__up"', $data['footer'][0]);
        $this->assertStringContainsString('&#104;&#116;&#116;&#112;&#115;&#58;', $data['footer'][0]);
        $this->assertMatchesRegularExpression('/\?[a-f0-9]+"/', $data['footer'][0]);
        $this->assertStringContainsString('<script type="text/javascript">', $data['footer'][1]);
    }

    /**
     * The development switch selects a local endpoint and preserves prior footer items.
     *
     * @return void
     */
    public function testDevelopmentLicenseCheckInFreshProcess()
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'version-check', $this->temporaryDirectory, '7.5.0', 'PAGE', 'development'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $data = json_decode($result[1], true);
        $this->assertCount(3, $data['footer']);
        $this->assertSame('<script>existing()</script>', $data['footer'][0]);
        $encoded = implode('', array_map(static function ($character) { return '&#' . ord($character) . ';'; }, str_split('http://localhost:2368/update.gif')));
        $this->assertStringContainsString($encoded, $data['footer'][1]);
    }

    /**
     * Oversized payloads must fail before emitting a broken RSA license pixel.
     *
     * @return void
     */
    public function testLongLicenseSiteUrlInFreshProcess()
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'version-check', $this->temporaryDirectory, '7.5.0', 'PAGE', 'long-url'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $data = json_decode($result[1], true);
        $this->assertFalse($data['first']);
        $this->assertFalse($data['second']);
        $this->assertSame(array(), $data['footer']);
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
