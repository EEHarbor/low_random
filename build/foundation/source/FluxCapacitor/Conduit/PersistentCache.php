<?php

namespace Low\Random\FluxCapacitor\Conduit;

use Low\Random\FluxCapacitor\Conduit\StaticCache;

// Based on CI DB caching function
class PersistentCache
{
    private static $cache_path = PATH_CACHE;
    private static $module_cache_path = PATH_CACHE . 'low_random/';

    /**
     * Retrieve the data stored in the cache.
     *
     * @param  string|array  $key  Either a string or array of items
     * @return mixed Cached value, or false when no value is available.
     */
    public static function get($key)
    {
        $key = self::createKey($key);
        // Check to see if we have this stored in our StaticCache first.
        $static_cache_data = StaticCache::get($key, true);

        if (StaticCache::has($key, true)) {
            return $static_cache_data;
        }

        if (! self::checkPath()) {
            return false;
        }

        ee()->load->helper('file');

        if (false === ($cache_data = read_file(self::$module_cache_path . $key))) {
            ee()->load->library('logger');
            ee()->logger->developer('Low Random: Write Cache enabled but could not find cache for: ' . self::$module_cache_path . $key);
            return false;
        }

        // Store the cache data in the StaticCache so we don't have
        // to read it off the disk again during this execution loop.
        $cache_data = self::serveCacheData($cache_data);
        StaticCache::set($key, $cache_data, true);

        return $cache_data;
    }

     /**
     * Set the data to be stored in the cache.
     * @param  string|array  $key  Either a string or array of items
     * @return boolean             Indicates whether the data was able to be saved
     */
    public static function set($key, $value)
    {
        if (!self::checkPath()) {
            return false;
        }

        ee()->load->helper('file');

        $key = self::createKey($key);
        $cache_data = self::prepCacheData($value);

        if (write_file(self::$module_cache_path . $key, $cache_data) === false) {
            ee()->load->library('logger');
            ee()->logger->developer('Low Random: Write Cache enabled but could not write cache file: ' . self::$module_cache_path . $key);

            return false;
        }

        // Try to change the folder permissions to EE's default write mode.
        @chmod(self::$module_cache_path . $key, FILE_WRITE_MODE);

        // Store the cache data in the StaticCache so we don't have
        // to read it off the disk again during this execution loop.
        // We use $value here because we don't need it in file format.
        StaticCache::set($key, $value, true);

        return true;
    }

    /**
     * Delete the cached file for a specific key for this module.
     * @return bool  Indicates if the key was successfully deleted.
     */
    public static function delete($key)
    {
        if (!self::checkPath()) {
            return false;
        }

        $key = self::createKey($key);

        if (is_file(self::$module_cache_path . $key)) {
            @unlink(self::$module_cache_path . $key);
        }

        // Remove any StaticCache we have for this key as well.
        StaticCache::delete($key, true);

        return true;
    }

    /**
     * Find out if a specific key exists in the file cache.
     * @param  string|array  $key            Either a string or an array if items
     * @param  boolean       $key_processed  Whether the key has already be SHA1'd
     * @return boolean                       Whether the key exists in the cache.
     * @throws \Throwable When cache-key hooks or filesystem diagnostics fail.
     */
    public static function has($key, $key_processed = false)
    {
        // NAN was truthy before PHP 8.5 began diagnosing its boolean conversion.
        $is_nan = \is_float($key_processed) && \is_nan($key_processed);
        if (!$is_nan && !$key_processed) {
            $key = self::createKey($key);
        }

        return is_file(self::$module_cache_path . $key);
    }

    /**
     * Delete all the cached files that this module may have created.
     * @return bool  Indicates if the cache path existed and we attempted to clear it.
     */
    public static function clear()
    {
        if (!self::checkPath()) {
            return false;
        }

        foreach (glob(self::$module_cache_path . '*') as $key) {
            if (is_file($key)) {
                @unlink($key);
            }

            // Remove any StaticCache we have for this key as well.
            // We don't want to clear the StaticCache globally as
            // we may only be looking to clear the persistent cache
            // and leave the other things in the StaticCache alone.
            StaticCache::delete(pathinfo($key, PATHINFO_FILENAME), true);
        }

        return true;
    }


