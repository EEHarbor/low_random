<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings;

final class StoredHandlerContractValue
{
    /**
     * Exercise native callback dispatch without modifying the handler stack.
     *
     * @return void
     */
    public function __wakeup()
    {
        trigger_error('Stored callback native severity contract', E_USER_WARNING);
    }
}

final class StoredHandlerContractTest extends TestCase
{
    /**
     * A prior notice-only handler must never receive an unregistered warning.
     *
     * This positive native contract remains red while the serializer fix is blocked.
     *
     * @return void
     */
    public function testPriorHandlerKeepsItsNativeSeverityMask(): void
    {
        $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode(serialize(array('object' => new StoredHandlerContractValue())))));
        $settings = new GlobalFieldSettings();
        $calls = array();
        $previous = null;
        $previous = set_error_handler(static function ($severity, $message, $file, $line) use (&$calls, &$previous) {
            $calls[] = $severity;
            if ($severity !== E_USER_NOTICE) {
                throw new \RuntimeException('Notice-only handler received unregistered severity ' . $severity);
            }
            return $previous === null ? false : $previous(...func_get_args());
        }, E_USER_NOTICE);

        try {
            $value = $settings->getSettings();
            $this->assertInstanceOf(StoredHandlerContractValue::class, $value['object']);
            $this->assertSame(array(), $calls);
        } finally {
            restore_error_handler();
        }
    }
}
