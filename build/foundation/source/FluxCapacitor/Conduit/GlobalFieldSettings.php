<?php

namespace Low\Random\FluxCapacitor\Conduit;

use Low\Random\FluxCapacitor\FluxCapacitor;

class GlobalFieldSettings
{
    public $flux;
    public $fieldtype_name;
    public function __construct($fieldtype_name=null)
    {
        $this->flux = new FluxCapacitor;
        $this->fieldtype_name = $fieldtype_name ?: $this->flux->shortname;
    }

    /**
     * Read native array keys and string offsets while preserving custom ArrayAccess reads.
     *
     * @param int|float|string|null $name Setting key or native string byte offset.
     * @return mixed
     * @throws \Throwable When native hydration or a consumer callback fails.
     */
    public function getSetting($name)
    {
        $ftSettings = $this->getSettings();
        if ($ftSettings instanceof \ArrayAccess) {
            return @$ftSettings[$name];
        }

        if (\is_array($ftSettings) && $name === null) {
            $name = '';
        }
        if (\is_array($ftSettings) && \is_float($name)) {
            $range = 2.0 ** (\PHP_INT_SIZE * 8);
            $name = \is_finite($name) ? \fmod($name, $range) : 0;
            if ($name >= $range / 2) {
                $name -= $range;
            }
            if ($name < -$range / 2) {
                $name += $range;
            }
            $name = (int) $name;
        }

        if (\is_array($ftSettings) || \is_string($ftSettings)) {
            return isset($ftSettings[$name]) ? $ftSettings[$name] : null;
        }
        return null;
    }

    /**
     * Read native settings values, retaining the historical empty-value fallback.
     *
     * @return mixed Null if the fieldtype is absent; otherwise its stored value.
     * @throws \Throwable When object hydration raises an unexpected error.
     */
    public function getSettings()
    {
        $ftSettingsQuery = ee()->db->select('settings')
            ->where('name', $this->fieldtype_name)
            ->get('fieldtypes');

        if ((bool) $ftSettingsQuery->num_rows()) {
            // Class statics retain the native callsite lifetime across PHP 7.4 subclasses.
            $bindings = new class {
                public static bool $parser;
                public static bool $carrier;
            };
            if (!isset($bindings::$parser)) {
                $bindings::$parser = \function_exists(__NAMESPACE__ . '\\unserialize');
                $bindings::$carrier = \function_exists(__NAMESPACE__ . '\\base64_decode');
            }
            $normalizer = require __DIR__ . '/NativeSettings.php';
            /**
             * Retain raw native operands while spelling only null and NaN before decoding.
             *
             * @param mixed $carrier Original fieldtype row operand.
             * @return mixed Original operand or its quiet native null/NaN spelling.
             * @throws \Throwable When native conversion or a consumer callback fails.
             */
            $nativeCarrier = static function ($carrier) {
                if (\is_float($carrier) && \is_nan($carrier)) {
                    $carrier = 'NAN';
                }
                return $carrier === null ? '' : $carrier;
            };
            if ($bindings::$parser) {
                $decode = __NAMESPACE__ . '\\unserialize';
                if ($bindings::$carrier) {
                    $decodeCarrier = __NAMESPACE__ . '\\base64_decode';
                    $ftSettings = @$decode(@$decodeCarrier($ftSettingsQuery->row('settings')));
                } else {
                    $ftSettings = @$decode(@\base64_decode($nativeCarrier($ftSettingsQuery->row('settings'))));
                }
            } else {
                if ($bindings::$carrier) {
                    $decodeCarrier = __NAMESPACE__ . '\\base64_decode';
                    $ftSettings = @\unserialize($normalizer->normalize(@$decodeCarrier($ftSettingsQuery->row('settings')), true));
                } else {
                    $ftSettings = @\unserialize($normalizer->normalize(@\base64_decode($nativeCarrier($ftSettingsQuery->row('settings'))), true));
                }
            }
            if (!\is_float($ftSettings) || !\is_nan($ftSettings)) {
                $ftSettings = $ftSettings ?: array();
            }
        } else {
            return null;
        }

        return $ftSettings;
    }

    /**
     * Update a native array, ArrayAccess key or writable stored-string offset.
     *
     * @param mixed $key Setting key or native string byte offset.
     * @param mixed $value Native serializable setting value.
     * @return bool Whether an update was requested for a writable settings value.
     * @throws \Throwable When object hydration or serialization fails.
     */
    public function updateSetting($key, $value)
    {
        $ftSettings = $this->getSettings();

        $stringOffset = false;
        if (\is_string($ftSettings)) {
            $pattern = \PHP_VERSION_ID < 80000 ? '/^\s*[+-]?[0-9]+$/D' : '/^\s*[+-]?[0-9]+\s*$/D';
            $integerKey = \is_int($key) || (\is_string($key) && \preg_match($pattern, $key));
            $stringOffset = $integerKey && $key >= -\strlen($ftSettings) && $key < \PHP_INT_MAX;
        }

        if (!is_null($ftSettings)) {
            $offsetKey = null;
            if (\is_array($ftSettings) && $key === null) {
                $offsetKey = '';
            }
            if (\is_array($ftSettings) && \is_float($key)) {
                $range = 2.0 ** (\PHP_INT_SIZE * 8);
                $offsetKey = \is_finite($key) ? \fmod($key, $range) : 0;
                if ($offsetKey >= $range / 2) {
                    $offsetKey -= $range;
                }
                if ($offsetKey < -$range / 2) {
                    $offsetKey += $range;
                }
                $offsetKey = (int) $offsetKey;
            }

            if (\is_string($ftSettings) && $stringOffset) {
                $replacement = \is_float($value) && \is_nan($value) ? 'NAN' : (string) $value;
                if (\strlen($replacement) > 1) {
                    $replacement = $replacement[0];
                }
                $ftSettings[$key] = $replacement;
            } else {
                $ftSettings[$offsetKey === null ? $key : $offsetKey] = $value;
            }
            $settingsForDb = array('settings' => base64_encode(serialize($ftSettings)));

            ee()->db->where('name', $this->fieldtype_name)
                ->update('fieldtypes', $settingsForDb);
        } else {
            return false;
        }

        return true;
    }

}
