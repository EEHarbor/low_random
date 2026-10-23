<?php

namespace Tests\Support {
    use ErrorException;

    final class Diagnostics
    {
        /**
         * Exercise suppression independently of shipped source defects.
         *
         * @return void
         */
        public static function probe()
        {
            @trigger_error('diagnostic harness probe', E_USER_DEPRECATED);
        }

        /**
         * Fail diagnostics from the add-on, including diagnostics hidden by @.
         *
         * @return callable|null
         */
        public static function install()
        {
            return set_error_handler(static function ($severity, $message, $file, $line) {
                if (strpos($file, dirname(__DIR__, 2) . '/low_random/') !== false || strpos($file, dirname(__DIR__) . '/') === 0) {
                    throw new ErrorException($message, 0, $severity, $file, $line);
                }

                return false;
            }, E_ALL);
        }
    }

    final class Template
    {
        public $tagparams = array();
        public $tagdata = '';
        public $logs = array();
        public $ignore_fetch = array('url_title');

        /**
         * Match EE_Template's missing/null defaults and legacy yes/no aliases.
         *
         * @param string $which
         * @param mixed $default
         * @return mixed
         */
        public function fetch_param($which, $default = false)
        {
            if (!isset($this->tagparams[$which])) {
                return $default;
            }

            $value = $this->tagparams[$which];

            if (in_array($which, $this->ignore_fetch, true)) {
                return $value;
            }

            switch ($value) {
                case 'y':
                case 'on':
                    return 'yes';
                case 'n':
                case 'off':
                    return 'no';
                default:
                    return $value;
            }
        }

        /**
         * Preserve the exact candidate log for deterministic assertions.
         *
         * @param string $message
         * @return void
         */
        public function log_item($message)
        {
            $this->logs[] = $message;
        }
    }

    final class Loader
    {
        public $helpers = array();
        public $libraries = array();
        public $forgeLoads = 0;

        /**
         * Record helper dependencies; native test helpers are preloaded.
         *
         * @param string $name
         * @return void
         */
        public function helper($name)
        {
            $this->helpers[] = $name;
        }

        /**
         * Record required EE libraries.
         *
         * @param string $name
         * @return void
         */
        public function library($name)
        {
            $this->libraries[] = $name;
        }

        /**
         * Record schema-service initialization without touching an EE database.
         *
         * @return void
         */
        public function dbforge()
        {
            $this->forgeLoads++;
        }
    }

    final class Query
    {
        public $rows;
        public $num_rows;
        private $objects;
        private $currentRow = 0;
        private $rowData;

        /**
         * Represent a native Active Record query result.
         *
         * @param array $rows
         * @return void
         */
        public function __construct(array $rows)
        {
            $this->rows = $rows;
            $this->num_rows = count($rows);
            $this->objects = array_map(static function ($row) { return (object) $row; }, $rows);
        }

        /**
         * Return the native query row count.
         *
         * @return int
         */
        public function num_rows()
        {
            return $this->num_rows;
        }

        /**
         * Return the selected row as an array, retaining native row selection.
         *
         * @param int $index
         * @return array
         */
        public function row_array($index = 0)
        {
            return (array) $this->row_object($index);
        }

        /**
         * Match native named-field, SQL NULL, numeric row and type contracts.
         *
         * @param int|string $index
         * @param string $type
         * @return mixed
         */
        public function row($index = 0, $type = 'object')
        {
            if (!is_numeric($index)) {
                if (!is_array($this->rowData)) {
                    $this->rowData = $this->row_array(0);
                }

                if (array_key_exists($index, $this->rowData)) {
                    return $this->rowData[$index];
                }

                $index = 0;
            }

            return $type === 'object' ? $this->row_object($index) : $this->row_array($index);
        }

        /**
         * Keep the current native row when an out-of-range index is requested.
         *
         * @param int $index
         * @return object|array
         */
        public function row_object($index = 0)
        {
            if (!$this->objects) {
                return array();
            }

            if ($index != $this->currentRow && isset($this->objects[$index])) {
                $this->currentRow = $index;
            }

            return $this->objects[$this->currentRow];
        }

        /**
         * Return all rows in the requested native query-result shape.
         *
         * @param string $type
         * @return array
         */
        public function result($type = 'object')
        {
            return $type === 'object' ? $this->objects : $this->rows;
        }
    }

    final class Database
    {
        public $data_cache = array();
        public $tables = array();
        public $calls = array();
        public $results = array();
        public $getQueues = array();
        public $fields = array();
        public $primaryKeys = array();
        public $queries = array();
        public $journal;

