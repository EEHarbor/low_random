<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings;

final class SettingsComputedAccessFixture extends \ArrayObject
{
    public static $reporting = array();
    /**
     * Supply an object's computed default even when offsetExists is false.
     *
     * @param mixed $key Requested offset.
     * @return string
     */
    public function offsetGet($key): string
    {
        self::$reporting[] = error_reporting();
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
     * Retain the original native reporting context for custom getter callbacks.
     *
     * @return void
     */
    public function testArrayAccessGetterReportingContextIsPreserved(): void
    {
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize(new SettingsComputedAccessFixture(array('saved' => 'value'))))));
        $expected = @error_reporting();
        SettingsComputedAccessFixture::$reporting = array();
        try {
            $this->assertSame('value', (new GlobalFieldSettings())->getSetting('saved'));
            $this->assertSame(array($expected), SettingsComputedAccessFixture::$reporting);
        } finally {
            SettingsComputedAccessFixture::$reporting = array();
        }
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
        $compatibility = require 'phar://' . dirname(__DIR__, 3) . '/low_random/low_random.phar/FluxCapacitor/Conduit/NativeSettings.php';
        $this->assertSame($serialized, $compatibility->normalize($serialized));
    }

    /**
     * Hydrate canonical nested objects exactly once before returning their stored values.
     *
     * @return void
     */
    public function testCanonicalStoredTailsPreserveOnceOnlyCallbacks(): void
    {
        foreach (array('s:3:"bad";', 'S:3:"bad";') as $tail) {
            SettingsDeferredCallbackFixture::$calls = 0;
            $serialized = 'a:2:{i:0;' . serialize(new SettingsDeferredCallbackFixture()) . 'i:1;' . $tail . '}';
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($serialized)));
            $value = (new GlobalFieldSettings())->getSettings();
            $this->assertInstanceOf(SettingsDeferredCallbackFixture::class, $value[0]);
            $this->assertSame('bad', $value[1]);
            $this->assertSame(1, SettingsDeferredCallbackFixture::$calls);
        }
    }

    /**
     * Preserve an ordinary callback exception raised by valid nested hydration.
     *
     * @return void
     * @throws \UnexpectedValueException
     */
    public function testCanonicalStoredTailDoesNotSwallowObjectExceptions(): void
    {
        $serialized = 'a:2:{i:0;' . serialize(new SettingsHydrationExceptionFixture()) . 'i:1;S:3:"bad";}';
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
     * Preserve quiet native integer boundaries as stored scalar and nested values.
     *
     * @return void
     */
    public function testNativeIntegerBoundariesRemainReadable(): void
    {
        $settings = new GlobalFieldSettings();
        foreach (array((string) PHP_INT_MAX => PHP_INT_MAX, (string) PHP_INT_MIN => PHP_INT_MIN) as $integer => $expected) {
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
        $cases = array(array(0, 'Zext'), array('0', 'Zext'), array(-1, 'texZ'), array('-1', 'texZ'), array(5, 'text Z'), array('+1', 'tZxt'), array('01', 'tZxt'), array(' 1', 'tZxt'),
            array(-4, 'Zext'), array('-04', 'Zext'), array('+00', 'Zext'), array('0001', 'tZxt'), array("\t1", 'tZxt'), array("\n1", 'tZxt'));
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

    }

    /**
     * Preserve canonical callbacks at valid zero and leading-zero length boundaries.
     *
     * @return void
     */
    public function testValidSerializedLengthBoundariesPreserveCallbacks(): void
    {
        $tails = array('s:0:"";' => '', 'S:0:"";' => '', 's:0003:"bad";' => 'bad', 'S:0003:"bad";' => 'bad');
        foreach ($tails as $tail => $expected) {
            SettingsDeferredCallbackFixture::$calls = 0;
            $serialized = 'a:2:{i:0;' . serialize(new SettingsDeferredCallbackFixture()) . 'i:1;' . $tail . '}';
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($serialized)));
            $value = (new GlobalFieldSettings())->getSettings();
            $this->assertInstanceOf(SettingsDeferredCallbackFixture::class, $value[0]);
            $this->assertSame($expected, $value[1]);
            $this->assertSame(1, SettingsDeferredCallbackFixture::$calls);
        }
    }

    /**
     * Preserve native null array keys and quiet optional-setting misses.
     *
     * @return void
     */
    public function testNullArrayKeysAndMissingSettingsRemainNative(): void
    {
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize(array('' => 'empty', 'nullable' => null)))));
        $settings = new GlobalFieldSettings();
        $this->assertSame('empty', $settings->getSetting(null));
        $this->assertNull($settings->getSetting('missing'));
        $this->assertNull($settings->getSetting('nullable'));
        $this->assertTrue($settings->updateSetting(null, 'written'));
        $this->assertContains(array('update', array('fieldtypes', array('settings' => base64_encode(serialize(array('' => 'written', 'nullable' => null)))))), $this->ee->db->calls);
    }

    /**
     * Convert native first-byte replacement values exactly once before assignment.
     *
     * @return void
     */
    public function testNativeFirstByteConversionsRemainQuiet(): void
    {
        $object = new class {
            public $calls = 0;
            /** Convert the native replacement once. @return string */
            public function __toString() { $this->calls++; return 'long'; }
        };
        $resource = fopen('php://temp', 'w+');
        $closed = fopen('php://temp', 'w+');
        fclose($closed);
        try {
            foreach (array(array(NAN, 'aNc'), array(123, 'a1c'), array($object, 'alc'), array($resource, 'aRc'), array($closed, 'aRc')) as $case) {
                $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize('abc'))));
                $this->ee->db->calls = array();
                $this->assertTrue((new GlobalFieldSettings())->updateSetting(1, $case[0]));
                $this->assertContains(array('update', array('fieldtypes', array('settings' => base64_encode(serialize($case[1]))))), $this->ee->db->calls);
            }
            $this->assertSame(1, $object->calls);
        } finally {
            fclose($resource);
        }
    }

    /**
     * Execute legal child declarations and namespace hooks against the shipped members.
     *
     * @return void
     */
    public function testLegacyMethodAndNativeSymbolSurfacesRemainIntact(): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-settings-api.php'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('read' => 'thousand', 'persisted' => array(0 => 'zero', 1000 => 'written', -23 => 'wrapped'),
            'escaped' => 'abc', 'normalized' => 's:3:"abc";', 'new_native_dispatches' => 0, 'original_decoder_dispatches' => 4,
            'original_base64_dispatches' => 3, 'cache_write' => true, 'cache_read' => 'kept', 'legacy_cache_bytes' => serialize('kept'),
            'optional_update' => true, 'consumer_methods' => array_fill(0, 5, 'consumer')),
            json_decode($result[1], true));
    }

    /**
     * Dispatch raw carrier and decoder hooks in each original caller namespace.
     *
     * @return void
     */
    public function testOriginalSettingsAndCacheHookInputsRemainUntouched(): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-settings-hooks.php'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $expected = array();
        foreach (array('GlobalFieldSettings', 'EE2', 'EE3', 'EE4', 'EE5') as $kind) {
            $scope = $kind === 'GlobalFieldSettings' ? 'conduit' : 'bridge';
            foreach (array('carrier-text', null) as $carrier) {
                $expected[] = array('kind' => $kind, 'carrier' => $carrier, 'hook' => $scope,
                    'calls' => array(array($scope . '-base64', $carrier), array($scope . '-unserialize', 'S:3:"abc";')));
            }
        }
        $expected[] = array('kind' => 'cache', 'hook' => 'conduit', 'calls' => array(array('conduit-unserialize', 'S:3:"abc";')));
        $this->assertSame($expected, json_decode($result[1], true));
    }

    /**
     * Retain the native encoded Stringable carrier and its single conversion.
     *
     * @return void
     */
    public function testNativeEncodedCarrierConversionRemainsOnceOnly(): void
    {
        $carrier = new class {
            public $calls = 0;
            /** Supply the native encoded fieldtype row. @return string */
            public function __toString() { $this->calls++; return base64_encode(serialize(array('value' => 'kept'))); }
        };
        $this->ee->db->results['fieldtypes'] = array(array('settings' => $carrier));
        $this->assertSame(array('value' => 'kept'), (new GlobalFieldSettings())->getSettings());
        $this->assertSame(1, $carrier->calls);
    }

    /**
     * Decode actual bytes from a documented string-returning base64 helper.
     *
     * @dataProvider decodedCarrierFormats
     * @param string $format
     * @return void
     */
    public function testNativeDecodedCarrierConversionRemainsOnceOnly($format): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-settings-hooks.php',
            dirname(__DIR__, 3) . '/low_random/low_random.phar', 'decoded', $format));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $expected = array();
        foreach (array('GlobalFieldSettings', 'EE2', 'EE3', 'EE4', 'EE5') as $kind) {
            $scope = $kind === 'GlobalFieldSettings' ? 'conduit' : 'bridge';
            $wire = serialize(array('wire' => 'native'));
            if ($format === 'escaped') { $wire = 'a:1:{s:4:"wire";S:6:"native";}'; }
            if ($format === 'prefix') { $wire .= 'ignored remainder'; }
            foreach (array(base64_encode($wire), null) as $carrier) {
                $expected[] = array('kind' => $kind, 'carrier' => $carrier, 'hook' => $carrier === null ? null : 'native',
                    'calls' => array(array($scope . '-base64', $carrier)));
            }
        }
        $this->assertSame($expected, json_decode($result[1], true));
    }

    /**
     * Retain native accepted wire formats after the carrier's single conversion.
     *
     * @return array
     */
    public static function decodedCarrierFormats(): array
    {
        return array('canonical' => array('canonical'), 'escaped' => array('escaped'), 'prefix' => array('prefix'));
    }

    /**
     * Preserve the original callee selection across callbacks and inherited calls.
     *
     * @dataProvider lateHookCallers
     * @param string $kind
     * @param string $timing
     * @return void
     */
    public function testNativeCalleeBindingLifetime($kind, $timing): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-settings-late-hooks.php',
            dirname(__DIR__, 3) . '/low_random/low_random.phar', $kind, $timing));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $expected = array('values' => array(7, 7, 7), 'hook_calls' => 0);
        if ($timing === 'initial') {
            $expected = array('values' => array(88, 88, 88), 'hook_calls' => 6);
        }
        if ($timing === 'no-row') {
            $expected = array('values' => array(null, 88, 88), 'hook_calls' => 4);
        }
        $this->assertSame($expected, json_decode($result[1], true));
    }

    /**
     * Exercise each original lexical reader independently of subclass storage.
     *
     * @return array
     */
    public static function lateHookCallers(): array
    {
        $cases = array();
        foreach (array('GlobalFieldSettings', 'EE2', 'EE3', 'EE4', 'EE5') as $kind) {
            foreach (array('row', 'between', 'inherited', 'initial', 'no-row') as $timing) {
                $cases[$kind . ' ' . $timing] = array($kind, $timing);
            }
        }
        return $cases;
    }

    /**
     * Retain the raw cache parser binding across later namespace registration.
     *
     * @return void
     */
    public function testNativeCacheCalleeBindingLifetime(): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-settings-hooks.php',
            dirname(__DIR__, 3) . '/low_random/low_random.phar', 'cache-lifetime'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('values' => array(7, 7, 7), 'hook_calls' => 0), json_decode($result[1], true));
    }

    /**
     * Release original temporary carriers before native object callbacks run.
     *
     * @dataProvider nativeTemporaryCarriers
     * @param string $kind
     * @param string $mode
     * @return void
     */
    public function testNativeTemporaryCarrierOwnership($kind, $mode): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-settings-ownership.php',
            dirname(__DIR__, 3) . '/low_random/low_random.phar', $kind, $mode));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $trace = $mode === 'encoded' ? array('row', 'convert', 'release', 'hydrate') : array('row', 'base64', 'convert', 'release', 'hydrate');
        $this->assertSame(array('kept' => 7, 'trace' => $trace, 'reporting' => array_fill(0, 3, @error_reporting())), json_decode($result[1], true));
    }

    /**
     * Exercise both native carrier temporaries in each original reader.
     *
     * @return array
     */
    public static function nativeTemporaryCarriers(): array
    {
        $cases = array();
        foreach (array('GlobalFieldSettings', 'EE2', 'EE3', 'EE4', 'EE5') as $kind) {
            foreach (array('encoded', 'decoded') as $mode) {
                $cases[$kind . ' ' . $mode] = array($kind, $mode);
            }
        }
        return $cases;
    }

    /**
     * Convert a native raw cache reader operand once before using its value.
     *
     * @return void
     */
    public function testRawCacheCarrierConversionRemainsOnceOnly(): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-settings-hooks.php',
            dirname(__DIR__, 3) . '/low_random/low_random.phar', 'cache-carrier'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('wire' => 'native', 'calls' => array(array('cache-stringify'))), json_decode($result[1], true));
    }

}
