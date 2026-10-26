<?php

namespace Low\Random\FluxCapacitor\Conduit {
    /**
     * Preserve raw carrier mutation and the helper's reference-return boundary.
     *
     * @param mixed $carrier Original referenced query result.
     * @return string Referenced decoded bytes owned by the helper.
     */
    function &base64_decode(&$carrier)
    {
        $carrier = 'carrier-mutated';
        return $GLOBALS['reference_wire'];
    }

    /**
     * Consume the original helper reference before any native normalization.
     *
     * @param mixed $wire Original referenced helper result.
     * @return array Native namespace implementation's settings.
     */
    function unserialize(&$wire)
    {
        $GLOBALS['reference_seen'][] = $wire;
        $wire = 'wire-mutated';
        return array('keep' => 7);
    }
}

namespace Low\Random\FluxCapacitor\Bridge {
    /**
     * Keep Bridge hooks in their original namespace and reference context.
     *
     * @param mixed $carrier Original referenced query result.
     * @return string Referenced helper bytes.
     */
    function &base64_decode(&$carrier)
    {
        $carrier = 'carrier-mutated';
        return $GLOBALS['reference_wire'];
    }

    /**
     * Keep the original reference supplied to the selected Bridge decoder.
     *
     * @param mixed $wire Referenced helper bytes.
     * @return array Original hook result.
     */
    function unserialize(&$wire)
    {
        $GLOBALS['reference_seen'][] = $wire;
        $wire = 'wire-mutated';
        return array('keep' => 7);
    }
}

namespace {
    final class ReferencedSettingsDatabase
    {
        public $blob;
        public $data_cache = array();

        /**
         * Supply only the fluent settings query and absent add-on table.
         *
         * @param string $name Framework interaction.
         * @param array $arguments Original framework arguments.
         * @return mixed Query boundary, row count or absent table.
         */
        public function __call($name, $arguments)
        {
            if ($name === 'num_rows') { return 1; }
            if ($name === 'table_exists') { return false; }
            return $this;
        }

        /**
         * Retain a legal query implementation's reference-returning row value.
         *
         * @param string $column Requested column.
         * @return mixed Referenced original fieldtype carrier.
         */
        public function &row($column)
        {
            $GLOBALS['reference_query'] = $this;
            return $this->blob;
        }
    }

    /**
     * Supply the original EE query route without a full application bootstrap.
     *
     * @param string|null $service Framework service name.
     * @return object Database service or EE container.
     */
    function ee($service = null) { return $service === 'db' ? $GLOBALS['reference_app']->db : $GLOBALS['reference_app']; }

    $artifact = $argv[1] ?? dirname(__DIR__, 2) . '/low_random/low_random.phar';
    define('PATH_THIRD', dirname(__DIR__, 2) . '/');
    require 'phar://' . $artifact . '/FluxCapacitor/Conduit/StaticCache.php';
    require 'phar://' . $artifact . '/FluxCapacitor/Bridge/AbstractBridge.php';
    $db = new ReferencedSettingsDatabase();
    $GLOBALS['reference_app'] = (object) array('db' => $db);
    $rows = array();
    foreach (array('GlobalFieldSettings', 'EE2', 'EE3', 'EE4', 'EE5') as $kind) {
        $scope = $kind === 'GlobalFieldSettings' ? 'Conduit' : 'Bridge';
        require 'phar://' . $artifact . '/FluxCapacitor/' . $scope . '/' . $kind . '.php';
        $class = 'Low\\Random\\FluxCapacitor\\' . $scope . '\\' . $kind;
        $object = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        Low\Random\FluxCapacitor\Conduit\StaticCache::clear();
        $db->blob = 'raw-carrier';
        $GLOBALS['reference_wire'] = 'raw-hook-wire';
        $GLOBALS['reference_seen'] = array();
        if ($kind === 'GlobalFieldSettings') {
            $object->fieldtype_name = 'low_random';
            $value = $object->getSettings();
        } else {
            $module = new ReflectionProperty($class, 'module');
            if (PHP_VERSION_ID < 80100) { $module->setAccessible(true); }
            $module->setValue($object, 'low_random');
            $value = $object->getSettings(true);
        }
        $rows[] = array('kind' => $kind, 'keep' => $value['keep'], 'carrier' => $GLOBALS['reference_query']->blob,
            'wire' => $GLOBALS['reference_wire'], 'seen' => $GLOBALS['reference_seen']);
    }
    echo json_encode($rows, JSON_THROW_ON_ERROR);
}