        /**
         * Keep backend observations separate from each builder's pending state.
         *
         * @return void
         */
        public function __construct()
        {
            $this->journal = (object) array('calls' => array());
        }

        /**
         * Match EE's newQuery service: fresh builder and cache over one backend.
         *
         * @return self
         */
        public function newQuery()
        {
            $query = new self();

            foreach (array('tables', 'results', 'getQueues', 'fields', 'primaryKeys') as $property) {
                $query->$property =& $this->$property;
            }

            $query->journal = $this->journal;
            $this->queries[] = $query;
            return $query;
        }

        /**
         * Record the exact Active Record chain without mutating shared EE state.
         *
         * @param string $method
         * @param array $arguments
         * @return mixed
         */
        public function __call($method, array $arguments)
        {
            $this->calls[] = array($method, $arguments);
            $this->journal->calls[] = array('builder' => spl_object_id($this), 'call' => array($method, $arguments));

            if ($method === 'table_exists') {
                return in_array($arguments[0], $this->tables, true);
            }

            if ($method === 'field_exists') {
                return in_array($arguments[1] . '.' . $arguments[0], $this->fields, true);
            }

            if ($method === 'query') {
                $sql = "SHOW KEYS FROM exp_eeharbor_licenses WHERE Key_name = 'PRIMARY'";

                if ($arguments[0] !== $sql) {
                    throw new \RuntimeException('Unconfigured SQL query: ' . $arguments[0]);
                }

                return new Query($this->primaryKeys['eeharbor_licenses'] ?? array());
            }

            if ($method === 'get' || $method === 'get_where') {
                $table = $arguments[0] ?? '';

                if (!empty($this->getQueues[$table])) {
                    return new Query(array_shift($this->getQueues[$table]));
                }

                return new Query($this->results[$table] ?? array());
            }

            if ($method === 'count_all_results') {
                return count($this->results[$arguments[0]] ?? array());
            }

            if (in_array($method, array('insert', 'update', 'delete'), true)) {
                return true;
            }

            return $this;
        }
    }

    final class Configuration
    {
        public $items = array('site_id' => 1, 'base_url' => 'https://example.test/', 'site_license_key' => '', 'theme_folder_url' => 'https://example.test/themes/');

        /**
         * Read an explicitly configured EE value, preserving SQL-style nulls.
         *
         * @param string $key
         * @return mixed
         */
        public function item($key)
        {
            return $this->items[$key] ?? null;
        }

        /**
         * Return an EE configuration URL with exactly one trailing slash.
         *
         * @param string $key
         * @return string|false
         */
        public function slash_item($key)
        {
            $value = $this->item($key);
            return $value ? rtrim($value, '/') . '/' : false;
        }
    }

    final class SchemaForge
    {
        public $calls = array();
        private $database;
        private $fields = array();
        private $primaryKey;

        /**
         * Keep schema introspection consistent with the recorded DDL changes.
         *
         * @param Database $database
         * @return void
         */
        public function __construct(Database $database)
        {
            $this->database = $database;
        }

        /**
         * Queue field definitions for the next table creation.
         *
         * @param array $fields
         * @return void
         */
        public function add_field(array $fields)
        {
            $this->calls[] = array('add_field', $fields);
            $this->fields = $fields;
        }

        /**
         * Record secondary and primary key definitions in their actual order.
         *
         * @param string|array $key
         * @param bool $primary
         * @return void
         */
        public function add_key($key, $primary = false)
        {
            $this->calls[] = array('add_key', $key, $primary);

            if ($primary) {
                $this->primaryKey = $key;
            }
        }

        /**
         * Expose the newly created fields and key to subsequent schema queries.
         *
         * @param string $table
         * @return bool
         */
        public function create_table($table)
        {
            $this->calls[] = array('create_table', $table);
            $this->database->tables[] = $table;
            $this->addFieldsToDatabase($table, $this->fields);

            if ($this->primaryKey !== null) {
                $this->database->primaryKeys[$table] = array(array('Column_name' => $this->primaryKey, 'Non_unique' => '0'));
            }

            $this->fields = array();
            $this->primaryKey = null;
            return true;
        }

        /**
         * Apply an existing-table schema extension for the next introspection.
         *
         * @param string $table
         * @param array $fields
         * @return bool
         */
        public function add_column($table, array $fields)
        {
            $this->calls[] = array('add_column', $table, $fields);
            $this->addFieldsToDatabase($table, $fields);
            return true;
        }

        /**
         * Keep fully qualified field names consistent across both DDL paths.
         *
         * @param string $table
         * @param array $fields
         * @return void
         */
        private function addFieldsToDatabase($table, array $fields)
        {
            foreach (array_keys($fields) as $name) {
                $this->database->fields[] = $table . '.' . $name;
            }
        }
    }

