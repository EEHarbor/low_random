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
     * Files, unavailable alias targets and missing parents keep failed creation quiet.
     *
     * @return void
     */
    public function testCreationObstaclesReturnFalseWithoutChangingTheFilesystem()
    {
        foreach (array('file', 'dangling-link', 'missing-parent', 'alias-chain', 'alias-cycle') as $shape) {
            $path = $this->temporaryDirectory . '/' . $shape;

            if ($shape === 'file') {
                file_put_contents($path, 'keep');
            }

            if ($shape === 'dangling-link') {
                symlink($this->temporaryDirectory . '/absent-parent/cache', $path);
            }

            if ($shape === 'alias-chain' || $shape === 'alias-cycle') {
                $target = $this->temporaryDirectory . '/' . $shape . '-target';
                symlink($shape === 'alias-cycle' ? $path : $this->temporaryDirectory . '/absent-link-target', $target);
                symlink($target, $path);
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
     * Preserve configured absolute and relative aliases to a new cache directory.
     *
     * @return void
     */
    public function testConfiguredCacheAliasesRetainNativeDiskRoundTrips(): void
    {
        foreach (array('absolute', 'relative') as $shape) {
            $parent = $this->temporaryDirectory . '/' . $shape;
            mkdir($parent, 0700);
            $target = $parent . '/target';
            $alias = $parent . '/cache';
            symlink($shape === 'absolute' ? $target : 'target', $alias);
            $paths = \Closure::bind(static function ($path) {
                self::$cache_path = $path;
                self::$module_cache_path = $path . '/low_random/';
            }, null, PersistentCache::class);
            $paths($alias);
            $this->assertTrue(PersistentCache::set('alias-key', array($shape)));
            $this->assertSame(serialize(array($shape)), file_get_contents($target . '/low_random/' . sha1('alias-key')));
            StaticCache::clear();
            $this->assertSame(array($shape), PersistentCache::get('alias-key'));
            $this->assertTrue(is_link($alias));
            $this->assertSame(array(), $this->ee->logger->messages);
        }
    }

    /**
     * Native mkdir follows trailing-separator aliases through intermediate links.
     *
     * @return void
     */
    public function testTrailingSeparatorAliasChainsKeepRealDiskRoundTrips(): void
    {
        foreach (array('absolute', 'relative') as $shape) {
            $parent = $this->temporaryDirectory . '/chain-' . $shape;
            mkdir($parent, 0700);
            $target = $parent . '/target';
            $inner = $parent . '/inner';
            $alias = $parent . '/cache';
            symlink($shape === 'absolute' ? $target : 'target', $inner);
            symlink(($shape === 'absolute' ? $inner : 'inner') . '/', $alias);
            clearstatcache(true);
            $paths = \Closure::bind(static function ($path) {
                self::$cache_path = $path;
                self::$module_cache_path = $path . '/low_random/';
            }, null, PersistentCache::class);
            $paths($alias);

            $this->assertTrue(PersistentCache::set('alias-key', array($shape)));
            $this->assertSame(serialize(array($shape)), file_get_contents($target . '/low_random/' . sha1('alias-key')));
            StaticCache::clear();
            $this->assertSame(array($shape), PersistentCache::get('alias-key'));
            $this->assertTrue(is_link($alias));
            $this->assertTrue(is_link($inner));
            $this->assertSame(array(), $this->ee->logger->messages);
        }
    }

    /**
     * Validate the configured alias target's creation parent before native mkdir.
     *
     * @return void
     */
    public function testUnavailableAliasTargetParentsKeepTheQuietFallback(): void
    {
        $modes = DIRECTORY_SEPARATOR === '/' ? array(0500, 0200) : array(0500);

        foreach ($modes as $mode) {
            $parent = $this->temporaryDirectory . '/alias-parent-' . $mode;
            mkdir($parent, 0700);
            $alias = $this->temporaryDirectory . '/alias-' . $mode;
            symlink($parent . '/cache', $alias);
            $paths = \Closure::bind(static function ($path) {
                self::$cache_path = $path;
                self::$module_cache_path = $path . '/low_random/';
            }, null, PersistentCache::class);
            $paths($alias);
            chmod($parent, $mode);

            try {
                $this->assertFalse(PersistentCache::set('key', 'value'));
                $this->assertStringContainsString('could not create cache folder', end($this->ee->logger->messages));
            } finally {
                chmod($parent, 0700);
            }

            $this->assertDirectoryDoesNotExist($parent . '/cache');
            $this->assertTrue(is_link($alias));
        }
    }

    /**
     * Existing writable POSIX cache directories still require search permission.
     *
     * @return void
     */
    public function testUnsearchableExistingCachePathsKeepTheQuietFallback(): void
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $this->assertSame('\\', DIRECTORY_SEPARATOR);
            return;
        }

        $base = $this->temporaryDirectory . '/cache';
        $module = $base . '/low_random';
        mkdir($base, 0700);
        mkdir($module, 0700);

        foreach (array($base, $module) as $path) {
            chmod($path, 0200);

            try {
                $this->assertFalse(PersistentCache::set('key', 'value'));
                $this->assertStringContainsString('path not writable', end($this->ee->logger->messages));
            } finally {
                chmod($path, 0700);
            }
        }

        $this->assertSame(array(), glob($module . '/*'));
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

    /**
     * A writable POSIX parent without search permission cannot accept a cache folder.
     *
     * @return void
     */
    public function testUnsearchableParentReturnsTheQuietCacheFallback(): void
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $this->assertSame('\\', DIRECTORY_SEPARATOR);
            return;
        }

        $parent = $this->temporaryDirectory . '/unsearchable';
        mkdir($parent, 0700);
        $paths = \Closure::bind(static function ($parent) {
            self::$cache_path = $parent . '/cache';
            self::$module_cache_path = $parent . '/cache/low_random/';
        }, null, PersistentCache::class);
        $paths($parent);
        chmod($parent, 0200);
        try {
            $this->assertFalse(PersistentCache::set('key', 'value'));
            $this->assertStringContainsString('could not create cache folder', $this->ee->logger->messages[0]);
        } finally {
            chmod($parent, 0700);
        }
        $this->assertDirectoryDoesNotExist($parent . '/cache');
    }

    /**
     * Omit historical falsey optional parts while retaining explicit zero IDs.
     *
     * @return void
     */
    public function testOptionalKeyPartsKeepTheirNativeIdentity(): void
    {
        $parts = array('topic', null, false, '', array(), 0.0);
        $this->assertTrue(PersistentCache::set($parts, 'kept'));
        $this->assertSame('kept', StaticCache::get('topic'));
        StaticCache::clear();
        $this->assertSame('kept', PersistentCache::get('topic'));
        $this->assertSame(serialize('kept'), file_get_contents($this->temporaryDirectory . '/cache/low_random/' . sha1('topic')));
        $this->assertTrue(PersistentCache::set(array('topic', 0), 'zero'));
        $this->assertSame('zero', PersistentCache::get(array('topic', '0')));
        $this->assertSame('kept', PersistentCache::get('topic'));
    }

    /**
     * Honor the documented flag for an already processed disk-cache key.
     *
     * @return void
     */
    public function testProcessedHasKeyReadsTheStoredFile(): void
    {
        $this->assertTrue(PersistentCache::set('processed', array('value')));
        $this->assertTrue(PersistentCache::has(sha1('processed'), true));
        $this->assertFalse(PersistentCache::has(sha1('processed')));
    }

    /**
     * Preserve native accepted persisted values without new parser diagnostics.
     *
     * @param string $serialized
     * @param mixed $expected
     * @return void
     * @dataProvider acceptedPersistedValues
     */
    public function testAcceptedPersistedValuesKeepTheirNativeBehavior($serialized, $expected): void
    {
        $this->assertTrue(PersistentCache::set('accepted-native', 'seed'));
        $path = $this->temporaryDirectory . '/cache/low_random/' . sha1('accepted-native');
        file_put_contents($path, $serialized);
        StaticCache::clear();
        $this->assertSame($expected, PersistentCache::get('accepted-native'));
        $this->assertSame($serialized, file_get_contents($path));
    }

    /**
     * Supply actual clean PHP 7.4 persisted prefixes.
     *
     * @return array
     */
    public function acceptedPersistedValues(): array
    {
        return array(
            'ordinary prefix' => array('a:1:{s:4:"name";s:1:"A";}trailing', array('name' => 'A')),
            'escaped prefix' => array('a:1:{s:4:"name";S:1:"\\41";}trailing', array('name' => 'A')),
        );
    }

    /**
     * Keep pre-existing scalar cache files readable through their original SHA1.
     *
     * @return void
     */
    public function testLegacyScalarDelimiterFilesKeepTheirColdReadIdentity(): void
    {
        $this->assertTrue(PersistentCache::set('seed', array()));
        foreach (array('topic:one', 'topic\\one') as $key) {
            $path = $this->temporaryDirectory . '/cache/low_random/' . sha1($key);
            file_put_contents($path, serialize(array('key' => $key)));
            StaticCache::clear();
            $this->assertSame(array('key' => $key), PersistentCache::get($key));
            $this->assertSame(serialize(array('key' => $key)), file_get_contents($path));
        }
    }

    /**
     * Disambiguate compound parts without replacing legacy scalar cache entries.
     *
     * @return void
     */
    public function testCompoundKeysCannotOverwriteLegacyScalarKeys(): void
    {
        foreach (array(false, true) as $reverse) {
            $entries = array(array('topic:one', 'scalar'), array(array('topic', 'one'), 'compound'));
            foreach ($reverse ? array_reverse($entries) : $entries as $entry) {
                $this->assertTrue(PersistentCache::set($entry[0], $entry[1]));
            }
            $this->assertSame('scalar', StaticCache::get('topic:one'));
            $this->assertSame('compound', StaticCache::get(array('topic', 'one')));
            StaticCache::clear();
            $this->assertSame('scalar', PersistentCache::get('topic:one'));
            $this->assertSame('compound', PersistentCache::get(array('topic', 'one')));
            $this->assertSame('scalar', PersistentCache::get(array('topic:one')));
        }
    }

}
