<?php

define('BASEPATH', __DIR__);
$prefix = $argv[1] ?? 'rootcache';
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
     */
    public function mkdir($path, $mode, $options) { self::$created[]=$path;self::$directories[rtrim($path,'/')]=true;return true; }
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
require 'phar://'.(dirname(__DIR__,2).'/low_random/low_random.phar').'/FluxCapacitor/Conduit/StaticCache.php';
require 'phar://'.(dirname(__DIR__,2).'/low_random/low_random.phar').'/FluxCapacitor/Conduit/PersistentCache.php';
$value=Low\Random\FluxCapacitor\Conduit\PersistentCache::set('owned',array('keep'=>7));
Low\Random\FluxCapacitor\Conduit\StaticCache::clear();
$read=$value?Low\Random\FluxCapacitor\Conduit\PersistentCache::get('owned'):null;
echo json_encode(array('set'=>$value,'read'=>$read,'directories'=>count(RootVirtualCache::$created),'files'=>count(RootVirtualCache::$files),'logs'=>$logs),JSON_THROW_ON_ERROR);