    /************************************************************
    PRIVATE FUNCTIONS
    ************************************************************/

    /**
     * Create a SHA1 key from either a string or an array of pieces to
     * ensure that the key is unique.
     *
     * @param  string|array  $key  Either a string or an array of items
     * @return string              SHA1 string
     * @throws \Throwable When an original namespace cache-key hook fails.
     */
    private static function createKey($key)
    {
        if (!is_array($key)) {
            $key = array($key);
        }

        // Preserve the original nested callees before a filter hook can register others.
        $bindings = new class {
            public static bool $hash;
            public static bool $join;
            public static bool $filter;
        };
        if (!isset($bindings::$hash)) {
            $bindings::$hash = \function_exists(__NAMESPACE__ . '\\sha1');
            $bindings::$join = \function_exists(__NAMESPACE__ . '\\implode');
            $bindings::$filter = \function_exists(__NAMESPACE__ . '\\array_filter');
        }
        if ($bindings::$hash || $bindings::$join || $bindings::$filter) {
            return sha1(implode(':', array_filter($key)));
        }

        $pieces = \array_filter($key, static function ($piece) {
            return (\is_float($piece) && \is_nan($piece)) || $piece || $piece === 0 || $piece === '0';
        });
        $pieces = \array_map(static function ($piece) {
            return \is_float($piece) && \is_nan($piece) ? 'NAN' : $piece;
        }, $pieces);
        if (\count($pieces) < 2) {
            return \sha1(\implode(':', $pieces));
        }

        // Native compound keys cannot alias a scalar SHA1 or another colon grouping.
        return 'compound-' . \sha1(\implode(':', \str_replace(array('\\', ':'), array('\\\\', '\\:'), $pieces)));
    }

    /**
     * Prep the data to be stored in the cache. Making this it's own
     * method so we can add to it if needed.
     * @param  array|object  $value Data to store in cache
     * @return string        Serialized string of values
     */
    private static function prepCacheData($value)
    {
        return serialize($value);
    }

    /**
     * Read native cached values, including legacy escaped string tokens.
     *
     * @param string $value Serialized cache bytes.
     * @return mixed
     * @throws \Throwable When hydration or its diagnostic handler fails.
     */
    private static function serveCacheData($value)
    {
        // Class statics retain the native callsite lifetime across PHP 7.4 subclasses.
        $bindings = new class {
            public static bool $parser;
        };
        if (!isset($bindings::$parser)) {
            $bindings::$parser = \function_exists(__NAMESPACE__ . '\\unserialize');
        }
        if ($bindings::$parser) {
            return unserialize($value);
        }

        $normalizer = require __DIR__ . '/NativeSettings.php';
        return \unserialize($normalizer->normalize($value));
    }

