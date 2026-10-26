<?php

$root = dirname(__DIR__, 2);
$mode = $argv[1];
$events = array();

final class SettingsV21Database
{
    public $blob;
    public $written;
    public $throws = false;

    /**
     * Supply only the native fluent fieldtype query and observe its real write.
     *
     * @param string $name Database interaction.
     * @param array $arguments Original interaction arguments.
     * @return mixed Query result or write result.
     * @throws RuntimeException When the application rejects a database write.
     */
    public function __call($name, $arguments)
    {
        if ($name === 'num_rows') { return 1; }
        if ($name === 'row') { return $this->blob; }
        if ($name === 'update') {
            $GLOBALS['events'][] = 'write';
            if ($this->throws) { throw new RuntimeException('database'); }
            $this->written = $arguments[1];
            return true;
        }
        return $this;
    }
}

final class SettingsV21Temporary
{
    private $name;
    private $throws;

    /**
     * Select a native argument with observable application cleanup.
     *
     * @param string $name Argument label.
     * @param bool $throws Whether cleanup rejects release.
     */
    public function __construct($name, $throws = false) { $this->name = $name; $this->throws = $throws; }

    /**
     * Supply a quiet one-byte string-offset replacement.
     *
     * @return string Native replacement bytes.
     */
    public function __toString(): string { $GLOBALS['events'][] = 'convert'; return 'l'; }

    /**
     * Observe release relative to persistence and the other native arguments.
     *
     * @return void
     * @throws RuntimeException When application cleanup rejects release.
     */
    public function __destruct()
    {
        $GLOBALS['events'][] = $this->name . '-destroy';
        if ($this->throws) { throw new RuntimeException('destructor'); }
    }
}

final class SettingsV21ArrayAccess implements ArrayAccess
{
    /**
     * Keep a computed native ArrayAccess value independent of membership.
     *
     * @param mixed $key Native caller key.
     * @return bool Whether the key exists.
     */
    public function offsetExists($key): bool { return false; }

    /**
     * Supply a quiet computed value for an absent key.
     *
     * @param mixed $key Native caller key.
     * @return string Computed value.
     */
    public function offsetGet($key): string { return ''; }

    /**
     * Observe assignment without retaining either temporary application argument.
     *
     * @param mixed $key Native caller key.
     * @param mixed $value Native caller value.
     * @return void
     */
    public function offsetSet($key, $value): void { $GLOBALS['events'][] = 'assign'; }

    /**
     * Accept a native removal without changing argument ownership.
     *
     * @param mixed $key Native caller key.
     * @return void
     */
    public function offsetUnset($key): void {}

    /**
     * Record dictionary release relative to key and value cleanup.
     *
     * @return void
     */
    public function __destruct() { $GLOBALS['events'][] = 'root-destroy'; }
}

final class SettingsV21SerializedValue
{
    public $key;

    /**
     * Preserve the public caller argument observed by application serialization.
     *
     * @return array Native state derived from the original method argument.
     */
    public function __serialize(): array
    {
        foreach (debug_backtrace(0) as $frame) {
            if ($frame['function'] === 'updateSetting') { return array('key' => $frame['args'][0]); }
        }
        throw new RuntimeException('Missing public setting frame.');
    }
}

final class SettingsV21Decoded
{
    /**
     * Derive actual valid settings from the native conversion context.
     *
     * @return string Native serialized bytes.
     */
    public function __toString(): string
    {
        $frames = debug_backtrace(0, 2);
        return serialize($frames[1]['function'] === 'unserialize' ? 'native' : 'changed');
    }
}

final class SettingsV21Encoded
{
    /**
     * Supply encoded bytes through the native base64 conversion frame.
     *
     * @return string Encoded native settings derived from the immediate caller.
     */
    public function __toString(): string
    {
        $frames = debug_backtrace(0, 3);
        $native = $frames[1]['function'] === 'base64_decode' && $frames[2]['function'] === 'getSettings';
        return base64_encode(serialize($native ? 'native' : 'changed'));
    }
}

