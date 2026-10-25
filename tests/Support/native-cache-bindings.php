<?php

namespace Low\Random\FluxCapacitor\Conduit {
    if (substr($argv[1], 3) === 'filter') {
        /**
         * Register a late hash hook while the original nested filter is evaluated.
         *
         * @param array $key Raw original cache pieces.
         * @param callable|null $callback Native optional filtering predicate.
         * @return array Filtered original pieces.
         */
        function array_filter($key, $callback = null)
        {
            $GLOBALS['filterCalls']++;
            if (!\function_exists(__NAMESPACE__ . '\\sha1')) {
                /**
                 * Observe an incorrectly rebound hash operation.
                 *
                 * @param string $value
                 * @return string
                 */
                function sha1($value) { $GLOBALS['bindingCalls']++; return 'late-' . $GLOBALS['bindingCalls']; }
            }
            return $callback === null ? \array_filter($key) : \array_filter($key, $callback);
        }
    }
    if (substr($argv[1], 3) === 'arity') {
        /**
         * Select stored bytes using the original one-argument filter contract.
         *
         * @param array $key Raw original cache pieces.
         * @return array Original hook-selected scalar key.
         */
        function array_filter($key)
        {
            $GLOBALS['filterArguments'][] = \func_num_args();
            return array(\func_num_args() === 1 ? 'selected' : 'redirected');
        }
    }
    if (substr($argv[1], 3) === 'references') {
        /**
         * Retain the original filter's returned reference.
         *
         * @param array $key
         * @param callable|null $callback
         * @return array
         */
        function &array_filter($key, $callback = null) { $GLOBALS['rawPieces'] = $key; return $GLOBALS['rawPieces']; }
        /**
         * Retain both the original pieces alias and returned byte reference.
         *
         * @param string $separator
         * @param array $pieces
         * @return string
         */
        function &implode($separator, &$pieces) { $GLOBALS['joinedBytes'] = \implode($separator, $pieces); return $GLOBALS['joinedBytes']; }
        /**
         * Mutate the actual original returned byte reference.
         *
         * @param string $bytes
         * @return string
         */
        function sha1(&$bytes) { $value = \sha1($bytes); $bytes .= ':observed'; return $value; }
    }
}

namespace {
$artifact = $argv[3] ?? dirname(__DIR__, 2) . '/low_random/low_random.phar';
$kind = $argv[1];
$root = $argv[2];
define('PATH_CACHE', $root . '/cache/');
define('DIR_WRITE_MODE', 0700);
define('FILE_WRITE_MODE', 0600);
$bindingCalls = 0;
$filterCalls = 0;
$family = substr($kind, 0, 2) === 'pc' ? 'PersistentCache' : 'StaticCache';
$mode = substr($kind, 3);
require 'phar://' . $artifact . '/FluxCapacitor/Conduit/StaticCache.php';
require 'phar://' . $artifact . '/FluxCapacitor/Conduit/PersistentCache.php';

/**
 * Match the real EE file helper's full-write boolean boundary.
 *
 * @param string $path Native cache destination.
 * @param string $data Serialized cache bytes.
 * @return bool Whether the file was written.
 */
function write_file($path, $data) { return file_put_contents($path, $data) === strlen($data); }

$bindingApp = (object) array('load' => new class {
    /**
     * Accept the required helper already represented here.
     *
     * @param string $name
     * @return void
     */
    public function helper($name) {}
    /**
     * Accept the owned logger.
     *
     * @param string $name
     * @return void
     */
    public function library($name) {}
}, 'logger' => new class {
    /**
     * Refuse an unexpected fallback in a positive native contract.
     *
     * @param string $message
     * @return void
     * @throws RuntimeException
     */
    public function developer($message) { throw new RuntimeException($message); }
});

/**
 * Supply only the actual producer's EE collaborator boundary.
 *
 * @return object
 */
function ee() { return $GLOBALS['bindingApp']; }

$class = 'Low\\Random\\FluxCapacitor\\Conduit\\' . $family;
if ($mode === 'arity') {
    $GLOBALS['filterArguments'] = array();
    $class::set('entry', 'kept');
    $hash = \sha1('selected');
    $value = \Low\Random\FluxCapacitor\Conduit\StaticCache::get($hash, true);
    $stored = $family !== 'PersistentCache' || is_file(PATH_CACHE . 'low_random/' . $hash);
    echo json_encode(array('value' => $value, 'calls' => $bindingCalls,
        'arity' => $GLOBALS['filterArguments'], 'stored' => $stored), JSON_THROW_ON_ERROR);
    exit;
}
$class::set('warm', 'first');
if ($mode === 'hash' || $mode === 'join') {
    $function = $mode === 'hash' ? 'sha1' : 'implode';
    $parameters = $mode === 'hash' ? '$value' : '$separator, $pieces';
    eval('namespace Low\\Random\\FluxCapacitor\\Conduit; function ' . $function . '(' . $parameters . ') { $GLOBALS["bindingCalls"]++; return "late-" . $GLOBALS["bindingCalls"]; }');
}
$key = array('alpha', 'beta');
$class::set($key, 'kept');
$value = $family === 'PersistentCache' ? $class::has($key) : $class::get($key);
 $observed = array('value' => $value, 'calls' => $bindingCalls);
 if ($mode === 'references') { $observed['pieces'] = $GLOBALS['rawPieces']; $observed['joined'] = $GLOBALS['joinedBytes']; }
 echo json_encode($observed, JSON_THROW_ON_ERROR);

}