    /**
     * Make sure the EE global cache folder and our module-specific folders
     * exist and are writable.
     *
     * @return bool Whether the folders exist and are writable.
     * @throws \Throwable When an unexpected filesystem diagnostic handler fails.
     */
    private static function checkPath()
    {
        /**
         * Preserve native cache aliases and the existing logged directory-failure path.
         *
         * @param mixed $path Original configured directory operand, retained by reference.
         * @param bool $module Whether this is the original module-specific native callsite.
         * @return bool Whether native directory creation succeeded.
         * @throws \Throwable When an unexpected diagnostic handler fails.
         */
        $createDirectory = static function (&$path, $module)
        {
            // Each original opcode resolves independently, only when its directory is missing.
            $bindings = new class {
                public static bool $generic;
                public static bool $module;
            };
            if ($module && !isset($bindings::$module)) {
                $bindings::$module = \function_exists(__NAMESPACE__ . '\\mkdir');
            }
            if (!$module && !isset($bindings::$generic)) {
                $bindings::$generic = \function_exists(__NAMESPACE__ . '\\mkdir');
            }
            $hook = $module ? $bindings::$module : $bindings::$generic;
            $physical = !$hook && \is_string($path)
                && (!\preg_match('~\\A[a-zA-Z0-9+.-]{2,}://~D', $path) || \strncasecmp($path, 'file://', 7) === 0);
            $mkdirLine = 0;
            $previous = null;
            if ($physical) {
                $previous = \set_error_handler(static function ($severity, $message, $file, $line) use (&$previous, &$mkdirLine) {
                    if ($severity === \E_WARNING && $file === __FILE__ && $line === $mkdirLine
                        && \preg_match('/^mkdir\\(\\): (?:Permission denied|File exists|Not a directory|No such file or directory|Too many levels of symbolic links|Read-only file system|File name too long)$/D', $message)) {
                        return true;
                    }

                    return $previous === null ? false : $previous(...\func_get_args());
                });
            }

            try {
                if ($module) {
                    $mkdirLine = __LINE__ + 1;
                    return @($hook ? mkdir($path, DIR_WRITE_MODE) : \mkdir($path, DIR_WRITE_MODE));
                }
                $mkdirLine = __LINE__ + 1;
                return @($hook ? mkdir($path, DIR_WRITE_MODE) : \mkdir($path, DIR_WRITE_MODE));
            } finally {
                if ($physical) {
                    \restore_error_handler();
                }
            }
        };

        // Add a trailing slash to the path if needed
        self::$cache_path = preg_replace("/(.+?)\/*$/", "\\1/", self::$cache_path);

        // If the generic cache folder doesn't exist, try to create it.
        if (!is_dir(self::$cache_path)) {
            if (!$createDirectory(self::$cache_path, false)) {
                ee()->load->library('logger');
                ee()->logger->developer('Low Random: Write Cache enabled but could not create cache folder: ' . self::$cache_path);
                return false;
            }

            // Try to change the folder permissions to EE's default write mode.
            @chmod(self::$cache_path, DIR_WRITE_MODE);
        }

        // Make sure the generic cache folder is writable.
        if (!is_writable(self::$cache_path)
            || (\DIRECTORY_SEPARATOR === '/' && \is_string(self::$cache_path)
                && (!\preg_match('~\\A[a-zA-Z0-9+.-]{2,}://~D', self::$cache_path) || \strncasecmp(self::$cache_path, 'file://', 7) === 0)
                && !\is_executable(self::$cache_path))) {
            ee()->load->library('logger');
            ee()->logger->developer('Low Random: Write Cache enabled but cache path not writable: ' . self::$cache_path);
            return false;
        }

        // If the module-specific cache folder doesn't exist, try to create it.
        if (!is_dir(self::$module_cache_path)) {
            if (!$createDirectory(self::$module_cache_path, true)) {
                ee()->load->library('logger');
                ee()->logger->developer('Low Random: Write Cache enabled but could not create module cache folder: ' . self::$module_cache_path);
                return false;
            }

            // Try to change the folder permissions to EE's default write mode.
            @chmod(self::$module_cache_path, DIR_WRITE_MODE);
        }

        // Make sure the module-specific cache folder is writable.
        if (!is_writable(self::$module_cache_path)
            || (\DIRECTORY_SEPARATOR === '/' && \is_string(self::$module_cache_path)
                && (!\preg_match('~\\A[a-zA-Z0-9+.-]{2,}://~D', self::$module_cache_path) || \strncasecmp(self::$module_cache_path, 'file://', 7) === 0)
                && !\is_executable(self::$module_cache_path))) {
            // If the path is wrong we'll turn off caching
            ee()->load->library('logger');
            ee()->logger->developer('Low Random: Write Cache enabled but module cache path not writable: ' . self::$module_cache_path);
            return false;
        }

        return true;
    }

}
