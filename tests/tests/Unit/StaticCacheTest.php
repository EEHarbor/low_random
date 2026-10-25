<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\StaticCache;

final class StaticCacheTest extends TestCase
{
    /**
     * Store and retrieve objects without losing identity.
     *
     * @return void
     */
    public function testStoresValuesAndExposesHasAllClear()
    {
        $object = (object) array('value' => 'example');
        $this->assertFalse(StaticCache::get('missing'));
        $this->assertFalse(StaticCache::has('key'));
        StaticCache::put('key', $object);
        $this->assertSame($object, StaticCache::get('key'));
        $this->assertTrue(StaticCache::has('key'));
        $this->assertSame(array(sha1('key') => $object), StaticCache::all());
        $this->assertTrue(StaticCache::clear());
        $this->assertSame(array(), StaticCache::all());
    }

    /**
     * Set is an alias for put and honors an already processed key.
     *
     * @return void
     */
    public function testSetAliasAndProcessedKeys()
    {
        StaticCache::set('raw-key', array('data'), true);
        $this->assertSame(array('data'), StaticCache::get('raw-key', true));
        $this->assertTrue(StaticCache::has('raw-key', true));
        $this->assertFalse(StaticCache::has('raw-key'));
    }

    /**
     * Composite keys preserve their order and identify the same lookup.
     *
     * @return void
     */
    public function testCompositeKeysAndDeleteSideEffect()
    {
        StaticCache::put(array('site', 'one'), 'first');
        StaticCache::put(array('one', 'site'), 'second');
        $this->assertSame('first', StaticCache::get(array('site', 'one')));
        $this->assertSame('second', StaticCache::get(array('one', 'site')));
        StaticCache::delete(array('site', 'one'));
        $this->assertFalse(StaticCache::has(array('site', 'one')));
        $this->assertSame('second', StaticCache::get(array('one', 'site')));
    }

    /**
     * A stored zero is a valid cached value rather than a cache miss.
     *
     * @return void
     */
    public function testZeroValueRoundTrips()
    {
        StaticCache::put('count', 0);
        $this->assertSame(0, StaticCache::get('count'));
    }

    /**
     * Delete should report success when it removes an existing key.
     *
     * @return void
     */
    public function testDeleteReportsSuccess()
    {
        StaticCache::put('key', 'value');
        $this->assertTrue(StaticCache::delete('key'));
    }

    /**
     * Zero-valued key components must distinguish sites and record identifiers.
     *
     * @return void
     */
    public function testZeroKeyComponentDoesNotCollideWithMissingComponent()
    {
        StaticCache::put(array('site', 0), 'zero-site');
        StaticCache::put(array('site'), 'default-site');
        $this->assertSame('zero-site', StaticCache::get(array('site', 0)));
    }

    /**
     * Scalar escape bytes cannot alias newly preserved compound key pieces.
     *
     * @return void
     */
    public function testScalarAndCompoundKeyBoundariesRemainDistinct()
    {
        foreach (array(array('site:0', array('site', 0)), array('a\\:b:c', array('a:b', 'c'))) as $keys) {
            foreach (array($keys, array_reverse($keys)) as $order) {
                StaticCache::clear();
                StaticCache::set($order[0], 'first');
                StaticCache::set($order[1], 'second');
                $this->assertSame('first', StaticCache::get($order[0]));
                $this->assertSame('second', StaticCache::get($order[1]));
            }
        }
    }

    /**
     * Retain NAN key pieces without deprecated native boolean conversion.
     *
     * @return void
     */
    public function testNanKeyRemainsDistinctFromAnOmittedPiece(): void
    {
        StaticCache::put(array('topic', NAN), 'nan-key');
        StaticCache::put('topic', 'ordinary-key');
        $this->assertSame('nan-key', StaticCache::get(array('topic', NAN)));
        $this->assertSame('ordinary-key', StaticCache::get('topic'));
    }

}
