<?php

namespace Tests;

final class NativeProcessTest extends TestCase
{
    /**
     * Start native PHP using the same explicit Herd runtime and strict settings.
     *
     * @param array $arguments
     * @param array $environmentOverrides
     * @return array
     */
    private function process(array $arguments, array $environmentOverrides = array())
    {
        return $this->native($arguments, $environmentOverrides);
    }

    /**
     * Deny direct plugin access before the archive can be loaded.
     *
     * @return void
     */
    public function testDirectAccessGuard()
    {
        $result = $this->process(array(dirname(__DIR__, 2) . '/Support/native.php', 'direct-access', $this->temporaryDirectory));
        $this->assertSame(array(0, 'No direct script access allowed', ''), $result);
    }

}
