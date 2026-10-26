<?php

namespace Tests;

use Low\Random\FluxCapacitor\Bridge\EE2;
use Tests\Support\LegacySession;
use Tests\Support\RequestInput;

final class ArchiveLegacyBridgeStateTest extends TestCase
{
    /**
     * Observe the original bridge in a disposable historical constant context.
     *
     * @param string $mode
     * @param string $version
     * @param array $options
     * @return array
     */
    private function observe($mode, $version, array $options)
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-legacy-bridge.php', $mode, $this->temporaryDirectory, $version, json_encode($options, JSON_THROW_ON_ERROR)));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        return json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Preserve explicit sessions/directives and append only missing values.
     *
     * @dataProvider sessionUrls
     * @param array $options
     * @param string $expected
     * @return void
     */
    public function testHistoricalSessionUrls(array $options, $expected)
    {
        $data = $this->observe('url', '2.5.5', $options);
        $this->assertSame($expected, $data['base']);
        $module = str_replace('&amp;method=license&amp;zero=0', '', $expected);
        $this->assertSame($module . '&amp;method=listing&amp;page=0', $data['module']);
    }

    /**
     * Provide cookie, session-id, fingerprint and existing-query controls.
     *
     * @return array
     */
    public static function sessionUrls()
    {
        $base = 'https://example.test/cp?C=home';
        $route = '&amp;C=addons_modules&amp;M=show_module_cp&amp;module=low_random';
        $options = array('base' => $base, 'method' => 'license', 'extra' => '&amp;zero=0', 'variables' => array('page' => 0));
        $result = array();

        foreach (array('cookies' => array('c', array(), ''), 'session' => array('s', array('session_id' => 'sid'), '&amp;S=sid'), 'fingerprint' => array('cs', array('fingerprint' => 'finger'), '&amp;S=finger'), 'zero session' => array('s', array('session_id' => '0'), '')) as $name => $vector) {
            $result[$name] = array(array_merge($options, array('session_type' => $vector[0], 'userdata' => $vector[1])), $base . $route . '&amp;method=license&amp;zero=0' . $vector[2] . '&amp;D=cp');
        }

        $result['existing session and directive'] = array(array_merge($options, array('base' => $base . '&amp;S=existing&amp;D=cp', 'session_type' => 's', 'userdata' => array('session_id' => 'sid'))), $base . '&amp;S=existing&amp;D=cp' . $route . '&amp;method=license&amp;zero=0');
        return $result;
    }

    /**
     * Map current CP routes to legacy controllers while preserving query values.
     *
     * @dataProvider legacyRoutes
     * @param string $path
     * @param string $mode
     * @param array $variables
     * @param string $expected
     * @return void
     */
    public function testLegacyControlPanelRoutes($path, $mode, array $variables, $expected)
    {
        $data = $this->observe('route', '2.5.5', array('path' => $path, 'mode' => $mode, 'variables' => $variables));
        $this->assertSame('https://example.test/cp?C=home&amp;D=cp&amp;C=' . $expected, $data['url']);
    }

    /**
     * Exercise remapped and passthrough controller/method combinations.
     *
     * @return array
     */
    public static function legacyRoutes()
    {
        return array(
            array('listing', 'ignored', array('filter_by_channel' => 4, 'page' => 0), 'content_edit&amp;page=0&amp;channel_id=4'),
            array('listing', '', array(), 'content_edit'),
            array('publish', 'create', array('channel_id' => 4), 'content_publish&amp;M=entry_form&amp;channel_id=4'),
            array('publish', 'edit', array('entry_id' => 7), 'content_publish&amp;M=entry_form&amp;entry_id=7'),
            array('publish', 'preview', array(), 'content_publish&amp;M=preview'),
            array('members', 'groups', array(), 'members&amp;M=member_group_manager'),
            array('members', 'list', array(), 'members&amp;M=list'),
            array('channels', 'create', array(), 'admin_content&amp;M=channel_add'),
            array('channels', 'edit', array(), 'admin_content&amp;M=edit'),
            array('addons', '', array(), 'addons_modules'),
            array('custom', '', array('zero' => 0), 'custom&amp;zero=0'),
        );
    }

