<?php

define('BASEPATH', __DIR__);
$prefix = $argv[1] ?? 'rootcache';
$handlerMode = $argv[2] ?? '';
$artifact = $argv[3] ?? dirname(__DIR__, 2) . '/low_random/low_random.phar';
define('PATH_CACHE', $prefix . '://cache/');
define('DIR_WRITE_MODE', 0700);
define('FILE_WRITE_MODE', 0600);
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb');
/**
 * Mirror EE's native file-read boundary for the in-memory collaborator.
 *
 * @param string $path Original cache path.
 * @return string|false Native file contents or a miss.
 */
function read_file($path) { return file_exists($path) ? file_get_contents($path) : false; }

/**
 * Mirror the real EE helper's lock/write/close behavior.
 *
 * @param string $path Native cache destination.
 * @param string $bytes Original serialized value.
 * @return bool Whether the file was opened and written.
 */
function write_file($path, $bytes)
{
    $file = fopen($path, 'wb');
    if ($file === false) { return false; }
    flock($file, LOCK_EX);
    fwrite($file, $bytes);
    flock($file, LOCK_UN);
    fclose($file);
    return true;
}

final class RootVirtualCache
{
    public $context;
    public static $directories = array();
    public static $files = array();
    public static $created = array();
    public static $onCreate;
    private $path;
    private $offset = 0;

    /**
     * Report writable virtual entries without POSIX search bits.
     *
     * @param string $path
     * @param int $flags
     * @return array|false
     */
    public function url_stat($path, $flags)
    {
        $name = rtrim($path, '/');
        if (!isset(self::$directories[$name]) && !isset(self::$files[$name])) { return false; }
        return array('dev'=>0,'ino'=>1,'mode'=>isset(self::$directories[$name])?0040666:0100666,'nlink'=>1,'uid'=>getmyuid(),'gid'=>getmygid(),'rdev'=>0,'size'=>strlen(self::$files[$name]??''),'atime'=>1,'mtime'=>1,'ctime'=>1,'blksize'=>4096,'blocks'=>1);
    }
    /**
     * Create the native requested virtual directory.
     *
     * @param string $path
     * @param int $mode
     * @param int $options
     * @return bool
     * @throws Throwable When the native stream callback fails.
     */
    public function mkdir($path, $mode, $options)
    {
        self::$created[] = $path;
        self::$directories[rtrim($path, '/')] = true;
        if (self::$onCreate !== null) {
            (self::$onCreate)();
        }
        return true;
    }
    /**
     * Open a file owned by this in-memory stream.
     *
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string|null $opened
     * @return bool
     */
    public function stream_open($path,$mode,$options,&$opened) { $this->path=$path;$this->offset=0;if(strpos($mode,'w')!==false){self::$files[$path]='';}return isset(self::$files[$path]); }
    /**
     * Write native EE helper bytes.
     *
     * @param string $bytes
     * @return int
     */
    public function stream_write($bytes) { self::$files[$this->path].=$bytes;$this->offset+=strlen($bytes);return strlen($bytes); }
    /**
     * Read the original serialized file bytes.
     *
     * @param int $count
     * @return string
     */
    public function stream_read($count) { $bytes=substr(self::$files[$this->path],$this->offset,$count);$this->offset+=strlen($bytes);return $bytes; }
    /**
     * Report whether all bytes were consumed.
     *
     * @return bool
     */
    public function stream_eof() { return $this->offset>=strlen(self::$files[$this->path]); }
    /**
     * Report an open file's native position.
     *
     * @return int
     */
    public function stream_tell() { return $this->offset; }
    /**
     * Supply the same virtual file metadata.
     *
     * @return array|false
     */
    public function stream_stat() { return $this->url_stat($this->path,0); }
    /**
     * Permit the real EE helper's advisory lock.
     *
     * @param int $operation
     * @return bool
     */
    public function stream_lock($operation) { return true; }
    /**
     * Accept the helper's directory/file metadata operation.
     *
     * @param string $path
     * @param int $option
     * @param mixed $value
     * @return bool
     */
    public function stream_metadata($path,$option,$value) { return true; }
}
stream_wrapper_register($prefix, RootVirtualCache::class);
$logs=array();$app=(object)array('load'=>new class {
    /**
     * Keep the actual helper already loaded.
     *
     * @param string $name
     * @return void
     */
    public function helper($name) {}
    /**
     * Keep the owned logger available.
     *
     * @param string $name
     * @return void
     */
    public function library($name) {}
}, 'logger'=>new class {
    /**
     * Retain developer fallback messages.
     *
     * @param string $message
     * @return void
     */
    public function developer($message) { $GLOBALS['logs'][]=$message; }
});
function ee(){return $GLOBALS['app'];}
require 'phar://' . $artifact . '/FluxCapacitor/Conduit/StaticCache.php';
require 'phar://' . $artifact . '/FluxCapacitor/Conduit/PersistentCache.php';

