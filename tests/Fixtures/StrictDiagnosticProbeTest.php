<?php

namespace Tests;

final class StrictDiagnosticHydration
{
    /**
     * Emit an owned unexpected object-hydration diagnostic.
     *
     * @return void
     */
    public function __wakeup(): void
    {
        if (getenv('LOW_RANDOM_STRICT_PROBE') === 'nested') {
            unserialize('owned malformed nested callback input');
            return;
        }

        @trigger_error('owned hydration diagnostic', E_USER_DEPRECATED);
    }
}

final class StrictDiagnosticProbeTest extends TestCase
{
    /**
     * Exercise provider diagnostics through actual PHPUnit discovery.
     *
     * @return array
     */
    public static function cases(): array
    {
        if (getenv('LOW_RANDOM_STRICT_PROBE') === 'provider') {
            try {
                @trigger_error('owned provider diagnostic', E_USER_DEPRECATED);
            } catch (\Throwable $error) {
            }
        }

        return array(array(true));
    }

    /**
     * Keep deliberately failing controls outside the ordinary source suite.
     *
     * @dataProvider cases
     * @param bool $value Clean assertion input.
     * @return void
     */
    public function testRetainedDiagnostics($value): void
    {
        if (getenv('LOW_RANDOM_STRICT_PROBE') === 'main') {
            try {
                @trigger_error('owned main diagnostic', E_USER_DEPRECATED);
            } catch (\Throwable $error) {
            }
        }

        if (getenv('LOW_RANDOM_STRICT_PROBE') === 'native') {
            $this->native(array('-r', 'try { @trigger_error("owned native diagnostic", E_USER_DEPRECATED); } catch (Throwable $error) {}'));
        }

        if (in_array(getenv('LOW_RANDOM_STRICT_PROBE'), array('hydration', 'nested', 'malformed', 'ordinary-malformed'), true)) {
            $serialized = serialize(new StrictDiagnosticHydration());
            if (in_array(getenv('LOW_RANDOM_STRICT_PROBE'), array('malformed', 'ordinary-malformed'), true)) {
                $tail = getenv('LOW_RANDOM_STRICT_PROBE') === 'malformed' ? 'S:9:"bad";' : 's:9:"bad";';
                $serialized = 'a:2:{i:0;' . $serialized . 'i:1;' . $tail . '}';
            }
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode($serialized)));

            if (PHP_VERSION_ID < 80000) {
                set_error_handler(static function ($severity, $message, $file, $line, $context) {
                    \LowRandomDiagnostics::handle($severity, $message, $file, $line);
                });
            }

            try {
                (new \Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings())->getSettings();
            } catch (\Throwable $error) {
            } finally {
                if (PHP_VERSION_ID < 80000) {
                    restore_error_handler();
                }
            }
        }

        if (getenv('LOW_RANDOM_STRICT_PROBE') === 'native-timeout') {
            try {
                $this->native(array('-r', 'try { @trigger_error("owned timed-out native diagnostic", E_USER_DEPRECATED); } catch (Throwable $error) {} echo "owned native output"; usleep(2000000);'), array(), 0.25);
            } catch (\RuntimeException $error) {
                $this->assertSame('Native PHP probe exceeded its deadline.', $error->getMessage());
            }
        }

        if (getenv('LOW_RANDOM_STRICT_PROBE') === 'after-parser') {
            $this->ee->db->results['fieldtypes'] = array(array('settings' => base64_encode('malformed')));
            (new \Low\Random\FluxCapacitor\Conduit\GlobalFieldSettings())->getSettings();

            try {
                @trigger_error('owned restored-handler diagnostic', E_USER_DEPRECATED);
            } catch (\Throwable $error) {
            }
        }

        $this->assertTrue($value);
    }
}