    /**
     * Write dynamic views only inside the owned temporary add-on directory.
     *
     * @dataProvider viewModes
     * @param bool $directory
     * @param string|false $string
     * @param bool $return
     * @return void
     */
    public function testTemporaryDynamicViews($directory, $string, $return)
    {
        $data = $this->observe('view', '2.5.5', array('directory' => $directory, 'string' => $string, 'return' => $return));
        $dynamic = $directory && $string;
        $expected = array($dynamic ? 'dynamic' : 'original', array('ee_ver' => '2', 'zero' => 0), $return);
        $this->assertSame($expected, $data['result']);
        $this->assertSame(array($expected), $data['calls']);
        $this->assertSame($dynamic ? $string : null, $data['contents']);
    }

    /**
     * Include writable, absent-directory and false/empty-string boundaries.
     *
     * @return array
     */
    public static function viewModes()
    {
        return array(array(true, '<p>zero: 0</p>', true), array(false, '<p>fallback</p>', false), array(true, false, true), array(true, '', false));
    }

    /**
     * Request-cache deletes and clears preserve zero and namespace ownership.
     *
     * @dataProvider requestCacheModes
     * @param string $version
     * @param bool $persistent
     * @param string $delete
     * @param string|false $key
     * @return void
     */
    public function testRequestCacheCompatibility($version, $persistent, $delete, $key)
    {
        $data = $this->observe('cache', $version, array('persistent' => $persistent, 'delete' => $delete, 'key' => $key));
        $this->assertSame(0, $data['set']);
        $this->assertSame(0, $data['get']);
        $this->assertSame(array('persistent' => array(), 'request' => array('other_addon' => array('sentinel' => 0), 'low_random' => array('key' => 0))), $data['before']);
        $this->assertFalse($data['after']);
        $this->assertSame(array('other_addon' => array('sentinel' => 0), 'low_random' => array()), $data['request']);
        $this->assertSame(array(), $data['calls']);
        $this->assertFalse($data['unknown']);
    }

    /**
     * Persistent mode falls back to session storage before EE 2.8.
     *
     * @return array
     */
    public static function requestCacheModes()
    {
        return array(array('2.7.3', true, 'delete', 'key'), array('2.8.0', false, 'delete', 'key'), array('2.8.0', false, 'clear', false));
    }

    /**
     * Persistent deletion must target the tier used by the matching get/set.
     *
     * @return void
     */
    public function testPersistentCacheDeleteUsesItsOwnTier()
    {
        $data = $this->observe('cache', '2.8.0', array('persistent' => true, 'delete' => 'delete', 'key' => 'key'));
        $this->assertTrue($data['set']);
        $this->assertSame(0, $data['get']);
        $this->assertSame(array('persistent' => array('/low_random/key' => 0), 'request' => array('other_addon' => array('sentinel' => 0), 'low_random' => array())), $data['before']);
        $this->assertFalse($data['after']);
    }

    /**
     * Legacy inserts and updates own the fresh active-site mutation predicate.
     *
     * @dataProvider existingSettings
     * @param bool $exists
     * @return void
     */
    public function testLegacySettingsWrites($exists)
    {
        $bridge = new EE2(array('module' => 'low_random', 'module_name' => 'Low Random'));
        $this->ee->db->tables = array('low_random_settings');
        $this->ee->db->results['low_random_settings'] = $exists ? array(array('site_id' => 1)) : array();
        $this->ee->db->data_cache = array('table_names' => array('caller'), 'sentinel' => 0);
        $bridge->setConfig('custom', 0);
        $journal = $this->ee->db->journal->calls;
        $mutation = end($journal);
        $this->assertSame(array($exists ? 'update' : 'insert', array('low_random_settings', array('site_id' => 1, 'custom' => 0))), $mutation['call']);
        $expected = array(array('table_exists', array('low_random_settings')), array('where', array('site_id', 1)), array('count_all_results', array('low_random_settings')));

        if ($exists) {
            $expected[] = array('where', array('site_id', 1));
        }

        $expected[] = $mutation['call'];
        $writer = array_slice($journal, -count($expected));
        $calls = array_map(static function ($entry) { return $entry['call']; }, $writer);
        $this->assertSame($expected, $calls);
        $this->assertSame(array_fill(0, count($expected), $mutation['builder']), array_column($writer, 'builder'));
        $this->assertNotSame(spl_object_id($this->ee->db), $mutation['builder']);
        $this->assertCount(1, array_filter($journal, static function ($entry) { return in_array($entry['call'][0], array('insert', 'update', 'delete'), true); }));
        $this->assertSame(array(), $this->ee->db->calls);
        $this->assertSame(array('table_names' => array('caller'), 'sentinel' => 0), $this->ee->db->data_cache);
        $this->assertSame(0, $bridge->getConfig('custom'));
    }

