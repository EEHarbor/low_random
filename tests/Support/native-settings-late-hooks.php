<?php

$root = dirname(__DIR__, 2);
$archive = $argv[1];
$kind = $argv[2];
$timing = $argv[3];
$GLOBALS['late_namespace'] = 'Low\\Random\\FluxCapacitor\\' . ($kind === 'GlobalFieldSettings' ? 'Conduit' : 'Bridge');
$GLOBALS['late_timing'] = $timing;
$GLOBALS['late_calls'] = 0;

/**
 * Register ordinary consumer hooks after the original callsite resolves.
 *
 * @return void
 */
function registerLateSettingsHooks()
{
    $namespace = $GLOBALS['late_namespace'];
    if (function_exists($namespace . '\\unserialize')) { return; }
    eval('namespace ' . $namespace . '; function unserialize($value) { $GLOBALS["late_calls"]++; return array("kept" => 88); } function base64_decode($value) { $GLOBALS["late_calls"]++; return \\serialize(array("kept" => 88)); }');
}

final class LateSettingsDatabase
{
    public $data_cache = array();
    public $rows = 1;
    /** Retain the fluent query. @param string $column @return self */
    public function select($column) { return $this; }
    /** Retain the fluent query. @param string $column @param mixed $value @return self */
    public function where($column, $value) { return $this; }
    /** Supply the fieldtype row. @param string $table @return self */
    public function get($table) { return $this; }
    /** Isolate unrelated tables. @param string $table @return bool */
    public function table_exists($table) { return false; }
    /** Return the actual configured count. @return int */
    public function num_rows() { return $this->rows; }
    /**
     * Exercise a native query callback without replacing the source method.
     *
     * @param string $column
     * @return string
     */
    public function row($column)
    {
        if ($GLOBALS['late_timing'] === 'row') { registerLateSettingsHooks(); }
        return base64_encode(serialize(array('kept' => 7)));
    }
}
/** Supply only the EE database collaborator. @param string|null $service @return object */
function ee($service = null) { return $service === 'db' ? $GLOBALS['late_app']->db : $GLOBALS['late_app']; }

define('PATH_THIRD', $root . '/');
require 'phar://' . $archive . '/FluxCapacitor/Conduit/StaticCache.php';
if ($kind !== 'GlobalFieldSettings') {
    require 'phar://' . $archive . '/FluxCapacitor/Bridge/AbstractBridge.php';
}
$class = $GLOBALS['late_namespace'] . '\\' . $kind;
require 'phar://' . $archive . '/FluxCapacitor/' . ($kind === 'GlobalFieldSettings' ? 'Conduit' : 'Bridge') . '/' . $kind . '.php';
$database = new LateSettingsDatabase();
$GLOBALS['late_app'] = (object) array('db' => $database);
if ($timing === 'initial') { registerLateSettingsHooks(); }
$values = array();
for ($call = 0; $call < 3; $call++) {
    if ($call === 1 && $timing !== 'row') { registerLateSettingsHooks(); }
    $database->rows = $timing === 'no-row' && $call === 0 ? 0 : 1;
    $selectedClass = $class;
    if ($timing === 'inherited' && $call > 0) {
        if (!class_exists('LateSettingsChild', false)) { eval('class LateSettingsChild extends \\' . $class . ' {}'); }
        $selectedClass = 'LateSettingsChild';
    }
    $object = (new ReflectionClass($selectedClass))->newInstanceWithoutConstructor();
    if ($kind === 'GlobalFieldSettings') {
        $object->fieldtype_name = 'native';
        $value = $object->getSettings();
    } else {
        $module = new ReflectionProperty($class, 'module');
        if (PHP_VERSION_ID < 80100) { $module->setAccessible(true); }
        $module->setValue($object, 'low_random');
        \Low\Random\FluxCapacitor\Conduit\StaticCache::clear();
        $value = $object->getSettings(true);
    }
    $values[] = $value['kept'] ?? null;
}
echo json_encode(array('values' => $values, 'hook_calls' => $GLOBALS['late_calls']), JSON_THROW_ON_ERROR);
