<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings;

final class QuietStoredCallbackValue
{
    public static $previousHandler;
    public static $reporting = array();
    public static $balanced = false;

    /**
     * Exercise a quiet hydration callback's native handler installation.
     *
     * @return void
     */
    public function __wakeup(): void
    {
        self::$reporting[] = error_reporting();
        self::$previousHandler = set_error_handler(array(self::class, 'handle'), E_USER_NOTICE);
        if (self::$balanced) {
            restore_error_handler();
        }
    }

    /**
     * Supply a callable identity without intentionally emitting a diagnostic.
     *
     * @param int $severity Native diagnostic severity.
     * @param string $message Native diagnostic description.
     * @param string $file Original diagnostic file.
     * @param int $line Original diagnostic line.
     * @return bool Whether the diagnostic was consumed.
     */
    public static function handle($severity, $message, $file, $line): bool
    {
        return false;
    }
}

final class StoredCallbackTest extends TestCase
{
    /**
     * Preserve canonical object hydration, callable state and the restore stack.
     *
     * @dataProvider callbackModes
     * @param bool $balanced Whether the callback restores its own handler.
     * @return void
     */
    public function testQuietHydrationKeepsNativeHandlerOwnership($balanced): void
    {
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize(array(
            'object' => new QuietStoredCallbackValue(), 'keep' => 7,
        )))));
        $settings = new GlobalFieldSettings();
        $prior = static function () { return false; };
        set_error_handler($prior, E_USER_NOTICE);
        QuietStoredCallbackValue::$balanced = $balanced;
        QuietStoredCallbackValue::$reporting = array();
        $installed = array(QuietStoredCallbackValue::class, 'handle');
        try {
            $value = $settings->getSettings();
            $this->assertInstanceOf(QuietStoredCallbackValue::class, $value['object']);
            $this->assertSame(7, $value['keep']);
            $this->assertSame($prior, QuietStoredCallbackValue::$previousHandler);
            $active = set_error_handler($prior);
            restore_error_handler();
            $this->assertSame($balanced ? $prior : $installed, $active);
            $nativeMask = PHP_VERSION_ID < 80000 ? 0
                : E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
            $this->assertSame(array($nativeMask), QuietStoredCallbackValue::$reporting);
            if (!$balanced) {
                restore_error_handler();
                $restored = set_error_handler($prior);
                restore_error_handler();
                $this->assertSame($prior, $restored);
            }
        } finally {
            $active = set_error_handler($prior);
            restore_error_handler();
            if ($active === $installed) {
                restore_error_handler();
            }
            restore_error_handler();
            QuietStoredCallbackValue::$previousHandler = null;
            QuietStoredCallbackValue::$reporting = array();
            QuietStoredCallbackValue::$balanced = false;
        }
    }

    /**
     * Supply balanced and persistently installed native callback handlers.
     *
     * @return array
     */
    public function callbackModes(): array
    {
        return array('balanced' => array(true), 'installed' => array(false));
    }
    /**
     * Keep original namespace hooks' raw arguments and both reference mutations.
     *
     * @return void
     */
    public function testOriginalSettingsHookReferencesRemainVisible(): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-settings-references.php'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $expected = array();
        foreach (array('GlobalFieldSettings', 'EE2', 'EE3', 'EE4', 'EE5') as $kind) {
            $expected[] = array('kind' => $kind, 'keep' => 7, 'carrier' => 'carrier-mutated',
                'wire' => 'wire-mutated', 'seen' => array('raw-hook-wire'));
        }
        $this->assertSame($expected, json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Reject only unknown native first opcodes without attempting object hydration.
     *
     * @return void
     */
    public function testSettingsInvalidPrefixCannotHydrateEmbeddedObjects(): void
    {
        $object = serialize(new QuietStoredCallbackValue());
        foreach (array('not serialized', 'malformed', 'x' . $object, "\0" . $object, ' ' . $object) as $wire) {
            QuietStoredCallbackValue::$reporting = array();
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($wire)));
            $this->assertSame(array(), (new GlobalFieldSettings())->getSettings());
            $this->assertSame(array(), QuietStoredCallbackValue::$reporting);
        }
    }

    /**
     * Retain recognized native roots and primitive/object reference identities.
     *
     * @return void
     */
    public function testSettingsPrefixGuardPreservesRecognizedNativeValues(): void
    {
        $settings = new GlobalFieldSettings();
        $values = array('N;' => array(), 'b:0;' => array(), 'b:1;' => true, 'i:+7;' => 7,
            'd:1.5;' => 1.5, 's:3:"abc";' => 'abc', 'S:3:"abc";' => 'abc',
            'a:1:{s:4:"keep";i:7;}' => array('keep' => 7), 'O:8:"stdClass":0:{}' => new \stdClass());
        foreach ($values as $wire => $expected) {
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($wire)));
            $this->assertEquals($expected, $settings->getSettings());
        }
        $normalizer = require 'phar://' . dirname(__DIR__, 3) . '/low_random/low_random.phar/FluxCapacitor/Conduit/NativeSettings.php';
        // These byte-only headers must remain available to native context and class resolution.
        foreach (array('R:1;', 'r:1;', 'C:1:"X":0:{}', 'E:9:"Kind:Case";') as $wire) {
            $this->assertSame($wire, $normalizer->normalize($wire, true));
        }
        $primitive = 'kept';
        $object = new \stdClass();
        $value = array('first' => &$primitive, 'second' => &$primitive, 'object' => $object, 'alias' => $object);
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize($value))));
        $stored = $settings->getSettings();
        $stored['first'] = 'changed';
        $this->assertSame('changed', $stored['second']);
        $this->assertSame($stored['object'], $stored['alias']);
    }

}