    /**
     * Select both persistence paths without conflating reads and writes.
     *
     * @return array
     */
    public static function existingSettings()
    {
        return array(array(false), array(true));
    }

    /**
     * Structure's row-per-variable representation merges with field settings.
     *
     * @return void
     */
    public function testLegacyRowPerVariableSettings()
    {
        $bridge = new EE2(array('module' => 'structure', 'module_name' => 'Structure'));
        $this->ee->db->tables = array('structure_settings');
        $this->ee->db->results = array(
            'structure_settings' => array(array('var' => 'custom', 'var_value' => 0), array('var' => 'priority', 'var_value' => 'database')),
            'fieldtypes' => array(array('settings' => base64_encode(serialize(array('priority' => 'field', 'field_only' => false))))),
        );
        $settings = $bridge->getSettings(true);
        $this->assertSame(0, $settings['custom']);
        $this->assertSame('database', $settings['priority']);
        $this->assertFalse($settings['field_only']);
        $this->assertSame('Low Random', $settings['name']);
    }

    /**
     * Flash and class assignment retain legacy scalar and structured values.
     *
     * @return void
     */
    public function testLegacySessionAndClassDelegation()
    {
        require_once dirname(__DIR__, 2) . '/Support/LegacyBridgeFixtures.php';
        $bridge = new EE2(array('module' => 'low_random', 'module_name' => 'Low Random'));
        $this->ee->session = new LegacySession();
        $bridge->flashData('message_error', 'Title', '0', array('unused'));
        $this->assertSame(array(array('message_error', 'Title 0')), $this->ee->session->flashes);
        $value = (object) array('value' => 0);
        $bridge->overwriteEEClass('custom', $value);
        $this->assertSame($value, $this->ee->custom);
        $bridge->removeEEClass('custom');
        $this->assertSame('', $this->ee->custom);
    }

    /**
     * Legacy offsets take precedence over one-based page values.
     *
     * @dataProvider currentPageInputs
     * @param array $input
     * @param int $expected
     * @return void
     */
    public function testLegacyCurrentPage(array $input, $expected)
    {
        $bridge = new EE2(array('module' => 'low_random', 'module_name' => 'Low Random'));
        $this->ee->input = new RequestInput();
        $this->ee->input->get = $input;
        $this->assertEquals($expected, $bridge->getCurrentPage(array('per_page' => 10)));
        $this->assertContains(array('per_page', 1), $this->ee->input->calls);
    }

    /**
     * Include absent, null, zero and numeric-string request representations.
     *
     * @return array
     */
    public static function currentPageInputs()
    {
        return array(array(array(), 1), array(array('per_page' => '20', 'page' => '9'), 3), array(array('per_page' => '0', 'page' => '3'), 3), array(array('per_page' => null, 'page' => '2'), 2), array(array('page' => 0), 1));
    }

    /**
     * Legacy pagination passes remapped page options to the loaded library.
     *
     * @return void
     */
    public function testLegacyPaginationDelegation()
    {
        $bridge = new EE2(array('module' => 'low_random', 'module_name' => 'Low Random'));
        $this->ee->pagination = new class {
            public $options;

            /**
             * Record the complete options supplied to the native paginator.
             *
             * @param array $options
             * @return void
             */
            public function initialize(array $options)
            {
                $this->options = $options;
            }

            /**
             * Return a distinct native pagination result for delegation checks.
             *
             * @return string
             */
            public function create_links()
            {
                return '<legacy-pagination>';
            }
        };

        foreach (array(array('per_page' => 10), array('per_page' => 10, 'current_page' => 3, 'cur_page' => 99)) as $options) {
            $this->assertSame('<legacy-pagination>', $bridge->pagination($options));
            $expected = $options;

            if (isset($expected['current_page'])) {
                $expected['cur_page'] = $expected['current_page'];
            }

            $this->assertSame($expected, $this->ee->pagination->options);
        }

        $this->assertSame(array('pagination', 'pagination'), $this->ee->load->libraries);
    }
}
