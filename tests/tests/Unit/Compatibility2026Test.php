<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings;

final class SettingsComputedAccessFixture extends \ArrayObject
{
    /**
     * Supply an object's computed default even when offsetExists is false.
     *
     * @param mixed $key Requested offset.
     * @return string
     */
    public function offsetGet($key): string
    {
        return parent::offsetExists($key) ? parent::offsetGet($key) : 'computed-default';
    }
}

final class SettingsHydrationExceptionFixture
{
    /**
     * Preserve an ordinary object exception through native settings hydration.
     *
     * @return void
     * @throws \UnexpectedValueException
     */
    public function __wakeup(): void
    {
        throw new \UnexpectedValueException('owned hydration exception');
    }
}

final class SettingsDeferredCallbackFixture
{
    public static $calls = 0;

    /**
     * Record deferred native hydration cleanup without altering its result.
     *
     * @return void
     */
    public function __wakeup(): void
    {
        self::$calls++;
    }
}

final class Compatibility2026Test extends TestCase
{
    /**
     * Preserve native nested object values and reference identity in settings.
     *
     * @return void
     */
    public function testNativeObjectSettingsRemainReadableAndWritable(): void
    {
        $object = (object) array('value' => 'stored');
        $value = array('object' => $object, 'alias' => $object);
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize($value))));
        $settings = new GlobalFieldSettings();
        $stored = $settings->getSettings();
        $this->assertEquals($value, $stored);
        $this->assertSame($stored['object'], $stored['alias']);
        $this->assertTrue($settings->updateSetting('next', 'new'));
        $value['next'] = 'new';
        $data = array('settings' => base64_encode(serialize($value)));
        $this->assertContains(array('update', array('fieldtypes', $data)), $this->ee->db->calls);
    }

    /**
     * Keep valid native scalar roots and ArrayAccess containers readable.
     *
     * @return void
     */
    public function testNativeSettingsRootsKeepTheirHistoricalShapes(): void
    {
        $settings = new GlobalFieldSettings();

        foreach (array('text', 42, true, new \ArrayObject(array('count' => 0))) as $value) {
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize($value))));
            $this->assertEquals($value, $settings->getSettings());
        }

        $this->assertSame(0, $settings->getSetting('count'));
        $this->assertTrue($settings->updateSetting('count', 1));
    }

    /**
     * Delegate valid noncanonical numeric and duplicate-key syntax to PHP.
     *
     * @return void
     */
    public function testNativeSerializedSyntaxDoesNotAcquireNewRestrictions(): void
    {
        $settings = new GlobalFieldSettings();

        foreach (array('a:1:{i:+1;s:1:"x";}', 'a:1:{i:01;s:1:"x";}', 'a:1:{i:0;d:+1;}', 'a:2:{i:0;s:1:"a";i:0;s:1:"b";}') as $serialized) {
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($serialized)));
            $this->assertSame(unserialize($serialized), $settings->getSettings());
        }
    }

    /**
     * A malformed parser fallback restores the exact previously installed handler.
     *
     * @return void
     */
    public function testParserFallbackRestoresTheStrictHandler(): void
    {
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode('malformed')));
        $this->assertSame(array(), (new GlobalFieldSettings())->getSettings());
        $handler = set_error_handler(static function () { return false; });

        try {
            $this->assertSame(array(\LowRandomDiagnostics::class, 'handle'), $handler);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * An object hydration exception cannot be mistaken for malformed storage.
     *
     * @return void
     */
    public function testObjectHydrationExceptionPropagates(): void
    {
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize(new SettingsHydrationExceptionFixture()))));
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('owned hydration exception');
        (new GlobalFieldSettings())->getSettings();
    }

    /**
     * Preserve direct custom offset reads rather than imposing isset semantics.
     *
     * @return void
     */
    public function testArrayAccessComputedDefaultsRemainVisible(): void
    {
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize(new SettingsComputedAccessFixture(array('saved' => 'value'))))));
        $settings = new GlobalFieldSettings();
        $this->assertSame('value', $settings->getSetting('saved'));
        $this->assertSame('computed-default', $settings->getSetting('missing'));
    }
    /**
     * Keep native class extensions free to choose their existing data visibility.
     *
     * @return void
     */
    public function testLegacyControllerDataVisibilityRemainsExtensible(): void
    {
        $public = new class extends \Low\Random\FluxCapacitor\Base\Mcp {
            public $data = array('public');
        };
        $protected = new class extends \Low\Random\FluxCapacitor\Base\Mcp {
            protected $data = array('protected');
        };
        $private = new class extends \Low\Random\FluxCapacitor\Base\Mcp {
            private $data = array('private');
        };
        $this->assertSame(array('public'), $public->data);
        $this->assertSame(array('protected'), (new \ReflectionClass($protected))->getDefaultProperties()['data']);
        $this->assertSame(array('private'), (new \ReflectionClass($private))->getDefaultProperties()['data']);
    }

    /**
     * Read legacy escaped strings without restricting native signed object counts.
     *
     * @return void
     */
    public function testLegacyEscapedSettingsKeepNativeValues(): void
    {
        $settings = new GlobalFieldSettings();
        $vectors = array(
            'S:1:"\\41";' => 'A',
            'a:1:{S:1:"\\78";S:1:"\\41";}' => array('x' => 'A'),
            'O:8:"stdClass":+1:{s:1:"x";S:1:"\\41";}' => (object) array('x' => 'A'),
            'O:08:"stdClass":+01:{s:1:"x";S:1:"\\41";}' => (object) array('x' => 'A'),
            'a:1:{i:0;S:1:"\\41";}trailing' => array('A'),
            'a:1:{i:0;s:4:"S:x;";}' => array('S:x;'),
        );

        foreach ($vectors as $serialized => $expected) {
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($serialized)));
            $this->assertEquals($expected, $settings->getSettings());
        }
    }

    /**
     * Leave malformed opaque custom payloads for the native parser to diagnose.
     *
     * @return void
     */
    public function testNegativeOpaquePayloadLengthDoesNotEnterTheLexer(): void
    {
        $serialized = 'C:1:"X":-999:{S:}';
        $this->assertSame($serialized, GlobalFieldSettings::normalizeEscapedStrings($serialized));
    }

    /**
     * Let native parsing run deferred object callbacks before malformed fallback.
     *
     * @return void
     */
    public function testMalformedStoredTailsPreserveDeferredCallbacks(): void
    {
        foreach (array('s:9:"bad";', 'S:9:"bad";') as $tail) {
            SettingsDeferredCallbackFixture::$calls = 0;
            $serialized = 'a:2:{i:0;' . serialize(new SettingsDeferredCallbackFixture()) . 'i:1;' . $tail . '}';
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($serialized)));
            $this->assertSame(array(), (new GlobalFieldSettings())->getSettings());
            $this->assertSame(1, SettingsDeferredCallbackFixture::$calls);
        }
    }

    /**
     * Preserve an ordinary callback exception raised while native parsing unwinds.
     *
     * @return void
     * @throws \UnexpectedValueException
     */
    public function testMalformedStoredTailDoesNotSwallowObjectExceptions(): void
    {
        $serialized = 'a:2:{i:0;' . serialize(new SettingsHydrationExceptionFixture()) . 'i:1;S:9:"bad";}';
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($serialized)));
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('owned hydration exception');
        (new GlobalFieldSettings())->getSettings();
    }

    /**
     * Exercise the real extraction entry before it resolves or cleans a linked root.
     *
     * @return void
     */
    public function testExtractionEntryPreservesLinkedTargets(): void
    {
        foreach (array(array('root', 'direct'), array('container', 'direct'), array('cleanup', 'direct'), array('root', 'fallback'), array('container', 'fallback')) as $case) {
            list($mode, $entry) = $case;
            $owned = $this->temporaryDirectory . '/' . $mode . '-' . $entry;
            mkdir($owned, 0700);
            $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-extraction-entry.php', $mode, $owned, $entry));
            $this->assertSame(0, $result[0], $result[1] . $result[2]);
            $this->assertSame('', $result[2]);
            $data = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame('owned sentinel', $data['hidden']);
            $this->assertSame('owned payload', $data['ordinary']);
            $this->assertTrue($data['cwd']);
            $this->assertSame($mode !== 'cleanup', $data['link']);
            $this->assertSame($mode === 'cleanup' ? null : 'PHAR extraction ' . $mode . ' must not be a symlink.', $data['message']);
        }
    }

    /**
     * Preserve native clamped integers rather than discarding accepted stored values.
     *
     * @return void
     */
    public function testNativeClampedIntegersRemainReadable(): void
    {
        $settings = new GlobalFieldSettings();
        foreach (array('99999999999999999999999999999' => PHP_INT_MAX, '-99999999999999999999999999999' => PHP_INT_MIN) as $integer => $expected) {
            foreach (array('i:' . $integer . ';' => $expected, 'a:2:{s:4:"keep";i:7;s:1:"n";i:' . $integer . ';}' => array('keep' => 7, 'n' => $expected)) as $serialized => $value) {
                $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($serialized)));
                $this->assertSame($value, $settings->getSettings());
            }
        }
    }

    /**
     * Keep valid native byte offsets on stored strings without indexing invalid keys.
     *
     * @return void
     */
    public function testStoredStringOffsetsRemainReadable(): void
    {
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize('text'))));
        $settings = new GlobalFieldSettings();
        foreach (array(0 => 't', 1 => 'e', -1 => 't') as $key => $value) {
            $this->assertSame($value, $settings->getSetting($key));
            $this->assertSame($value, $settings->getSetting((string) $key));
        }
        $this->assertNull($settings->getSetting('missing'));
        $this->assertNull($settings->getSetting(4));
    }

    /**
     * Preserve accepted native byte writes and their exact serialized database value.
     *
     * @return void
     */
    public function testStoredStringOffsetsRemainWritable(): void
    {
        $settings = new GlobalFieldSettings();
        $cases = array(array(0, 'Zext'), array('0', 'Zext'), array(-1, 'texZ'), array('-1', 'texZ'), array(5, 'text Z'), array('+1', 'tZxt'), array('01', 'tZxt'), array(' 1', 'tZxt'));
        if (PHP_VERSION_ID >= 80000) {
            $cases[] = array('1 ', 'tZxt');
        }

        foreach ($cases as $case) {
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize('text'))));
            $this->ee->db->calls = array();
            $this->assertTrue($settings->updateSetting($case[0], 'Z'));
            $data = array('settings' => base64_encode(serialize($case[1])));
            $this->assertContains(array('update', array('fieldtypes', $data)), $this->ee->db->calls);
        }

        foreach (array(-5, 'missing', true, null, 1.0, PHP_INT_MAX) as $key) {
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize('text'))));
            $this->ee->db->calls = array();
            $this->assertFalse($settings->updateSetting($key, 'Z'));
            $updates = array_filter($this->ee->db->calls, static function ($call) { return $call[0] === 'update'; });
            $this->assertSame(array(), $updates);
        }
    }

    /**
     * Delegate oversized wire lengths to native parsing without extra cast diagnostics.
     *
     * @return void
     */
    public function testOversizedSerializedLengthsPreserveNativeCleanup(): void
    {
        $huge = '999999999999999999999999999999';
        $tails = array('S:' . $huge . ':"x";', 's:' . $huge . ':"S:";', 'O:' . $huge . ':"X":0:{S:}', 'C:' . $huge . ':"X":1:{S:}', 'C:1:"X":' . $huge . ':{S:}', 'C:1:"X":-' . $huge . ':{S:}');
        foreach ($tails as $tail) {
            SettingsDeferredCallbackFixture::$calls = 0;
            $serialized = 'a:2:{i:0;' . serialize(new SettingsDeferredCallbackFixture()) . 'i:1;' . $tail . '}';
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($serialized)));
            $this->assertSame(array(), (new GlobalFieldSettings())->getSettings());
            $this->assertSame(1, SettingsDeferredCallbackFixture::$calls);
        }
    }

}
