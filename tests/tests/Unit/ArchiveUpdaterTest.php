<?php

namespace Tests;

final class ArchiveUpdaterTest extends TestCase
{
    /**
     * Initialize method-test state; native probes cover the constructor and its crypto side effects.
     *
     * @return \Low_random_test_upd
     */
    private function updater()
    {
        require_once dirname(__DIR__, 2) . '/Support/UpdaterFixture.php';
        $this->ee->functions = new class {
            public $cleared = array();

            /**
             * Record EE's database-cache invalidation before installation writes.
             *
             * @param string $type
             * @return void
             */
            public function clear_caching($type)
            {
                $this->cleared[] = $type;
            }
        };
        $reflection = new \ReflectionClass('Low_random_test_upd');
        $updater = $reflection->newInstanceWithoutConstructor();
        $updater->flux = new \Low\Random\FluxCapacitor\FluxCapacitor();
        $updater->version = $updater->flux->getConfig('version');
        $updater->module_name = 'Low_random_test';
        return $updater;
    }

    /**
     * Installation writes retain the initialized updater metadata and capability flags.
     *
     * @return void
     */
    public function testInstallWithInitializedUpdater()
    {
        $updater = $this->updater();
        $this->assertSame('4.1.1', $updater->version);
        $this->assertSame('Low_random_test', $updater->module_name);
        $this->ee->db->calls = array();
        $this->assertTrue($updater->install());
        $this->assertSame(array('db'), $this->ee->functions->cleared);
        $this->assertSame(array(array('insert', array('modules', array(
            'module_name' => 'Low_random_test', 'module_version' => '4.1.1',
            'has_cp_backend' => 'y', 'has_publish_fields' => 'n',
        )))), $this->ee->db->calls);
    }

    /**
     * Extension-settings mode must not register a module or clear its cache.
     *
     * @return void
     */
    public function testExtensionSettingsInstallGuard()
    {
        $updater = $this->updater();
        $updater->ext_settings = 'y';
        $this->ee->db->calls = array();
        $this->assertTrue($updater->install());
        $this->assertSame(array(), $this->ee->db->calls);
        $this->assertSame(array(), $this->ee->functions->cleared);
    }

    /**
     * Installation preserves explicit CP and publish-field capability flags.
     *
     * @return void
     */
    public function testInstallCapabilityFlags()
    {
        $updater = $this->updater();
        $updater->has_cp_backend = 'n';
        $updater->has_publish_fields = 'y';
        $this->ee->db->calls = array();
        $this->assertTrue($updater->install());
        $data = $this->ee->db->calls[0][1][1];
        $this->assertSame('n', $data['has_cp_backend']);
        $this->assertSame('y', $data['has_publish_fields']);
    }

    /**
     * Empty and equal installed versions stop before optional extension loading.
     *
     * @return void
     */
    public function testUpdateVersionGuards()
    {
        $updater = $this->updater();
        $this->ee->db->calls = array();
        $this->assertFalse($updater->update());
        $this->assertFalse($updater->update('4.1.1'));
        $this->assertSame(array(), $this->ee->db->calls);
    }

    /**
     * Uninstall removes only this module's roles, registration, actions and menu.
     *
     * @return void
     */
    public function testUninstallScopesEveryMutation()
    {
        $updater = $this->updater();
        $this->ee->db->results[''] = array(array('module_id' => 11));
        $this->ee->db->calls = array();
        $this->assertTrue($updater->uninstall());
        $this->assertSame(array(
            array('select', array('module_id')), array('from', array('modules')),
            array('where', array('module_name', 'Low_random_test')), array('get', array()),
            array('where', array('module_id', 11)), array('delete', array('module_member_roles')),
            array('where', array('module_name', 'Low_random_test')), array('delete', array('modules')),
            array('where', array('class', 'Low_random_test')), array('delete', array('actions')),
            array('where', array('class', 'Low_random_test_ext')), array('delete', array('extensions')),
            array('where', array('data', 'Low_random_test_ext')), array('delete', array('menu_items')),
        ), $this->ee->db->calls);
    }

    /**
     * A plugin without an optional extension must still update without warnings.
     *
     * @return void
     */
    public function testUpdateWithoutOptionalExtensionDoesNotEmitDiagnostics()
    {
        $this->assertTrue($this->updater()->update('4.0.0'));
    }
}
