<?php

namespace Tests;

final class NativeCaptureOwnershipTest extends TestCase
{
    /**
     * Keep a healthy native operation joined when caller environment changes.
     *
     * @dataProvider captureEnvironmentChanges
     * @param bool $redirect Whether to select another caller-owned directory.
     * @return void
     */
    public function testCaptureSelectionSurvivesEnvironmentChanges(bool $redirect): void
    {
        $authority = \LOW_RANDOM_NATIVE_CAPTURE_AUTHORITY;
        $selected = getenv('LOW_RANDOM_STRICT_CAPTURES');
        $before = $authority === null ? array() : glob($authority['path'] . '/*.json');
        $alternate = $this->temporaryDirectory . '/alternate-captures';
        mkdir($alternate);
        putenv($redirect ? 'LOW_RANDOM_STRICT_CAPTURES=' . $alternate : 'LOW_RANDOM_STRICT_CAPTURES');

        try {
            $result = $this->native(array('-r', 'echo "OWNED_CAPTURE";'),
                array('LOW_RANDOM_STRICT_CAPTURES' => $alternate));
        } finally {
            putenv($selected === false ? 'LOW_RANDOM_STRICT_CAPTURES' : 'LOW_RANDOM_STRICT_CAPTURES=' . $selected);
        }

        self::assertSame(array(0, 'OWNED_CAPTURE', ''), $result);
        self::assertSame(array(), glob($alternate . '/*'));
        if ($authority === null) {
            return;
        }

        $created = array_values(array_diff(glob($authority['path'] . '/*.json'), $before));
        self::assertCount(1, $created);
        $capture = json_decode(file_get_contents($created[0]), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('complete', $capture['state']);
        self::assertSame(getmypid(), $capture['owner_pid']);
        self::assertSame(0, $capture['exit']);
        self::assertSame('OWNED_CAPTURE', file_get_contents($capture['stdout']));
    }

    /**
     * Supply both supported caller environment mutations.
     *
     * @return array
     */
    public function captureEnvironmentChanges(): array
    {
        return array('unset' => array(false), 'redirect' => array(true));
    }
}
