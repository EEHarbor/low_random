<?php

namespace Low\Random\FluxCapacitor\Conduit {
    /** Preserve the original carrier callback. @param mixed $value @return string */
    function base64_decode($value) { $GLOBALS['hook_calls'][] = array('conduit-base64', $value); return ($GLOBALS['argv'][2] ?? '') === 'decoded' ? \base64_decode($value === null ? '' : $value) : 'S:3:"abc";'; }
    /** Preserve the original raw decoder callback. @param mixed $value @return array */
    if (!in_array($GLOBALS['argv'][2] ?? '', array('decoded', 'cache-lifetime', 'cache-carrier'), true)) {
        function unserialize($value) { $GLOBALS['hook_calls'][] = array('conduit-unserialize', $value); return array('native_hook' => 'conduit'); }
    }
}
namespace Low\Random\FluxCapacitor\Bridge {
    /** Preserve the original carrier callback. @param mixed $value @return string */
    function base64_decode($value) { $GLOBALS['hook_calls'][] = array('bridge-base64', $value); return ($GLOBALS['argv'][2] ?? '') === 'decoded' ? \base64_decode($value === null ? '' : $value) : 'S:3:"abc";'; }
    /** Preserve the original raw decoder callback. @param mixed $value @return array */
    if (!in_array($GLOBALS['argv'][2] ?? '', array('decoded', 'cache-lifetime', 'cache-carrier'), true)) {
        function unserialize($value) { $GLOBALS['hook_calls'][] = array('bridge-unserialize', $value); return array('native_hook' => 'bridge'); }
    }
}
namespace {
    final class NativeDecodedCarrier
    {
        private $scope;
        /**
         * Record the namespace that supplied the native decoded value.
         *
         * @param string $scope
         * @return void
         */
        public function __construct($scope) { $this->scope = $scope; }
        /**
         * Preserve the original once-only native decoded-value conversion.
         *
         * @return string
         */
        public function __toString()
        {
            $GLOBALS['hook_calls'][] = array($this->scope . '-stringify');
            if (($GLOBALS['argv'][3] ?? '') === 'escaped') { return 'a:1:{s:4:"wire";S:6:"native";}'; }
            $value = serialize(array('wire' => 'native'));
            return ($GLOBALS['argv'][3] ?? '') === 'prefix' ? $value . 'ignored remainder' : $value;
        }
    }
    $root = dirname(__DIR__, 2);
    $archive = isset($argv[1]) ? $argv[1] : $root . '/low_random/low_random.phar';
    define('PATH_THIRD', $root . '/');
    define('PATH_CACHE', sys_get_temp_dir() . '/settings-hook-cache/');
    define('DIR_WRITE_MODE', 0700);
    define('FILE_WRITE_MODE', 0600);
    require 'phar://' . $archive . '/FluxCapacitor/Conduit/GlobalFieldSettings.php';
    require 'phar://' . $archive . '/FluxCapacitor/Conduit/StaticCache.php';
    require 'phar://' . $archive . '/FluxCapacitor/Conduit/PersistentCache.php';
    require 'phar://' . $archive . '/FluxCapacitor/Bridge/AbstractBridge.php';

    final class NativeHookDatabase
    {
        public $blob;
        public $data_cache = array();
        /** Retain the query boundary. @param string $column @return self */
        public function select($column) { return $this; }
        /** Retain the query boundary. @param string $column @param mixed $value @return self */
        public function where($column, $value) { return $this; }
        /** Retain the query boundary. @param string $table @return self */
        public function get($table) { return $this; }
        /** Isolate unrelated add-on settings tables. @param string $table @return bool */
        public function table_exists($table) { return false; }
        /** Supply the stored fieldtype row. @return int */
        public function num_rows() { return 1; }
        /** Supply the untouched carrier. @param string $column @return mixed */
        public function row($column) { return $this->blob; }
    }
    /** Supply the EE database boundary. @param string|null $service @return object */
    function ee($service = null) { return $service === 'db' ? $GLOBALS['hook_app']->db : $GLOBALS['hook_app']; }
    /** Read the real persisted cache bytes. @param string $path @return string */
    function read_file($path) { return ($GLOBALS['argv'][2] ?? '') === 'cache-carrier' ? new NativeDecodedCarrier('cache') : file_get_contents($path); }

