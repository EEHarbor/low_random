<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\PersistentCache;
use Low\Random\FluxCapacitor\Conduit\StaticCache;

final class PersistentCacheTest extends TestCase
{
    private $previousPaths;

    /**
     * Redirect only this source class's cache paths into the unique test folder.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        class_exists(PersistentCache::class);
        $changePaths = \Closure::bind(static function ($cachePath, $modulePath) {
            $previous = array(self::$cache_path, self::$module_cache_path);
            self::$cache_path = $cachePath;
            self::$module_cache_path = $modulePath;
            return $previous;
        }, null, PersistentCache::class);
        $this->previousPaths = $changePaths($this->temporaryDirectory . '/cache', $this->temporaryDirectory . '/cache/low_random/');
        $this->ee->logger = new class {
            public $messages = array();
            /**
             * Record a developer log message.
             *
             * @param string $message
             * @return void
             */
            public function developer($message) { $this->messages[] = $message; }
        };
    }

    /**
     * Restore private static path state before removing this test's files.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $restore = \Closure::bind(static function ($paths) {
            self::$cache_path = $paths[0];
            self::$module_cache_path = $paths[1];
        }, null, PersistentCache::class);
        $restore($this->previousPaths);
        parent::tearDown();
    }

    /**
     * Persist serialized arrays and restore them after clearing in-memory state.
     *
     * @return void
     */
    public function testDiskRoundTripAndDelete()
    {
        $value = array('zero' => 0, 'false' => false, 'null' => null, 'nested' => array('value'));
        $this->assertTrue(PersistentCache::set('site-one', $value));
        $this->assertTrue(PersistentCache::has('site-one'));
        $path = $this->temporaryDirectory . '/cache/low_random/' . sha1('site-one');
        $this->assertSame(serialize($value), file_get_contents($path));
        StaticCache::clear();
        $this->assertSame($value, PersistentCache::get('site-one'));
        $this->assertTrue(PersistentCache::delete('site-one'));
        $this->assertFalse(PersistentCache::has('site-one'));
        $this->assertTrue(PersistentCache::delete('site-one'));
    }

    /**
     * Documented composite keys must also round-trip through the static tier.
     *
     * @return void
     */
    public function testCompositeKeyRoundTrip()
    {
        $this->assertTrue(PersistentCache::set(array('site', 'one'), array('value')));
        $this->assertSame(array('value'), PersistentCache::get(array('site', 'one')));
    }

    /**
     * A miss logs the exact missing file and returns false.
     *
     * @return void
     */
    public function testMissingFileReturnsFalseAndLogs()
    {
        $this->assertFalse(PersistentCache::get('missing'));
        $this->assertStringContainsString('could not find cache for:', $this->ee->logger->messages[0]);
        $this->assertSame(array('logger'), $this->ee->load->libraries);
    }

    /**
     * Clear removes only this module's files and associated static entries.
     *
     * @return void
     */
    public function testClearPreservesUnrelatedStaticCache()
    {
        $this->assertTrue(PersistentCache::set('first', 'one'));
        $this->assertTrue(PersistentCache::set('second', 'two'));
        StaticCache::set('unrelated', 'keep');
        mkdir($this->temporaryDirectory . '/cache/low_random/subfolder');
        $this->assertTrue(PersistentCache::clear());
        $this->assertFalse(PersistentCache::has('first'));
        $this->assertFalse(PersistentCache::has('second'));
        $this->assertSame('keep', StaticCache::get('unrelated'));
        $this->assertTrue(is_dir($this->temporaryDirectory . '/cache/low_random/subfolder'));
    }

    /**
     * The documented static tier should serve a previously stored value.
     *
     * @return void
     */
    public function testStaticTierSurvivesRemovalOfDiskFile()
    {
        $this->assertTrue(PersistentCache::set('key', array('value')));
        unlink($this->temporaryDirectory . '/cache/low_random/' . sha1('key'));
        $this->assertSame(array('value'), PersistentCache::get('key'));
    }
}
