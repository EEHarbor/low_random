<?php

namespace Low\Random\FluxCapacitor\Conduit {
    if (($GLOBALS['argv'][3] ?? '') === 'decoded') {
        /** Return the original temporary decoded carrier. @param mixed $value @return \OwnedSettingsCarrier */
        function base64_decode($value) { $GLOBALS['ownership_trace'][] = 'base64'; return new \OwnedSettingsCarrier(false); }
    }
}
namespace Low\Random\FluxCapacitor\Bridge {
    if (($GLOBALS['argv'][3] ?? '') === 'decoded') {
        /** Return the original temporary decoded carrier. @param mixed $value @return \OwnedSettingsCarrier */
        function base64_decode($value) { $GLOBALS['ownership_trace'][] = 'base64'; return new \OwnedSettingsCarrier(false); }
    }
}
namespace {
    final class OwnedSettingsValue
    {
        /** Record the real native hydration point. @return void */
        public function __wakeup() { $GLOBALS['ownership_trace'][] = 'hydrate'; $GLOBALS['ownership_reporting'][] = error_reporting(); }
    }
    final class OwnedSettingsCarrier
    {
        private $encoded;
        /** Retain the kind of native operand. @param bool $encoded @return void */
        public function __construct($encoded) { $this->encoded = $encoded; }
        /** Supply bytes while recording native conversion. @return string */
        public function __toString()
        {
            $GLOBALS['ownership_trace'][] = 'convert';
            $GLOBALS['ownership_reporting'][] = error_reporting();
            $value = serialize(array('kept' => 7, 'object' => new OwnedSettingsValue()));
            return $this->encoded ? base64_encode($value) : $value;
        }
        /** Record release without adding an independent owner reference. @return void */
        public function __destruct() { $GLOBALS['ownership_trace'][] = 'release'; $GLOBALS['ownership_reporting'][] = error_reporting(); }
    }
    final class OwnershipSettingsDatabase
    {
        public $data_cache = array();
        /** Retain the fluent query. @param string $column @return self */
        public function select($column) { return $this; }
        /** Retain the fluent query. @param string $column @param mixed $value @return self */
        public function where($column, $value) { return $this; }
        /** Supply the fieldtype row. @param string $table @return self */
        public function get($table) { return $this; }
        /** Isolate unrelated settings tables. @param string $table @return bool */
        public function table_exists($table) { return false; }
        /** Supply the real count. @return int */
        public function num_rows() { return 1; }
        /** Return an independently owned temporary row value. @param string $column @return mixed */
        public function row($column)
        {
            $GLOBALS['ownership_trace'][] = 'row';
            return $GLOBALS['argv'][3] === 'encoded' ? new OwnedSettingsCarrier(true) : 'raw carrier';
        }
    }
    /** Supply only the EE database collaborator. @param string|null $service @return object */
    function ee($service = null) { return $service === 'db' ? $GLOBALS['ownership_app']->db : $GLOBALS['ownership_app']; }

    $archive = $argv[1];
    $kind = $argv[2];
    define('PATH_THIRD', dirname(__DIR__, 2) . '/');
    require 'phar://' . $archive . '/FluxCapacitor/Conduit/StaticCache.php';
    if ($kind !== 'GlobalFieldSettings') { require 'phar://' . $archive . '/FluxCapacitor/Bridge/AbstractBridge.php'; }
    $scope = $kind === 'GlobalFieldSettings' ? 'Conduit' : 'Bridge';
    require 'phar://' . $archive . '/FluxCapacitor/' . $scope . '/' . $kind . '.php';
    $class = 'Low\\Random\\FluxCapacitor\\' . $scope . '\\' . $kind;
    $GLOBALS['ownership_app'] = (object) array('db' => new OwnershipSettingsDatabase());
    $GLOBALS['ownership_trace'] = array();
    $GLOBALS['ownership_reporting'] = array();
    $object = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    if ($kind === 'GlobalFieldSettings') {
        $object->fieldtype_name = 'native';
        $value = $object->getSettings();
    } else {
        $module = new ReflectionProperty($class, 'module');
        if (PHP_VERSION_ID < 80100) { $module->setAccessible(true); }
        $module->setValue($object, 'low_random');
        $value = $object->getSettings(true);
    }
    echo json_encode(array('kept' => $value['kept'], 'trace' => $GLOBALS['ownership_trace'], 'reporting' => $GLOBALS['ownership_reporting']), JSON_THROW_ON_ERROR);
}
