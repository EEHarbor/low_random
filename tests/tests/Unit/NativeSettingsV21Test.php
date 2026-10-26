<?php

namespace Tests;

final class NativeSettingsV21Test extends TestCase
{
    /**
     * Preserve real callback values, native callee lifetime and persistence ordering.
     *
     * @dataProvider nativeContracts
     * @param string $mode Isolated native contract.
     * @param mixed $expected Original caller-visible result.
     * @return void
     */
    public function testOriginalNativeContract($mode, $expected): void
    {
        $arguments = array(dirname(__DIR__, 2) . '/Support/native-settings-v21.php', $mode);
        if ($mode === 'lifetime') {
            // Trace argument retention is separate from unwinding parameter ownership.
            array_unshift($arguments, '-d', 'zend.exception_ignore_args=1');
        }
        $result = $this->native($arguments);
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame($expected, json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Preserve a namespace mkdir implementation's updates to the actual cache property.
     *
     * @return void
     */
    public function testDirectoryHookKeepsItsPathReference(): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-cache-reference.php', $this->temporaryDirectory));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('value' => true, 'mkdir_calls' => 2, 'saved' => true, 'logs' => array()),
            json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Preserve native conversion ownership when mkdir updates a path to a Stringable.
     *
     * @return void
     */
    public function testOpaqueDirectoryPathKeepsItsNativeConversions(): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-cache-reference.php', $this->temporaryDirectory, 'opaque'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('value' => true, 'mkdir_calls' => 2, 'saved' => true, 'logs' => array(), 'conversions' => 8),
            json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Cover only actual value and ownership regressions from this compatibility fix.
     *
     * @return array
     */
    public function nativeContracts(): array
    {
        return array(
            array('native-frame', 'native'),
            'encoded-native-frame' => array('encoded-native-frame', 'native'),
            array('nan', true),
            array('snapshot', array(7, 7)),
            array('raw-key', array(null, 1.9)),
            array('array-access', array('assign', 'write', 'key-destroy', 'value-destroy', 'root-destroy')),
            array('lifetime', array(
                array('events' => array('convert', 'write', 'value-destroy'), 'errors' => array(), 'stored' => 'alc'),
                array('events' => array('convert', 'write', 'value-destroy'), 'errors' => array('database'), 'stored' => null),
                array('events' => array('convert', 'write', 'value-destroy'), 'errors' => array('destructor'), 'stored' => 'alc'),
                array('events' => array('convert', 'write', 'value-destroy'), 'errors' => array('destructor', 'database'), 'stored' => null),
            )),
        );
    }
}
