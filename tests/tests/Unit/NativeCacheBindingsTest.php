<?php

namespace Tests;

final class NativeCacheBindingsTest extends TestCase
{
    /**
     * Preserve original hash/implode selections across branches and native references.
     *
     * @dataProvider cacheBindings
     * @param string $kind Actual public cache producer and binding arrangement.
     * @param mixed $expected Original quiet cache retrieval result.
     * @return void
     */
    public function testOriginalCacheBindings($kind, $expected): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-cache-bindings.php', $kind, $this->temporaryDirectory));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $native = array('value' => $expected, 'calls' => 0);
        if (substr($kind, 3) === 'arity') {
            $native['arity'] = array(1);
            $native['stored'] = true;
        }
        if (substr($kind, 3) === 'references') {
            $native['pieces'] = array('alpha', 'beta');
            $native['joined'] = 'alpha:beta:observed';
        }
        $this->assertSame($native, json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Supply only original quiet callable-lifetime cases.
     *
     * @return array
     */
    public function cacheBindings(): array
    {
        return array(
            array('sc-hash', 'kept'),
            array('sc-join', 'kept'),
            array('sc-filter', 'kept'),
            array('sc-references', 'kept'),
            array('pc-hash', true),
            array('pc-join', true),
            array('pc-filter', true),
            array('pc-references', true),
            array('sc-arity', 'kept'),
            array('pc-arity', 'kept'),
        );
    }

    /**
     * Keep an existing namespace strlen from intercepting a new native capacity check.
     *
     * @return void
     */
    public function testPayloadCapacityUsesNativeLength(): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-version-length.php', $this->temporaryDirectory));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $value = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(null, $value['first']);
        $this->assertSame(true, $value['second']);
        $this->assertCount(2, $value['footer']);
        $this->assertStringContainsString('<div id="low_random__up"', $value['footer'][0]);
        $this->assertStringContainsString('<script type="text/javascript">', $value['footer'][1]);
    }

    /**
     * Permit original writable virtual storage without applying host POSIX search bits.
     *
     * @dataProvider virtualPrefixes
     * @param string $prefix Registered native stream-wrapper scheme.
     * @return void
     */
    public function testNativeWritableVirtualStorage($prefix): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-virtual-cache.php', $prefix));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $this->assertSame(array('set' => true, 'read' => array('keep' => 7), 'directories' => 2,
            'files' => 1, 'logs' => array()), json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Supply native alphabetic and numeric-leading stream schemes.
     *
     * @return array
     */
    public function virtualPrefixes(): array
    {
        return array(array('rootcache'), array('1x'));
    }

}
