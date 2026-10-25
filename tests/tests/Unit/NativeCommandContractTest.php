<?php

namespace Tests;

final class NativeCommandContractTest extends TestCase
{
    /**
     * Preserve native CLI program meaning while installing the strict child guard.
     *
     * @dataProvider healthyPrograms
     * @param array $arguments Native PHP command arguments.
     * @param string $expected Original native stdout bytes.
     * @return void
     */
    public function testHealthyInlineProgram(array $arguments, $expected): void
    {
        $this->assertSame(array(0, $expected, ''), $this->native($arguments));
    }

    /**
     * Cover the executable option, declaration and namespace boundaries repaired here.
     *
     * @return array
     */
    public function healthyPrograms(): array
    {
        return array(
            array(array('-rdeclare(strict_types=1); echo "attached";'), 'attached'),
            array(array('-d', 'error_reporting=0', '-d', 'auto_prepend_file=', '-r', 'echo error_reporting();'), '-1'),
            array(array('--docroot', '/private/tmp', '--run', 'echo "docroot";'), 'docroot'),
            array(array('--run=namespace InlineNative; echo "namespace";'), 'namespace'),
            array(array('-r', 'declare(ticks=1) eval(\'echo "body";\');'), 'body'),
            array(array('-r', 'declare(ticks=1){declare(ticks=2);} namespace InlineNative; echo "nested";'), 'nested'),
            array(array('-r', "/* header */\ndeclare(strict_types=1);\nnamespace InlineNative;\necho __LINE__;"), '4'),
            array(array('-B', '$GLOBALS["ticks"] = 0; register_tick_function(static function () { $GLOBALS["ticks"]++; });', '-E', 'declare(ticks=1); namespace TickNative; $x = 1; $x++; echo $GLOBALS["ticks"];'), '3'),
            array(array('-E', 'echo "end";'), 'end'),
        );
    }

    /**
     * Keep literal application options after the selected script unchanged.
     *
     * @return void
     */
    public function testScriptArgumentsAreNotRewritten(): void
    {
        $script = $this->temporaryDirectory . '/arguments.php';
        file_put_contents($script, '<?php echo json_encode(array_slice($argv, 1));');
        $this->assertSame(array(0, '["--","-r","literal","--run=literal"]', ''),
            $this->native(array($script, '--', '-r', 'literal', '--run=literal')));
    }
}