/**
 * Supply a quiet caller-owned handler beneath the stream callback's handlers.
 *
 * @return bool Whether this handler consumed a diagnostic.
 */
function rootVirtualPriorHandler() { return false; }

/**
 * Identify a quiet handler installed by the first directory callback.
 *
 * @return bool Whether this handler consumed a diagnostic.
 */
function rootVirtualFirstHandler() { return false; }

/**
 * Identify a quiet handler installed by the second directory callback.
 *
 * @return bool Whether this handler consumed a diagnostic.
 */
function rootVirtualSecondHandler() { return false; }

/**
 * Inspect the native active callable without consuming its restore-stack entry.
 *
 * @return callable|null Native active diagnostic handler.
 */
function rootVirtualCurrentHandler()
{
    $handler = set_error_handler('rootVirtualPriorHandler');
    restore_error_handler();
    return $handler;
}

$originalHandler = null;
$callbackCount = 0;
$hookCalls = 0;
if ($handlerMode !== '') {
    $originalHandler = set_error_handler('rootVirtualPriorHandler', E_USER_NOTICE);
    RootVirtualCache::$onCreate = static function () use ($handlerMode, &$callbackCount) {
        $callbackCount++;
        if ($handlerMode === 'balanced') {
            set_error_handler('rootVirtualFirstHandler', E_USER_NOTICE);
            restore_error_handler();
        }
        if (($handlerMode === 'install' || $handlerMode === 'opaque-native') && $callbackCount === 1) {
            set_error_handler('rootVirtualFirstHandler', E_USER_NOTICE);
        }
        if ($handlerMode === 'stack') {
            set_error_handler($callbackCount === 1 ? 'rootVirtualFirstHandler' : 'rootVirtualSecondHandler', E_USER_NOTICE);
        }
        if (($handlerMode === 'late-hook' || $handlerMode === 'module-first') && $callbackCount === 1) {
            rootVirtualRegisterHook();
        }
    };
    if ($handlerMode === 'early-hook') {
        rootVirtualRegisterHook();
    }
    if ($handlerMode === 'module-first' || $handlerMode === 'opaque-native') {
        RootVirtualCache::$directories[rtrim(PATH_CACHE, '/')] = true;
    }
}

if ($handlerMode === 'opaque-native') {
    $module = new class {
        /**
         * Leave native path coercion to the filesystem operations that own it.
         *
         * @return string Native virtual module directory.
         */
        public function __toString(): string { return PATH_CACHE . 'low_random/'; }
    };
    $setModule = Closure::bind(static function ($path) { self::$module_cache_path = $path; }, null,
        Low\Random\FluxCapacitor\Conduit\PersistentCache::class);
    $setModule($module);
}

/**
 * Register a legal namespaced mkdir after a native callsite has first resolved.
 *
 * @return void
 */
function rootVirtualRegisterHook()
{
    eval('namespace Low\\Random\\FluxCapacitor\\Conduit; function mkdir(&$path, $mode) { $GLOBALS["hookCalls"]++; return \\mkdir($path, $mode); }');
}

$value=Low\Random\FluxCapacitor\Conduit\PersistentCache::set('owned',array('keep'=>7));
Low\Random\FluxCapacitor\Conduit\StaticCache::clear();
$read=$value?Low\Random\FluxCapacitor\Conduit\PersistentCache::get('owned'):null;
$observed = array('set' => $value, 'read' => $read, 'directories' => count(RootVirtualCache::$created),
    'files' => count(RootVirtualCache::$files), 'logs' => $logs);
if ($handlerMode !== '') {
    if ($handlerMode === 'native-first' || $handlerMode === 'module-first') {
        if ($handlerMode === 'native-first') {
            rootVirtualRegisterHook();
        }
        RootVirtualCache::$directories = array();
        $observed['second_set'] = Low\Random\FluxCapacitor\Conduit\PersistentCache::set('second', array('keep' => 8));
        $observed['directories'] = count(RootVirtualCache::$created);
        $observed['files'] = count(RootVirtualCache::$files);
    }
    $observed['hook_calls'] = $hookCalls;
    $stack = array();
    do {
        $handler = rootVirtualCurrentHandler();
        $stack[] = $handler === $originalHandler ? 'original' : (is_string($handler) ? $handler : gettype($handler));
        if ($handler === $originalHandler) {
            break;
        }
        restore_error_handler();
    } while (count($stack) < 6);
    $observed['handler_stack'] = $stack;
}
echo json_encode($observed, JSON_THROW_ON_ERROR);
