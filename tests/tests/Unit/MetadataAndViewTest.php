<?php

namespace Tests;

final class MetadataAndViewTest extends TestCase
{
    /**
     * Keep the public addon metadata aligned with its shipped manifest.
     *
     * @return void
     */
    public function testSetupReturnsManifestMetadata()
    {
        $metadata = require PATH_THIRD . 'low_random/addon.setup.php';
        $this->assertSame(array(
            'name' => 'Low Random', 'description' => 'Generates randomness', 'version' => '4.1.1',
            'namespace' => 'Low\\Random', 'author' => 'EEHarbor', 'author_url' => 'http://eeharbor.com/low_random',
            'docs_url' => 'http://eeharbor.com/low_random/documentation', 'settings_exist' => false,
        ), $metadata);
    }

    /**
     * License views preserve helper contracts for both legacy and current EE.
     *
     * @return void
     */
    public function testLicenseViewsRenderVersionAndIgnoredLicenseStates()
    {
        $GLOBALS['low_random_services']['CP/Alert'] = new class {
            /**
             * Return the configured inline alert markup.
             *
             * @return string
             */
            public function getAllInlines() { return '<p>inline-alert</p>'; }
        };

        foreach (array(PATH_THIRD . 'low_random/views/license.php', 'phar://' . PATH_THIRD . 'low_random/low_random.phar/Views/license.php') as $view) {
            foreach (array(array(2, 'real-<key>', false), array(7, 'ignore-site-1', true)) as $state) {
                $GLOBALS['low_random_forms'] = array();
                list($ee_ver, $license_key, $ignore_site) = $state;
                $action_url = '/cp/addons/settings/low_random/license';
                ob_start();

                try {
                    include $view;
                    $html = ob_get_contents();
                } finally {
                    ob_end_clean();
                }

                $this->assertStringContainsString('ee' . $ee_ver . ' addon-license', $html);
                $this->assertStringContainsString('License Missing Production Domain', $html);
                $this->assertSame(array('open', array($action_url, array('class' => 'settings panel-body'))), $GLOBALS['low_random_forms'][0]);
                $this->assertSame(array('input', array('license_key', $ignore_site ? '' : $license_key)), $GLOBALS['low_random_forms'][1]);
                $this->assertSame(array('checkbox', array('ignore_site', '1', $ignore_site)), $GLOBALS['low_random_forms'][2]);
                $this->assertSame(array('close', array()), $GLOBALS['low_random_forms'][3]);
                $this->assertSame($ee_ver > 2, strpos($html, 'inline-alert') !== false);
            }
        }
    }

    /**
     * The inherited plugin info API must return metadata without using $this.
     *
     * @return void
     */
    public function testStaticPluginInfoReturnsMetadata()
    {
        $info = \Low_random::info();
        $this->assertIsArray($info);
        $this->assertSame('Low Random', $info['pi_name']);
    }

    /**
     * Unrelated classes must be ignored by the add-on's autoloaders.
     *
     * @return void
     */
    public function testAutoloaderIgnoresOtherNamespaces()
    {
        $this->assertFalse(class_exists('OtherVendor\\Unrelated'));
    }

    /**
     * A namespaced missing class reports its exact archive location.
     *
     * @return void
     */
    public function testMissingArchiveClassReportsUsefulLocation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('FluxCapacitor/NotFound.php not found.');
        class_exists('Low\\Random\\FluxCapacitor\\NotFound');
    }

    /**
     * The diagnostic handler also sees deprecations suppressed with @.
     *
     * @return void
     */
    public function testDiagnosticsCatchSuppressedDeprecation()
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('diagnostic harness probe');
        \Tests\Support\Diagnostics::probe();
    }

    /**
     * Short control-panel URIs should not index missing segments under @.
     *
     * @return void
     */
    public function testShortUriDoesNotEmitDiagnostics()
    {
        $bridge = new \Low\Random\FluxCapacitor\Bridge\EE5(array('module' => 'low_random', 'module_name' => 'Low Random'));
        $this->ee->uri = new class {
            /**
             * Return the current control-panel URI.
             *
             * @return string
             */
            public function uri_string() { return 'cp'; }
        };
        $this->assertSame('index', $bridge->getCurrentUrlInfo('method'));
    }
}