    final class ControlPanel
    {
        public $footer = array();
        public $footer_item;
        public $breadcrumbs = array();

        /**
         * Expose EE2's footer collection alongside the current getter contract.
         *
         * @return void
         */
        public function __construct()
        {
            $this->footer_item = &$this->footer;
        }

        /**
         * Read the same footer entries that earlier add-on calls appended.
         *
         * @return array
         */
        public function get_foot()
        {
            return $this->footer;
        }

        /**
         * Append one fragment to the EE footer collection.
         *
         * @param string $html
         * @return void
         */
        public function add_to_foot($html)
        {
            $this->footer[] = $html;
        }

        /**
         * Record breadcrumb URL and title without rendering CP chrome.
         *
         * @param mixed $url
         * @param string $title
         * @return void
         */
        public function set_breadcrumb($url, $title)
        {
            $this->breadcrumbs[] = array($url, $title);
        }
    }

    final class FileQuery
    {
        public $rows = array();
        public $calls = array();

        /**
         * Record the real File model builder chain.
         *
         * @param string $method
         * @param array $arguments
         * @return mixed
         */
        public function __call($method, array $arguments)
        {
            $this->calls[] = array($method, $arguments);

            if ($method === 'all') {
                return $this->rows;
            }

            if ($method === 'first') {
                return $this->rows ? $this->rows[0] : null;
            }

            return $this;
        }
    }
}

namespace {
    /**
     * Delegate the legacy URL helper to EE's active URI object.
     *
     * @return string
     */
    function uri_string()
    {
        return ee()->uri->uri_string();
    }

    /**
     * Resolve the test EE container using native service names.
     *
     * @param string|null $service
     * @param mixed ...$arguments
     * @return mixed
     */
    function ee($service = null, ...$arguments)
    {
        if ($service === null) {
            return $GLOBALS['low_random_ee'];
        }

        if ($service === 'db') {
            return $GLOBALS['low_random_ee']->db->newQuery();
        }

        if (!isset($GLOBALS['low_random_services'][$service])) {
            throw new \RuntimeException('Unconfigured EE service: ' . $service);
        }

        $resolved = $GLOBALS['low_random_services'][$service];

        if (is_callable($resolved)) {
            return call_user_func_array($resolved, $arguments);
        }

        return $resolved;
    }

    /**
     * Match EE's recursion order, hidden-path guards and basename behavior.
     *
     * @param string $directory
     * @param bool $includePath
     * @return array|false
     */
    function get_filenames($directory, $includePath = false)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = array();
        $directory = rtrim(realpath($directory), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $handle = opendir($directory);

        try {
            while (($name = readdir($handle)) !== false) {
                if (substr($name, 0, 1) === '.') {
                    continue;
                }

                $path = $directory . $name;

                if (is_dir($path)) {
                    $files = array_merge($files, get_filenames($path, $includePath));
                    continue;
                }

                $files[] = $includePath ? $path : $name;
            }
        } finally {
            closedir($handle);
        }

        return $files;
    }

    /**
     * Read the cache file as EE's helper does.
     *
     * @param string $path
     * @return string|false
     */
    function read_file($path)
    {
        return is_file($path) ? file_get_contents($path) : false;
    }

    /**
     * Write the cache file through the real filesystem.
     *
     * @param string $path
     * @param string $data
     * @return bool
     */
    function write_file($path, $data)
    {
        return file_put_contents($path, $data) !== false;
    }

    /**
     * Record form helper arguments and return an escaped representation.
     *
     * @param string $name
     * @param array $arguments
     * @return string
     */
    function low_random_form($name, array $arguments)
    {
        $GLOBALS['low_random_forms'][] = array($name, $arguments);
        return htmlspecialchars(json_encode(array($name, $arguments)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Record the native form_open helper arguments.
     *
     * @param string $url
     * @param array $attributes
     * @return string
     */

    function form_open($url, $attributes) { return low_random_form('open', array($url, $attributes)); }
    /**
     * Record the native form_input helper arguments.
     *
     * @param string $name
     * @param mixed $value
     * @return string
     */
    function form_input($name, $value) { return low_random_form('input', array($name, $value)); }
    /**
     * Record the native form_checkbox helper arguments.
     *
     * @param string $name
     * @param mixed $value
     * @param bool $checked
     * @return string
     */
    function form_checkbox($name, $value, $checked) { return low_random_form('checkbox', array($name, $value, $checked)); }
    /**
     * Record the native form_close helper arguments.
     *
     * @return string
     */
    function form_close() { return low_random_form('close', array()); }
}
