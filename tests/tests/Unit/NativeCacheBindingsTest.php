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

    /**
     * Preserve quiet native stream callbacks and both original mkdir binding lifetimes.
     *
     * @dataProvider directoryCallbacks
     * @param string $mode Quiet callback or original callee-resolution arrangement.
     * @param int $directories Original count of native stream directory calls.
     * @param int $hooks Original count of namespace hook calls.
     * @param array $stack Original active callable and native restore-stack sequence.
     * @return void
     */
    public function testDirectoryCallbacksKeepNativeHandlers($mode, $directories, $hooks, array $stack): void
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-virtual-cache.php', 'rootcache', $mode));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $observed = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(true, $observed['set']);
        $this->assertSame(array('keep' => 7), $observed['read']);
        $this->assertSame($directories, $observed['directories']);
        $this->assertSame($hooks, $observed['hook_calls']);
        $this->assertSame($stack, $observed['handler_stack']);
        $this->assertSame(array(), $observed['logs']);
        $this->assertSame(isset($observed['second_set']) ? 2 : 1, $observed['files']);
        if (isset($observed['second_set'])) {
            $this->assertSame(true, $observed['second_set']);
        }
    }

    /**
     * Supply quiet handler installation, balanced restoration and lazy binding cases.
     *
     * @return array
     */
    public function directoryCallbacks(): array
    {
        $base = array('rootVirtualPriorHandler', 'original');
        return array(
            'balanced' => array('balanced', 2, 0, $base),
            'install' => array('install', 2, 0, array('rootVirtualFirstHandler', 'rootVirtualPriorHandler', 'original')),
            'stack' => array('stack', 2, 0, array('rootVirtualSecondHandler', 'rootVirtualFirstHandler', 'rootVirtualPriorHandler', 'original')),
            'opaque native' => array('opaque-native', 1, 0, array('rootVirtualFirstHandler', 'rootVirtualPriorHandler', 'original')),
            'early hook' => array('early-hook', 2, 2, $base),
            'late hook' => array('late-hook', 2, 1, $base),
            'native first' => array('native-first', 4, 0, $base),
            'module first' => array('module-first', 3, 1, $base),
        );
    }

    /**
     * Preserve namespace hooks' handler installations and original by-reference paths.
     *
     * @dataProvider namespaceDirectoryOperands
     * @param string $operand Original native directory operand arrangement.
     * @return void
     */
    public function testNamespaceDirectoryCallbacksKeepNativeHandlers($operand): void
    {
        $root = dirname(__DIR__, 3);
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-cache-reference.php',
            $this->temporaryDirectory, $operand, $root . '/low_random/low_random.phar', 'handlers'));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $expected = array('value' => true, 'mkdir_calls' => 2, 'saved' => true, 'logs' => array());
        if ($operand === 'opaque') {
            $expected['conversions'] = 8;
        }
        $expected['handler_stack'] = array('referenceSecondHandler', 'referenceFirstHandler', 'referencePriorHandler', 'original');
        $this->assertSame($expected, json_decode($result[1], true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Exercise ordinary and opaque native paths mutated by a legal namespace hook.
     *
     * @return array
     */
    public function namespaceDirectoryOperands(): array
    {
        return array(array('string'), array('opaque'));
    }

}
