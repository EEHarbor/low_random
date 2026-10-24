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

    /**
     * A missing cache under a read-only parent returns the documented fallback.
     *
     * @return void
     */
    public function testReadOnlyParentDoesNotEmitMkdirDiagnostics()
    {
        $parent = $this->temporaryDirectory . '/read-only';
        mkdir($parent, 0700);
        $paths = \Closure::bind(static function ($parent) {
            self::$cache_path = $parent . '/cache';
            self::$module_cache_path = $parent . '/cache/low_random/';
        }, null, PersistentCache::class);
        $paths($parent);
        chmod($parent, 0500);

        try {
            $this->assertFalse(PersistentCache::set('key', 'value'));
            $this->assertStringContainsString('could not create cache folder', $this->ee->logger->messages[0]);
            $this->assertDirectoryDoesNotExist($parent . '/cache');
        } finally {
            chmod($parent, 0700);
        }
    }

    /**
     * Distinct scalar and compound keys remain distinct after a disk reload.
     *
     * @return void
     */
    public function testEscapedKeyBoundariesSurviveDiskReload()
    {
        $this->assertTrue(PersistentCache::set('site:0', array('scalar')));
        $this->assertTrue(PersistentCache::set(array('site', 0), array('compound')));
        StaticCache::clear();
        $this->assertSame(array('scalar'), PersistentCache::get('site:0'));
        $this->assertSame(array('compound'), PersistentCache::get(array('site', 0)));
    }

    /**
     * Files, dangling links and missing parents keep failed cache creation quiet.
     *
     * @return void
     */
    public function testCreationObstaclesReturnFalseWithoutChangingTheFilesystem()
    {
        foreach (array('file', 'dangling-link', 'missing-parent') as $shape) {
            $path = $this->temporaryDirectory . '/' . $shape;

            if ($shape === 'file') {
                file_put_contents($path, 'keep');
            }

            if ($shape === 'dangling-link') {
                symlink($this->temporaryDirectory . '/absent-link-target', $path);
            }

            if ($shape === 'missing-parent') {
                $path .= '/cache';
            }

            $paths = \Closure::bind(static function ($path) {
                self::$cache_path = $path;
                self::$module_cache_path = $path . '/low_random/';
            }, null, PersistentCache::class);
            $paths($path);
            $this->assertFalse(PersistentCache::set('blocked', 'value'));
            $this->assertStringContainsString('could not create cache folder', end($this->ee->logger->messages));
        }

        $this->assertSame('keep', file_get_contents($this->temporaryDirectory . '/file'));
        $this->assertTrue(is_link($this->temporaryDirectory . '/dangling-link'));
    }
    /**
     * Read an existing legacy cache from disk without rewriting its native bytes.
     *
     * @return void
     */
    public function testLegacyEscapedCacheBytesRemainReadable(): void
    {
        $this->assertTrue(PersistentCache::set('legacy', 'seed'));
        $path = $this->temporaryDirectory . '/cache/low_random/' . sha1('legacy');
        $serialized = 'a:1:{S:1:"\\78";S:1:"\\41";}';
        file_put_contents($path, $serialized);
        StaticCache::clear();
        $this->assertSame(array('x' => 'A'), PersistentCache::get('legacy'));
        $this->assertSame($serialized, file_get_contents($path));
    }

    /**
     * Retain literal POSIX backslashes when testing cache directory availability.
     *
     * @return void
     */
    public function testPosixDirectoryNamesKeepLiteralBackslashes(): void
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $this->assertSame('\\', DIRECTORY_SEPARATOR);
            return;
        }

        $plain = $this->temporaryDirectory . '/cache';
        file_put_contents($plain, 'preserved file');
        $path = $plain . '\\';
        $paths = \Closure::bind(static function ($path) {
            self::$cache_path = $path;
            self::$module_cache_path = $path . '/low_random/';
        }, null, PersistentCache::class);
        $paths($path);
        $this->assertTrue(PersistentCache::set('key', 'value'));
        StaticCache::clear();
        $this->assertSame('value', PersistentCache::get('key'));
        $this->assertSame('preserved file', file_get_contents($plain));
    }

}