/**
 * Supply the fieldtype database boundary without constructing an application.
 *
 * @return object The configured database collaborator.
 */
function ee() { return $GLOBALS['app']; }

if ($mode === 'native-frame') {
    eval('namespace Low\\Random\\FluxCapacitor\\Conduit; function base64_decode($value) { return new \\SettingsV21Decoded(); }');
}
$artifact = $argv[2] ?? $root . '/low_random/low_random.phar';
require 'phar://' . $artifact . '/FluxCapacitor/Conduit/GlobalFieldSettings.php';
$database = new SettingsV21Database();
$app = (object) array('db' => $database);
$settings = (new ReflectionClass('Low\\Random\\FluxCapacitor\\Conduit\\GlobalFieldSettings'))->newInstanceWithoutConstructor();
$settings->fieldtype_name = 'owned';
$database->blob = base64_encode(serialize('abc'));

if ($mode === 'encoded-native-frame') {
    $database->blob = new SettingsV21Encoded();
    echo json_encode($settings->getSettings(), JSON_THROW_ON_ERROR);
    exit(0);
}
if ($mode === 'native-frame') {
    echo json_encode($settings->getSettings(), JSON_THROW_ON_ERROR);
    exit(0);
}
if ($mode === 'nan') {
    $database->blob = base64_encode('d:NAN;');
    echo json_encode(is_nan($settings->getSettings()), JSON_THROW_ON_ERROR);
    exit(0);
}
if ($mode === 'snapshot') {
    ob_start();
    require $root . '/tests/.phpunit/phpunit-9.phar';
    $prefix = ob_get_clean();
    if ($prefix !== '' && $prefix !== "#!/usr/bin/env php\n") { throw new RuntimeException('Unexpected pinned tooling prefix.'); }
    $snapshot = new PHPUnitPHAR\SebastianBergmann\GlobalState\Snapshot(null, false, true, false, false, false, false, false, false, false);
    $database->blob = base64_encode(serialize(7));
    $values = array($settings->getSettings());
    eval('namespace Low\\Random\\FluxCapacitor\\Conduit; function unserialize($value) { return "late"; }');
    (new PHPUnitPHAR\SebastianBergmann\GlobalState\Restorer())->restoreStaticAttributes($snapshot);
    $values[] = $settings->getSettings();
    echo json_encode($values, JSON_THROW_ON_ERROR);
    exit(0);
}
if ($mode === 'raw-key') {
    $values = array();
    $database->blob = base64_encode(serialize(array()));
    foreach (array(null, 1.9) as $key) {
        $settings->updateSetting($key, new SettingsV21SerializedValue());
        $stored = unserialize(base64_decode($database->written['settings']));
        $values[] = reset($stored)->key;
    }
    echo json_encode($values, JSON_THROW_ON_ERROR);
    exit(0);
}
if ($mode === 'array-access') {
    $database->blob = base64_encode(serialize(new SettingsV21ArrayAccess()));
    $events = array();
    $settings->updateSetting(new SettingsV21Temporary('key'), new SettingsV21Temporary('value'));
    echo json_encode($events, JSON_THROW_ON_ERROR);
    exit(0);
}
$rows = array();
foreach (array(array(false, false), array(true, false), array(false, true), array(true, true)) as $failure) {
    $events = array();
    $database->throws = $failure[0];
    $database->written = null;
    $errors = array();
    try {
        $settings->updateSetting(1, new SettingsV21Temporary('value', $failure[1]));
    } catch (Throwable $error) {
        do { $errors[] = $error->getMessage(); $error = $error->getPrevious(); } while ($error !== null);
    }
    $rows[] = array('events' => $events, 'errors' => $errors, 'stored' => $database->written === null ? null : unserialize(base64_decode($database->written['settings'])));
}
echo json_encode($rows, JSON_THROW_ON_ERROR);
