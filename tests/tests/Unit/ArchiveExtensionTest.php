<?php

namespace Tests;

use Low\Random\FluxCapacitor\Base\Ext;

final class ArchiveExtensionTest extends TestCase
{
    /**
     * Expose the shipped compatibility adapters without replacing their logic.
     *
     * @return Ext
     */
    private function extension()
    {
        return new class extends Ext {
            /**
             * Call the legacy registration API with every caller argument.
             *
             * @param string $method
             * @param string|null $hook
             * @param int $priority
             * @param string $enabled
             * @return bool
             */
            public function registerLegacy($method, $hook = null, $priority = 10, $enabled = 'y')
            {
                return $this->register_extension($method, $hook, $priority, $enabled);
            }

            /**
             * Expose current and legacy unregistration for contract comparison.
             *
             * @param string $method
             * @param string|null $hook
             * @param bool $legacy
             * @return bool
             */
            public function unregister($method, $hook = null, $legacy = false)
            {
                return $legacy ? $this->unregister_extension($method, $hook) : $this->unregisterExtension($method, $hook);
            }

            /**
             * Exercise the protected version compatibility adapter.
             *
             * @return bool
             */
            public function updateLegacyVersion()
            {
                return $this->update_version();
            }
        };
    }

    /**
     * Extension metadata comes from the actual manifest and archive constructor.
     *
     * @return void
     */
    public function testConstructorLoadsExtensionMetadata()
    {
        $extension = $this->extension();
        $this->assertSame('4.1.1', $extension->version);
        $this->assertSame('Low Random', $extension->name);
        $this->assertSame('Generates randomness', $extension->description);
        $this->assertSame('http://eeharbor.com/low_random/documentation', $extension->docs_url);
        $this->assertFalse($extension->settings_exist);
    }

    /**
     * Registration persists exact custom hook settings and priority once.
     *
     * @return void
     */
    public function testRegistersCurrentHookWithSettings()
    {
        $extension = $this->extension();
        $extension->settings = array('enabled' => false, 'limit' => 0);
        $this->ee->db->calls = array();
        $this->assertTrue($extension->registerExtension('publish', 'after_publish', 20, 'n'));
        $this->assertSame(array(
            array('get_where', array('extensions', array('class' => get_class($extension), 'method' => 'publish', 'hook' => 'after_publish'))),
            array('insert', array('extensions', array(
                'class' => get_class($extension), 'method' => 'publish', 'hook' => 'after_publish',
                'settings' => serialize($extension->settings), 'priority' => 20, 'version' => '4.1.1', 'enabled' => 'n',
            ))),
        ), $this->ee->db->calls);
    }

    /**
     * Existing hooks are not inserted again, even with a non-array settings value.
     *
     * @return void
     */
    public function testExistingHookAndInvalidSettingsGuards()
    {
        $extension = $this->extension();
        $this->ee->db->results['extensions'] = array(array('extension_id' => 9));
        $extension->settings = false;
        $this->ee->db->calls = array();
        $this->assertTrue($extension->registerExtension('publish'));
        $this->assertSame(array(), $extension->settings);
        $this->assertSame(array(array('get_where', array('extensions', array('class' => get_class($extension), 'method' => 'publish', 'hook' => 'publish')))), $this->ee->db->calls);
        unset($extension->settings);
        $this->assertTrue($extension->registerExtension('publish', 'after_publish'));
        $this->assertSame(array(), $extension->settings);
    }

    /**
     * Activation and update adapters register hooks before writing their version.
     *
     * @return void
     */
    public function testActivationAndVersionAdapters()
    {
        $extension = $this->extension();
        $extension->hooks = array('publish', 'delete');
        $this->ee->db->calls = array();
        $this->assertTrue($extension->activate_extension());
        $methods = array_map(static function ($call) { return $call[0]; }, $this->ee->db->calls);
        $this->assertSame(array('get_where', 'insert', 'get_where', 'insert', 'update'), $methods);
        $this->assertSame(array('update', array('extensions', array('version' => '4.1.1'), array('class' => get_class($extension)))), end($this->ee->db->calls));
        $extension->hooks = array();
        $this->ee->db->calls = array();
        $this->assertTrue($extension->update_extension('4.0.0'));
        $this->assertSame(array('update', 'update'), array_column($this->ee->db->calls, 0));
        $this->assertTrue($extension->updateLegacyVersion());
    }

    /**
     * Disabling removes only this extension's hooks and optional menu item.
     *
     * @return void
     */
    public function testDisableAndCurrentUnregistrationStayScoped()
    {
        $extension = $this->extension();
        $this->ee->db->calls = array();
        $this->assertTrue($extension->disable_extension());
        $this->assertSame(array(
            array('where', array('class', get_class($extension))), array('delete', array('extensions')),
            array('where', array('data', get_class($extension))), array('delete', array('menu_items')),
        ), $this->ee->db->calls);
        $this->ee->db->calls = array();
        $this->assertTrue($extension->unregister('publish'));
        $this->assertTrue($extension->unregister('publish', 'after_publish'));
        $this->assertSame(array(
            array('delete', array('extensions', array('class' => get_class($extension), 'method' => 'publish', 'hook' => 'publish'))),
            array('delete', array('extensions', array('class' => get_class($extension), 'method' => 'publish', 'hook' => 'after_publish'))),
        ), $this->ee->db->calls);
    }

    /**
     * A page request does not mutate control-panel navigation.
     *
     * @return void
     */
    public function testPageRequestMenuGuard()
    {
        $extension = $this->extension();
        $this->assertTrue($extension->cp_custom_menu(new \stdClass()));
    }

    /**
     * The legacy registration adapter must preserve every supplied argument.
     *
     * @return void
     */
    public function testLegacyRegistrationPreservesCallerArguments()
    {
        $extension = $this->extension();
        $this->ee->db->calls = array();
        $this->assertTrue($extension->registerLegacy('publish', 'after_publish', 20, 'n'));
        $insert = end($this->ee->db->calls);
        $this->assertSame(array('hook' => 'after_publish', 'priority' => 20, 'enabled' => 'n'), array_intersect_key($insert[1][1], array_flip(array('hook', 'priority', 'enabled'))));
    }

    /**
     * The legacy unregistration adapter must remove the caller's custom hook.
     *
     * @return void
     */
    public function testLegacyUnregistrationPreservesCallerHook()
    {
        $extension = $this->extension();
        $this->ee->db->calls = array();
        $this->assertTrue($extension->unregister('publish', 'after_publish', true));
        $this->assertSame('after_publish', $this->ee->db->calls[0][1][1]['hook']);
    }
}
