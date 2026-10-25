<?php

namespace Tests;

final class NativePreludeContractTest extends TestCase
{
    /**
     * Cover native prelude spellings without selecting a different startup API.
     *
     * @return array
     */
    public function preludeForms(): array
    {
        return array(array('separate'), array('attached'), array('long'), array('equals'), array('include-path'));
    }

    /**
     * Retain original file-global variables and literal main-program arguments.
     *
     * @dataProvider preludeForms
     * @param string $shape Original native INI option spelling.
     * @return void
     */
    public function testCallerPreludeRetainsItsScope($shape): void
    {
        $prelude = $this->temporaryDirectory . '/prelude setup.php';
        $main = $this->temporaryDirectory . '/main.php';
        file_put_contents($prelude, '<?php $nativePrelude = array("kept", __FILE__, getcwd());');
        file_put_contents($main, '<?php echo json_encode(array($nativePrelude, __FILE__, array_slice($argv, 1)));');
        $options = array('-d', 'auto_prepend_file=' . $prelude);
        if ($shape === 'attached') {
            $options = array('-dauto_prepend_file=' . $prelude);
        }
        if ($shape === 'long') {
            $options = array('--define', 'auto_prepend_file="' . $prelude . '"');
        }
        if ($shape === 'equals') {
            $options = array('--define=auto_prepend_file="' . $prelude . '"');
        }
        if ($shape === 'include-path') {
            $options = array('-d', 'include_path=' . $this->temporaryDirectory, '-d', 'auto_prepend_file=prelude setup.php');
        }
        $result = $this->native(array_merge($options, array($main, '-d', 'auto_prepend_file=literal')));
        $this->assertSame(array(0, json_encode(array(array('kept', realpath($prelude), dirname(__DIR__, 3)), realpath($main),
            array('-d', 'auto_prepend_file=literal'))), ''), $result);
    }

    /**
     * Select the effective default/scanned configuration through the native engine.
     *
     * @return void
     */
    public function testConfigurationPreludeIsSelectedNatively(): void
    {
        $ini = $this->temporaryDirectory . '/native.ini';
        $prelude = $this->temporaryDirectory . '/configured.php';
        $main = $this->temporaryDirectory . '/main.php';
        file_put_contents($ini, 'auto_prepend_file="' . $prelude . '"' . "\nprecision=17\n");
        file_put_contents($prelude, '<?php $configuredPrelude = true;');
        file_put_contents($main, '<?php echo json_encode(array(get_included_files(), ini_get("precision"), __FILE__));');
        $selection = $this->native(array('-c', $ini, '-r', 'echo json_encode(array(ini_get("auto_prepend_file"), ini_get("precision"), php_ini_loaded_file()));'));
        $this->assertSame(0, $selection[0]);
        $this->assertSame('', $selection[2]);
        $native = json_decode($selection[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(realpath($ini), $native[2]);
        $result = $this->native(array('-c', $ini, $main));
        $this->assertSame(0, $result[0]);
        $this->assertSame('', $result[2]);
        $value = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
        if ($native[0] !== '') {
            $this->assertContains(realpath($native[0]), $value[0]);
        }
        $this->assertSame($native[1], $value[1]);
        $this->assertSame(realpath($main), $value[2]);
    }

    /**
     * Do not turn an absent INI setting into a same-named environment fallback.
     *
     * @return void
     */
    public function testAbsentPreludeIgnoresEnvironmentName(): void
    {
        $main = $this->temporaryDirectory . '/empty-prelude.php';
        file_put_contents($main, '<?php echo "native-empty";');
        $result = $this->native(array('-n', $main), array('auto_prepend_file' => '/missing-environment-prelude.php'));
        $this->assertSame(array(0, 'native-empty', ''), $result);
    }

    /**
     * Preserve configured main output without encoding the owned discovery protocol.
     *
     * @return void
     */
    public function testConfiguredMainOutputRemainsNative(): void
    {
        $main = $this->temporaryDirectory . '/encoded-output.php';
        file_put_contents($main, '<?php echo "native-file";');
        $result = $this->native(array('-d', 'output_handler=mb_output_handler',
            '-d', 'output_encoding=UTF-16', '-d', 'internal_encoding=UTF-8', $main));
        $this->assertSame(array(0, mb_convert_encoding('native-file', 'UTF-16', 'UTF-8'), ''), $result);
    }


    /**
     * Execute a main file twice when native PHP also selects it as its prelude.
     *
     * @return void
     */
    public function testMainCanBeItsOwnPrelude(): void
    {
        $main = $this->temporaryDirectory . '/same-main.php';
        file_put_contents($main, '<?php $counter = ($counter ?? 0) + 1; echo $counter;');
        $this->assertSame(array(0, '12', ''), $this->native(array('-d', 'auto_prepend_file=' . $main, $main)));
    }

    /**
     * Reuse the owned guard through a native file alias without duplicate initialization.
     *
     * @return void
     */
    public function testOwnedGuardCanBeTheCallerPrelude(): void
    {
        $main = $this->temporaryDirectory . '/guard-main.php';
        $alias = $this->temporaryDirectory . '/guard-alias.php';
        file_put_contents($main, '<?php echo "owned-guard";');
        symlink(dirname(__DIR__, 2) . '/strict-diagnostics.php', $alias);
        $this->assertSame(array(0, 'owned-guard', ''), $this->native(array('-d', 'auto_prepend_file=' . $alias, $main)));
    }

}
