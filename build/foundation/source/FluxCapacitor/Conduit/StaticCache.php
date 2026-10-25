<?php

namespace Low\Random\FluxCapacitor\Conduit;

class StaticCache
{
    public static $cache_array = array();

    /**
     * Retrieve the data stored in the cache.
     *
     * @param  string|array  $key  Either a string or array of items
     * @param bool $key_processed Whether the key is already hashed.
     * @return mixed Cached value, or false when no value is available.
     */
    public static function get($key, $key_processed = false)
    {
        $key = self::createKey($key, $key_processed);

        return isset(self::$cache_array[$key]) ? self::$cache_array[$key] : false;
    }

    /**
     * DEPRECATED - Alias of `put`.
     * @param  string|array  $key            Either a string or array of items
     * @param  array|object  $value          The array or object to store in the cache.
     * @param  boolean       $key_processed  If true, bypasses `create_key`. Used when key has already be SHA1'd.
     * @return boolean                       Indicates whether the data was able to be saved
     */
    public static function set($key, $value, $key_processed = false)
    {
        self::put($key, $value, $key_processed);
    }

    /**
     * Set the data to be stored in the cache.
     * @param  string|array  $key            Either a string or array of items
     * @param  array|object  $value          The array or object to store in the cache.
     * @param  boolean       $key_processed  If true, bypasses `create_key`. Used when key has already be SHA1'd.
     * @return boolean                       Indicates whether the data was able to be saved
     */
    public static function put($key, $value, $key_processed = false)
    {
        self::$cache_array[ self::createKey($key, $key_processed) ] = $value;
    }

    /**
     * Delete a specific key from the static cache.
     *
     * @param  string|array  $key            Either a string or an array if items
     * @param  boolean       $key_processed  If true, bypasses `create_key`. Used when key has already be SHA1'd.
     * @return boolean                       Whether the function succeeded.
     */
    public static function delete($key, $key_processed = false)
    {
        unset(self::$cache_array[ self::createKey($key, $key_processed) ]);

        return true;
    }

    /**
     * Find out if a specific key exists in the static cache.
     * @param  string|array  $key            Either a string or an array if items
     * @param  boolean       $key_processed  If true, bypasses `create_key`. Used when key has already be SHA1'd.
     * @return boolean                       Whether the key exists in the cache.
     */
    public static function has($key, $key_processed = false)
    {
        return isset(self::$cache_array[ self::createKey($key, $key_processed) ]);
    }

    /**
     * Return all of the cached items
     * @return array  An array of all the cached items
     */
    public static function all()
    {
        return self::$cache_array;
    }

    /**
     * Clear the entire cache.
     * @return boolean  True, always True.
     */
    public static function clear()
    {
        self::$cache_array = array();

        return true;
    }

    /**
     * Create a SHA1 key from either a string or an array of pieces to
     * ensure that the key is unique.
     *
     * @param  string|array  $key            Either a string or an array of items
     * @param  boolean       $key_processed  If true, bypasses `create_key`. Used when key has already be SHA1'd.
     * @return string                        SHA1 string
     * @throws \Throwable When an original namespace cache-key hook fails.
     */
    private static function createKey($key, $key_processed = false)
    {
        if ($key_processed !== false) {
            return $key;
        }

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
}
