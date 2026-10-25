<?php

namespace Low\Random\FluxCapacitor\Conduit {
    /**
     * Preserve original URL length behavior while detecting the new payload operation.
     *
     * @param string $value Existing URL or an unrelated JSON payload.
     * @return int Original URL length, with a sentinel for an incorrectly dispatched payload.
     */
    function strlen($value)
    {
        if (substr($value, 0, 1) === '{') {
            $GLOBALS['newPayloadCalls']++;
            return PHP_INT_MAX;
        }
        return \strlen($value);
    }
}

namespace {
    $newPayloadCalls = 0;
    register_shutdown_function(static function (): void {
        if ($GLOBALS['newPayloadCalls'] !== 0) {
            throw new \RuntimeException('New native payload length invoked an unrelated namespace hook.');
        }
    });
    array_splice($argv, 1, 0, array('version-check'));
    require __DIR__ . '/native.php';
}
