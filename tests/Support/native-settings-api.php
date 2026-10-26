<?php

namespace Low\Random\FluxCapacitor\Conduit {
    const PHP_INT_SIZE = 1;

    /** Retain an unrelated pre-existing namespace function. @param float $value @param float $range @return int */
    function fmod($value, $range) { $GLOBALS['settings_lexical_calls']++; return 0; }
    /** Retain an unrelated pre-existing namespace function. @param string $value @return int */
    function strlen($value) { $GLOBALS['settings_lexical_calls']++; return 0; }
    /** Keep unrelated new-operation symbols from redirecting cache behavior. @param mixed $value @return int */
    function count($value) { $GLOBALS['settings_lexical_calls']++; return 999; }
    /** Keep an unrelated replacement function legal. @param mixed $search @param mixed $replace @param mixed $value @return mixed */
    function str_replace($search, $replace, $value) { $GLOBALS['settings_lexical_calls']++; return \str_replace($search, $replace, $value); }
    /** Keep an unrelated position function legal. @param string $value @param string $needle @return int */
    function strpos($value, $needle) { $GLOBALS['settings_lexical_calls']++; return 0; }
    /** Keep an unrelated traversal function legal. @param string $path @return bool */
    function is_executable($path) { $GLOBALS['settings_lexical_calls']++; return false; }
    /** Keep an unrelated optional-file function legal. @param string $path @return bool */
    function is_file($path) { $GLOBALS['settings_lexical_calls']++; return false; }
    /** Preserve the original namespace decoder hook. @param string $value @return mixed */
    function unserialize($value) { $GLOBALS['settings_decoder_calls']++; return \unserialize($value); }
    /** Preserve the original namespace base64 hook. @param string $value @return string */
    function base64_decode($value) { $GLOBALS['settings_base64_calls']++; return \base64_decode($value); }
}

namespace Low\Random\FluxCapacitor\Base {
    /** Keep an unrelated optional-extension function legal. @param string $path @return bool */
    function is_file($path) { $GLOBALS['settings_lexical_calls']++; return false; }
}

namespace {
    define('PATH_CACHE', sys_get_temp_dir() . '/low-random-native-settings/');
    define('DIR_WRITE_MODE', 0700);
    define('FILE_WRITE_MODE', 0600);
    $root = dirname(__DIR__, 2);
    define('PATH_THIRD', $root . '/');
    require 'phar://' . $root . '/low_random/low_random.phar/FluxCapacitor/Conduit/GlobalFieldSettings.php';
    require 'phar://' . $root . '/low_random/low_random.phar/FluxCapacitor/Conduit/PersistentCache.php';
    require 'phar://' . $root . '/low_random/low_random.phar/FluxCapacitor/Conduit/StaticCache.php';
    require 'phar://' . $root . '/low_random/low_random.phar/FluxCapacitor/Conduit/McpNav.php';
    require 'phar://' . $root . '/low_random/low_random.phar/FluxCapacitor/Base/Upd.php';

    class NativeSettingsConsumer extends \Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings
    {
        /** Preserve a consumer method. @return string */
        public function nativeArrayKey() { return 'consumer'; }
        /** Preserve a consumer method. @return string */
        public function canWriteStringOffset() { return 'consumer'; }
        /** Preserve a consumer method. @return string */
        public function decodeSettings() { return 'consumer'; }
        /** Preserve a consumer method. @return string */
        public function normalizeEscapedStrings() { return 'consumer'; }
    }
    class NativeCacheConsumer extends \Low\Random\FluxCapacitor\Conduit\PersistentCache
    {
        /** Preserve a consumer method. @return string */
        public function createDirectory() { return 'consumer'; }
    }
    final class NativeSettingsDatabase
    {
        public $blob;
        public $written;
        /** Retain the query boundary. @param string $column @return self */
        public function select($column) { return $this; }
        /** Retain the query boundary. @param string $column @param mixed $value @return self */
        public function where($column, $value) { return $this; }
        /** Retain the query boundary. @param string $table @return self */
        public function get($table) { return $this; }
        /** Supply one native settings row. @return int */
        public function num_rows() { return 1; }
        /** Supply the original bytes. @param string $column @return string */
        public function row($column) { return $this->blob; }
        /** Observe the persistence payload. @param string $table @param array $value @return bool */
        public function update($table, $value) { $this->written = $value; return true; }
    }
    /** Write bytes through the real local filesystem. @param string $path @param string $value @return bool */
    function write_file($path, $value) { return file_put_contents($path, $value) !== false; }
    /** Read bytes through the real local filesystem. @param string $path @return string */
    function read_file($path) { return file_get_contents($path); }
    /** Supply only the real database boundary. @return object */
    function ee() { return $GLOBALS['settings_native_app']; }

    $GLOBALS['settings_lexical_calls'] = 0;
    $GLOBALS['settings_decoder_calls'] = 0;
    $GLOBALS['settings_base64_calls'] = 0;
    $database = new NativeSettingsDatabase();
    $GLOBALS['settings_native_app'] = (object) array('db' => $database, 'load' => new class {
        /** Retain the real filesystem boundary. @param string $name @return void */
        public function helper($name) {}
    });
    $settings = (new ReflectionClass(NativeSettingsConsumer::class))->newInstanceWithoutConstructor();
    $settings->fieldtype_name = 'native';
    $database->blob = base64_encode(serialize(array(0 => 'zero', 1000 => 'thousand', -23 => 'wrapped')));
    $read = $settings->getSetting(1000.5);
    $settings->updateSetting(1000.5, 'written');
    $persisted = unserialize(base64_decode($database->written['settings']));
    $database->blob = base64_encode(serialize('abc'));
    $compatibility = require 'phar://' . $root . '/low_random/low_random.phar/FluxCapacitor/Conduit/NativeSettings.php';
    $normalized = $compatibility->normalize('S:3:"abc";');
    $escaped = $settings->getSettings();
    $cache = (new ReflectionClass(NativeCacheConsumer::class))->newInstanceWithoutConstructor();
    $cacheWrite = NativeCacheConsumer::set('topic:one', 'kept');
    \Low\Random\FluxCapacitor\Conduit\StaticCache::clear();
    $cacheRead = NativeCacheConsumer::get('topic:one');
    $legacyPath = PATH_CACHE . 'low_random/' . sha1('topic:one');
    $legacyBytes = file_get_contents($legacyPath);
    $update = (new ReflectionClass(\Low\Random\FluxCapacitor\Base\Upd::class))->newInstanceWithoutConstructor();
    $update->version = '2.0';
    $optionalUpdate = $update->update('1.0');
    echo json_encode(array('read' => $read, 'persisted' => $persisted, 'escaped' => $escaped, 'normalized' => $normalized,
        'new_native_dispatches' => $GLOBALS['settings_lexical_calls'],
        'original_decoder_dispatches' => $GLOBALS['settings_decoder_calls'],
        'original_base64_dispatches' => $GLOBALS['settings_base64_calls'],
        'cache_write' => $cacheWrite, 'cache_read' => $cacheRead, 'legacy_cache_bytes' => $legacyBytes, 'optional_update' => $optionalUpdate,
        'consumer_methods' => array($settings->nativeArrayKey(), $settings->canWriteStringOffset(),
            $settings->decodeSettings(), $settings->normalizeEscapedStrings(), $cache->createDirectory())), JSON_THROW_ON_ERROR);
}
