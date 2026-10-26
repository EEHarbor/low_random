<?php

namespace Tests;

use Tests\Support\BridgeHelperCalls;
use Tests\Support\BridgeFormatFactory;
use Tests\Support\BridgeRegistry;
use Tests\Support\BridgeTagFunctions;
use Tests\Support\BridgeVariableParser;

final class ArchiveBridgeHelpersTest extends TestCase
{
    /**
     * Build the shipped bridge with isolated helper and parser collaborators.
     *
     * @param string $version
     * @return object
     */
    private function bridge($version)
    {
        require_once dirname(__DIR__, 2) . '/Support/BridgeHelperFixtures.php';
        $helpers = new BridgeHelperCalls();
        $helpers->results = array('url_title' => 'slug-result', 'prep_url' => 'prepared-result', 'reduce_double_slashes' => 'reduced-result');
        $GLOBALS['low_random_services']['Test/BridgeHelpers'] = $helpers;
        $GLOBALS['low_random_services']['Format'] = new BridgeFormatFactory();
        $GLOBALS['low_random_services']['Variables/Parser'] = new BridgeVariableParser();
        $this->ee->functions = new BridgeTagFunctions();
        $class = 'Low\\Random\\FluxCapacitor\\Bridge\\' . $version;
        return new $class(array('module' => 'low_random', 'module_name' => 'Low Random'));
    }

    /**
     * Include every separately shipped implementation of these shared adapters.
     *
     * @return array
     */
    public static function versions()
    {
        return array(array('EE2'), array('EE3'), array('EE4'), array('EE5'));
    }

    /**
     * Normalize case and surrounding whitespace before delegating URL title creation.
     *
     * @dataProvider versions
     * @param string $version
     * @return void
     */
    public function testTitleNormalizationAndHelperResult($version)
    {
        $bridge = $this->bridge($version);
        $helpers = $GLOBALS['low_random_services']['Test/BridgeHelpers'];

        foreach (array('  HELLO World  ' => 'hello world', '' => '', '0' => '0') as $source => $normalized) {
            $this->assertSame('slug-result', $bridge->url_title((string) $source));

            if ($version === 'EE2' || $version === 'EE3') {
                $this->assertSame(array('url_title', $normalized), end($helpers->calls));
                continue;
            }

            $factory = $GLOBALS['low_random_services']['Format'];
            $this->assertSame(array('Text', $normalized), end($factory->calls));
            $this->assertSame(array('urlSlug', 'compile'), end($factory->formatters)->calls);
        }
    }

    /**
     * Non-ASCII UTF-8 must remain valid before the native formatter receives it.
     *
     * @dataProvider versions
     * @param string $version
     * @return void
     */
    public function testTitleUtf8Integrity($version)
    {
        $bridge = $this->bridge($version);
        $this->assertSame('slug-result', $bridge->url_title('ÄBC'));
        $legacy = $version === 'EE2' || $version === 'EE3';
        $calls = $legacy ? $GLOBALS['low_random_services']['Test/BridgeHelpers']->calls : $GLOBALS['low_random_services']['Format']->calls;
        $normalized = end($calls)[1];
        $this->assertTrue(mb_check_encoding($normalized, 'UTF-8'), 'Invalid normalized UTF-8 under LC_CTYPE=' . setlocale(LC_CTYPE, 0) . ': ' . bin2hex($normalized));
        $this->assertSame('Äbc', $normalized);
    }

    /**
     * Parser adapters preserve exact defaults, source text and structured outputs.
     *
     * @dataProvider versions
     * @param string $version
     * @return void
     */
    public function testTagParserBoundaries($version)
    {
        $bridge = $this->bridge($version);
        $legacy = $version === 'EE2' || $version === 'EE3';
        $parser = $legacy ? $this->ee->functions : $GLOBALS['low_random_services']['Variables/Parser'];
        $parser->parameters = array('limit' => 0, 'enabled' => false);
        $parser->variables = array('var_single' => array('title'), 'var_pair' => array('rows'));
        $defaults = array('limit' => 10, 'enabled' => true);
        $this->assertSame($parser->parameters, $bridge->assign_parameters('limit="0" enabled="no"', $defaults));
        $this->assertSame($parser->variables, $bridge->assign_variables('{rows}{title}{/rows}'));
        $this->assertSame(array(
            array($legacy ? 'parameters' : 'parseTagParameters', 'limit="0" enabled="no"', $defaults),
            array($legacy ? 'variables' : 'extractVariables', '{rows}{title}{/rows}'),
        ), $parser->calls);
    }