    $database = new NativeHookDatabase();
    $GLOBALS['hook_app'] = (object) array('db' => $database, 'load' => new class {
        /** Retain the real filesystem reader. @param string $name @return void */
        public function helper($name) {}
    });
    if (in_array($argv[2] ?? '', array('cache-lifetime', 'cache-carrier'), true)) {
        mkdir(PATH_CACHE, 0700);
        mkdir(PATH_CACHE . 'low_random', 0700);
        file_put_contents(PATH_CACHE . 'low_random/' . sha1('native-hook'), serialize(array('kept' => 7)));
        if ($argv[2] === 'cache-carrier') {
            $GLOBALS['hook_calls'] = array();
            $value = \Low\Random\FluxCapacitor\Conduit\PersistentCache::get('native-hook');
            echo json_encode(array('wire' => $value['wire'], 'calls' => $GLOBALS['hook_calls']), JSON_THROW_ON_ERROR);
            exit(0);
        }
        $values = array();
        $GLOBALS['cache_late_calls'] = 0;
        for ($call = 0; $call < 3; $call++) {
            if ($call === 1) {
                eval('namespace Low\\Random\\FluxCapacitor\\Conduit; function unserialize($value) { $GLOBALS["cache_late_calls"]++; return array("kept" => 88); }');
            }
            \Low\Random\FluxCapacitor\Conduit\StaticCache::clear();
            $value = \Low\Random\FluxCapacitor\Conduit\PersistentCache::get('native-hook');
            $values[] = $value['kept'];
        }
        echo json_encode(array('values' => $values, 'hook_calls' => $GLOBALS['cache_late_calls']), JSON_THROW_ON_ERROR);
        exit(0);
    }
    $results = array();
    foreach (array('GlobalFieldSettings', 'EE2', 'EE3', 'EE4', 'EE5') as $kind) {
        if ($kind !== 'GlobalFieldSettings') {
            require 'phar://' . $archive . '/FluxCapacitor/Bridge/' . $kind . '.php';
        }
        $wire = serialize(array('wire' => 'native'));
        if (($argv[3] ?? '') === 'escaped') { $wire = 'a:1:{s:4:"wire";S:6:"native";}'; }
        if (($argv[3] ?? '') === 'prefix') { $wire .= 'ignored remainder'; }
        $carriers = ($argv[2] ?? '') === 'decoded' ? array(base64_encode($wire), null) : array('carrier-text', null);
        foreach ($carriers as $carrier) {
            $GLOBALS['hook_calls'] = array();
            $database->blob = $carrier;
            \Low\Random\FluxCapacitor\Conduit\StaticCache::clear();
            if ($kind === 'GlobalFieldSettings') {
                $object = (new ReflectionClass(\Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings::class))->newInstanceWithoutConstructor();
                $object->fieldtype_name = 'native';
                $value = $object->getSettings();
            } else {
                $class = 'Low\\Random\\FluxCapacitor\\Bridge\\' . $kind;
                $object = (new ReflectionClass($class))->newInstanceWithoutConstructor();
                $module = new ReflectionProperty($class, 'module');
                if (PHP_VERSION_ID < 80100) {
                    $module->setAccessible(true);
                }
                $module->setValue($object, 'low_random');
                $value = $object->getSettings(true);
            }
            $results[] = array('kind' => $kind, 'carrier' => $carrier, 'hook' => ($argv[2] ?? '') === 'decoded' ? ($value['wire'] ?? null) : ($value['native_hook'] ?? null), 'calls' => $GLOBALS['hook_calls']);
        }
    }
    if (($argv[2] ?? '') === 'decoded') {
        echo json_encode($results, JSON_THROW_ON_ERROR);
        exit(0);
    }
    mkdir(PATH_CACHE, 0700);
    mkdir(PATH_CACHE . 'low_random', 0700);
    file_put_contents(PATH_CACHE . 'low_random/' . sha1('native-hook'), 'S:3:"abc";');
    $GLOBALS['hook_calls'] = array();
    \Low\Random\FluxCapacitor\Conduit\StaticCache::clear();
    $value = \Low\Random\FluxCapacitor\Conduit\PersistentCache::get('native-hook');
    $results[] = array('kind' => 'cache', 'hook' => $value['native_hook'] ?? null, 'calls' => $GLOBALS['hook_calls']);
    echo json_encode($results, JSON_THROW_ON_ERROR);
}
