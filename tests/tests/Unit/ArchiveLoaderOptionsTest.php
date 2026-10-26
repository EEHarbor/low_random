<?php

namespace Tests;

final class ArchiveLoaderOptionsTest extends TestCase
{
    /**
     * Load original classes under process-local OPcache settings and API limits.
     *
     * @dataProvider loaderModes
     * @param string $mode
     * @param bool $enabled
     * @param bool $cliEnabled
     * @param bool $permission
     * @param bool $apiEnabled
     * @return void
     */
    public function testNativeLoaderOptions($mode, $enabled, $cliEnabled, $permission, $apiEnabled)
    {
        $arguments = array('-d', 'opcache.enable=' . (int) $enabled, '-d', 'opcache.enable_cli=' . (int) $cliEnabled, '-d', 'opcache.validate_permission=' . (int) $permission);

        if (!$apiEnabled) {
            $arguments = array_merge($arguments, array('-d', 'disable_functions=opcache_get_configuration'));
        }

        if ($mode === 'restricted') {
            $arguments = array_merge($arguments, array('-d', 'opcache.restrict_api=' . $this->temporaryDirectory . '/api-whitelist'));
        }

        $arguments[] = dirname(__DIR__, 2) . '/Support/native-loader-options.php';
        $arguments[] = $mode;
        $result = $this->native($arguments);
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $data = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
        $available = extension_loaded('Zend OPcache');
        $this->assertTrue($data['loaded']);
        $this->assertSame('phar://' . PATH_THIRD . 'low_random/low_random.phar/FluxCapacitor/Base/Pi.php', $data['file']);
        $this->assertSame($available, $data['extension_available']);
        $this->assertSame($enabled && $available, $data['enable']);
        $this->assertSame($cliEnabled && $available, $data['enable_cli']);
        $this->assertSame($permission && $available, $data['validate_permission']);
        $this->assertSame($apiEnabled && $available, $data['configuration_available']);
        $this->assertSame($mode === 'restricted' || !$enabled || !$cliEnabled || !$available ? null : true, $data['runtime_active']);
        $this->assertSame(hash_file('sha256', PATH_THIRD . 'low_random/low_random.phar'), $data['archive_sha256']);
    }

    /**
     * Cover normal, permission-aware, absent-API and restricted-API host settings.
     *
     * @return array
     */
    public static function loaderModes()
    {
        return array(
            'default CLI' => array('default', true, false, false, true),
            'enabled CLI' => array('enabled', true, true, false, true),
            'permission validation' => array('permission', true, true, true, true),
            'globally disabled' => array('global-disabled', false, true, true, true),
            'disabled API' => array('disabled-api', true, true, true, false),
            'restricted API' => array('restricted', true, true, true, true),
        );
    }

    /**
     * A neighboring namespace must remain available to the next registered loader.
     *
     * @return void
     */
    public function testNeighboringNamespaceCanReachItsOwnLoader()
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-loader-options.php', 'neighbor'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('loaded' => true, 'calls' => array('Low\\Random\\FluxCapacitorExtra\\Widget'), 'value' => 'neighbor'), json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }
}