    /**
     * Native helper calls preserve their input and loader dependency.
     *
     * @dataProvider modernVersions
     * @param string $version
     * @return void
     */
    public function testModernUrlAndStringHelpers($version)
    {
        $bridge = $this->bridge($version);
        $this->assertSame('prepared-result', $bridge->prep_url('example.test/path?zero=0'));
        $this->assertSame('reduced-result', $bridge->reduce_double_slashes('https://example.test//path///'));
        $this->assertSame(array('string'), $this->ee->load->helpers);
        $expected = array(array('reduce_double_slashes', 'https://example.test//path///'));

        if ($version === 'EE3') {
            array_unshift($expected, array('prep_url', 'example.test/path?zero=0'));
        }

        if ($version !== 'EE3') {
            $factory = $GLOBALS['low_random_services']['Format'];
            $this->assertSame(array(array('Text', 'example.test/path?zero=0')), $factory->calls);
            $this->assertSame(array('url'), $factory->formatters[0]->calls);
        }

        $this->assertSame($expected, $GLOBALS['low_random_services']['Test/BridgeHelpers']->calls);
    }

    /**
     * Select modern implementations whose registry and URL APIs differ from EE2.
     *
     * @return array
     */
    public static function modernVersions()
    {
        return array(array('EE3'), array('EE4'), array('EE5'));
    }

    /**
     * Registry writes preserve loader rejection and removal semantics.
     *
     * @dataProvider modernVersions
     * @param string $version
     * @return void
     */
    public function testRegistryBoundaries($version)
    {
        $bridge = $this->bridge($version);
        $registry = new BridgeRegistry();
        $GLOBALS['low_random_ee'] = $registry;
        $value = (object) array('value' => 0);
        $this->assertNull($bridge->overwriteEEClass('custom', $value));
        $this->assertSame($value, $registry->loaded['custom']);

        foreach (array($value, null) as $existing) {
            $registry->loaded['custom'] = $existing;

            try {
                $bridge->overwriteEEClass('custom', 'replacement');
                $this->fail('EE must reject an already-loaded service.');
            } catch (\RuntimeException $exception) {
                $this->assertSame('Cannot overwrite custom on the loader.', $exception->getMessage());
            }

            $this->assertSame($existing, $registry->loaded['custom']);
        }

        $this->assertNull($bridge->removeEEClass('custom'));
        $this->assertArrayNotHasKey('custom', $registry->loaded);
        $this->assertSame(array('remove', 'custom'), end($registry->calls));
    }

    /**
     * Current URL inspection preserves full, selected, default and unknown-key returns.
     *
     * @dataProvider modernVersions
     * @param string $version
     * @return void
     */
    public function testCurrentUrlInformation($version)
    {
        $bridge = $this->bridge($version);
        $this->ee->uri = new class {
            public $path;

            /**
             * Return the configured native URI string without query manipulation.
             *
             * @return string
             */
            public function uri_string()
            {
                return $this->path;
            }
        };

        foreach (array('cp/addons/settings/low_random/license' => array(true, 'license'), 'api/addons/settings/low_random' => array(false, 'index')) as $path => $expected) {
            $this->ee->uri->path = $path;
            $information = array('full' => $path, 'cp' => $expected[0], 'segments' => explode('/', $path), 'module' => 'low_random', 'method' => $expected[1]);
            $this->assertSame($information, $bridge->getCurrentUrlInfo());
            $this->assertSame($information, $bridge->getCurrentUrlInfo('missing'));
            $this->assertSame($expected[1], $bridge->getCurrentUrlInfo('method'));
            $this->assertSame($expected[0], $bridge->getCurrentUrlInfo('cp'));
        }
    }
}
