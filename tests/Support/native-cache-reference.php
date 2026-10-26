<?php

namespace Low\Random\FluxCapacitor\Conduit {
    /**
     * Keep a legal namespace implementation's reference update visible to its caller.
     *
     * @param string $path Original cache path property, passed by reference.
     * @param int $mode Original native directory permissions.
     * @return bool Whether the requested directory was created.
     */
    function mkdir(&$path, $mode)
    {
        if ($GLOBALS['reference_handlers']) {
            \set_error_handler($GLOBALS['reference_calls'] === 0 ? 'referenceFirstHandler' : 'referenceSecondHandler', \E_USER_NOTICE);
        }
        $target = $GLOBALS['reference_root'] . '/rebound/' . ($GLOBALS['reference_calls']++ ? 'module/' : '');
        $path = $GLOBALS['reference_opaque'] ? new \ReferenceNativePath($target) : $target;
        return \mkdir($path, $mode);
    }
}

namespace {
    define('PATH_CACHE', $argv[1] . '/cache/');
    define('DIR_WRITE_MODE', 0700);
    define('FILE_WRITE_MODE', 0600);
    $reference_root = $argv[1];
    $reference_calls = 0;
    $reference_opaque = ($argv[2] ?? '') === 'opaque';
    $reference_conversions = 0;
    $reference_handlers = ($argv[4] ?? '') === 'handlers';
    $artifact = $argv[3] ?? dirname(__DIR__, 2) . '/low_random/low_random.phar';
    require 'phar://' . $artifact . '/FluxCapacitor/Conduit/StaticCache.php';
    require 'phar://' . $artifact . '/FluxCapacitor/Conduit/PersistentCache.php';

    final class ReferenceNativePath
    {
        private $path;

        /**
         * Retain a native Stringable operand written into the original private property.
         *
         * @param string $path Native destination selected by the mkdir hook.
         * @return void
         */
        public function __construct($path) { $this->path = $path; }

        /**
         * Count only conversions requested by the actual shipped producer.
         *
         * @return string Original native path spelling.
         */
        public function __toString(): string { $GLOBALS['reference_conversions']++; return $this->path; }
    }

    final class ReferenceCacheLoader
    {
        /**
         * Accept the framework helper requested by the cache.
         *
         * @param string $name Required framework helper.
         * @return void
         */
        public function helper($name) {}
        /**
         * Accept the framework library requested by the cache.
         *
         * @param string $name Required framework library.
         * @return void
         */
        public function library($name) {}
    }

    final class ReferenceCacheLogger
    {
        public $messages = array();
        /**
         * Record the actual cache developer message.
         *
         * @param string $message Original developer message.
         * @return void
         */
        public function developer($message) { $this->messages[] = $message; }
    }

    /**
     * Write actual filesystem bytes through the framework helper's boolean boundary.
     *
     * @param string $path Original add-on write destination.
     * @param string $data Original add-on serialized cache payload.
     * @return bool Whether all bytes were written.
     */
    function write_file($path, $data) { return file_put_contents($path, $data) === strlen($data); }

    /**
     * Supply only the loader and logger used by actual directory creation.
     *
     * @return object Framework collaborator boundary.
     */
    function ee() { return $GLOBALS['reference_app']; }

    $reference_app = (object) array('load' => new ReferenceCacheLoader(), 'logger' => new ReferenceCacheLogger());
    /**
     * Supply quiet native caller and namespace callback handler identities.
     *
     * @return bool Whether this handler consumed a diagnostic.
     */
    function referencePriorHandler() { return false; }

    /**
     * Identify the generic namespace mkdir callback's installed handler.
     *
     * @return bool Whether this handler consumed a diagnostic.
     */
    function referenceFirstHandler() { return false; }

    /**
     * Identify the module namespace mkdir callback's installed handler.
     *
     * @return bool Whether this handler consumed a diagnostic.
     */
    function referenceSecondHandler() { return false; }

    $originalHandler = $reference_handlers ? set_error_handler('referencePriorHandler', E_USER_NOTICE) : null;
    $result = Low\Random\FluxCapacitor\Conduit\PersistentCache::set('entry', 'stored');
    $target = $reference_root . '/rebound/module/' . sha1('entry');
    $observed = array('value' => $result, 'mkdir_calls' => $reference_calls,
        'saved' => is_file($target), 'logs' => $reference_app->logger->messages);
    if ($reference_opaque) { $observed['conversions'] = $reference_conversions; }
    if ($reference_handlers) {
        $stack = array();
        do {
            $handler = set_error_handler('referencePriorHandler');
            restore_error_handler();
            $stack[] = $handler === $originalHandler ? 'original' : (is_string($handler) ? $handler : gettype($handler));
            if ($handler === $originalHandler) {
                break;
            }
            restore_error_handler();
        } while (count($stack) < 6);
        $observed['handler_stack'] = $stack;
    }
    echo json_encode($observed, JSON_THROW_ON_ERROR);
}
