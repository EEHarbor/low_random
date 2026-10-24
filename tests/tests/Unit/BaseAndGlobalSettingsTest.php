<?php

namespace Tests;

use Low\Random\FluxCapacitor\Base\Mod;
use Low\Random\FluxCapacitor\Base\Tab;
use Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings;
use Low\Random\FluxCapacitor\FluxCapacitor;

final class BaseAndGlobalSettingsTest extends TestCase
{
    /**
     * Version helpers compare the actual major version in both directions.
     *
     * @return void
     */
    public function testVersionHelpers()
    {
        $flux = new FluxCapacitor();
        $this->assertSame('7', $flux->getEEVersion());
        $this->assertSame('7.5.0', $flux->getEEVersion(false));
        $this->assertTrue($flux->ver_lt('8'));
        $this->assertFalse($flux->ver_lt('7'));
        $this->assertTrue($flux->ver_gt('6'));
        $this->assertFalse($flux->ver_gt('7'));
        $this->assertTrue($flux->ver_lte('7'));
        $this->assertFalse($flux->ver_lte('6'));
        $this->assertTrue($flux->ver_gte('7'));
        $this->assertFalse($flux->ver_gte('8'));
        $this->assertFalse($flux->is_ee2());
        $this->assertFalse($flux->is_ee3());
        $this->assertFalse($flux->is_ee4());
        $this->assertFalse($flux->is_ee5());
    }

    /**
     * The module parameter wrapper trims encoded spaces and supports fallbacks.
     *
     * @return void
     */
    public function testModuleParameters()
    {
        $module = new class extends Mod {
            /**
             * Expose the inherited protected API without altering its behavior.
             *
             * @param string $name
             * @param mixed $fallback
             * @param bool $trim
             * @return mixed
             */
            public function parameter($name, $fallback = null, $trim = true)
            {
                return $this->getParameter($name, $fallback, $trim);
            }
        };
        $this->ee->TMPL->tagparams = array('value' => '&nbsp; hello &#32;', 'zero' => '0');
        $this->assertSame('hello', $module->parameter('value'));
        $this->assertSame('&nbsp; hello &#32;', $module->parameter('value', null, false));
        $this->assertSame('fallback', $module->parameter('missing', 'fallback'));
        $this->assertSame('fallback', $module->parameter('zero', 'fallback'));
        $this->assertSame('4.1.1', $module->version);
    }

    /**
     * Tab adapters preserve object, legacy parameter and absent entry shapes.
     *
     * @return void
     */
    public function testTabAdaptersAndEntryShapes()
    {
        $tab = new Tab();
        $this->assertSame('4.1.1', $tab->version);
        $this->assertSame(array(), $tab->display(3, 9));
        $this->assertSame(array(), $tab->publish_tabs(3));
        $this->assertTrue($tab->save((object) array('entry_id' => 9), array()));
        $this->assertTrue($tab->publish_data_db(array()));
        $expected = array('entry_id' => 9, 'channel_id' => 3, 'site_id' => 1);
        $this->assertSame($expected, $tab->getEntryVariables((object) $expected, array()));
        $this->assertSame($expected, $tab->getEntryVariables(null, array('entry_id' => 9, 'meta' => array('channel_id' => 3, 'site_id' => 1))));
        $this->assertSame(array('entry_id' => 0, 'channel_id' => 0, 'site_id' => 0), $tab->getEntryVariables(null, array()));
    }

    /**
     * Global settings round-trip legacy base64-serialized arrays.
     *
     * @return void
     */
    public function testGlobalSettingsReadAndUpdate()
    {
        $settings = new GlobalFieldSettings('custom_type');
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize(array('count' => 0, 'enabled' => false)))));
        $this->assertSame('custom_type', $settings->fieldtype_name);
        $this->assertSame(array('count' => 0, 'enabled' => false), $settings->getSettings());
        $this->assertSame(0, $settings->getSetting('count'));
        $this->assertTrue($settings->updateSetting('count', 2));
        $this->assertContains(array('update', array('fieldtypes', array('settings' => base64_encode(serialize(array('count' => 2, 'enabled' => false)))))), $this->ee->db->calls);
    }

    /**
     * No fieldtype row produces null and does not create a new settings row.
     *
     * @return void
     */
    public function testGlobalSettingsWithNoRow()
    {
        $settings = new GlobalFieldSettings();
        $this->assertSame('low_random', $settings->fieldtype_name);
        $this->assertNull($settings->getSettings());
        $this->assertNull($settings->getSetting('missing'));
        $this->assertFalse($settings->updateSetting('value', 'new'));
    }

    /**
     * Serialized false is a valid legacy empty settings value.
     *
     * @return void
     */
    public function testGlobalSettingsWithSerializedFalse()
    {
        $settings = new GlobalFieldSettings();
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize(false))));
        $this->assertSame(array(), $settings->getSettings());
        $this->assertNull($settings->getSetting('missing'));
    }

    /**
     * Missing keys in existing settings should be handled without @ warnings.
     *
     * @return void
     */
    public function testMissingGlobalSettingDoesNotEmitDiagnostics()
    {
        $settings = new GlobalFieldSettings();
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize(array('present' => 'value')))));
        $this->assertNull($settings->getSetting('missing'));
    }

    /**
     * Malformed stored settings should not emit suppressed unserialize warnings.
     *
     * @return void
     */
    public function testMalformedGlobalSettingsDoNotEmitDiagnostics()
    {
        $settings = new GlobalFieldSettings();
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode('not serialized')));
        $this->assertSame(array(), $settings->getSettings());
    }
    /**
     * Read and write ordinary array float keys with their native signed-word identity.
     *
     * @return void
     */
    public function testNativeFloatArrayOffsetsKeepStoredKeys(): void
    {
        $settings = new GlobalFieldSettings();
        foreach (array(array(1.5, 1), array(-1.5, -1), array(INF, 0), array(-INF, 0), array(NAN, 0)) as $case) {
            list($key, $native) = $case;
            $value = array($native => 'original');
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize($value))));
            $this->assertSame('original', $settings->getSetting($key));
            $this->assertTrue($settings->updateSetting($key, 'updated'));
            $update = end($this->ee->db->calls);
            $this->assertSame('update', $update[0]);
            $this->assertSame(array($native => 'updated'), unserialize(base64_decode($update[1][1]['settings'])));
        }
    }

    /**
     * Preserve native first-byte writes and a single Stringable conversion.
     *
     * @return void
     */
    public function testNativeStringValueWritesKeepTheirFirstByte(): void
    {
        $settings = new GlobalFieldSettings();
        $calls = 0;
        $stringable = new class($calls) {
            private $calls;
            /**
             * Retain the caller's conversion count.
             *
             * @param int $calls
             * @return void
             */
            public function __construct(&$calls) { $this->calls =& $calls; }
            /**
             * Supply a native string value once per write.
             *
             * @return string
             */
            public function __toString() { $this->calls++; return 'long'; }
        };
        foreach (array(array('long', 'l'), array(1234, '1'), array(1.25, '1'), array($stringable, 'l')) as $case) {
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize('abc'))));
            $this->assertTrue($settings->updateSetting(1, $case[0]));
            $update = end($this->ee->db->calls);
            $this->assertSame('update', $update[0]);
            $this->assertSame('a' . $case[1] . 'c', unserialize(base64_decode($update[1][1]['settings'])));
        }
        $this->assertSame(1, $calls);
    }

}
