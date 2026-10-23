<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\StaticCache;

final class BridgeSettingsTest extends TestCase
{
    /**
     * Exercise the separately shipped implementations without replacing them.
     *
     * @return array
     */
    private function bridges()
    {
        $bridges = array();

        foreach (array('EE2', 'EE3', 'EE4', 'EE5') as $version) {
            $class = 'Low\\Random\\FluxCapacitor\\Bridge\\' . $version;
            $bridges[] = new $class(array('module' => 'low_random', 'module_name' => 'Low Random'));
        }

        return $bridges;
    }

    /**
     * Merge fieldtype, module and manifest settings in increasing priority.
     *
     * @return void
     */
    public function testSettingsMergeAndCacheAcrossEveryBridge()
    {
        foreach ($this->bridges() as $bridge) {
            StaticCache::clear();
            $this->ee->db->tables = array('low_random_settings');
            $this->ee->db->data_cache = array('table_names' => array('stale'));
            $this->ee->db->results = array(
                'fieldtypes' => array(array('settings' => base64_encode(serialize(array('origin' => 'field', 'field_only' => 0, 'name' => 'incorrect'))))),
                'low_random_settings' => array(array('site_id' => 1, 'origin' => 'database')),
            );
            $settings = $bridge->getSettings(true);
            $this->assertSame('database', $settings['origin']);
            $this->assertSame(0, $settings['field_only']);
            $this->assertSame('Low Random', $settings['name']);
            $this->assertSame((array) $bridge->getSettings(), $settings);
            $this->assertSame('4.1.1', $bridge->getConfig('version'));
            $this->ee->db->results = array();
            $this->assertSame($settings, $bridge->getSettings(true));
        }
    }

    /**
     * Empty fieldtype rows and serialized false use safe empty settings arrays.
     *
     * @return void
     */
    public function testSettingsWithoutDatabaseValues()
    {
        foreach ($this->bridges() as $bridge) {
            foreach (array(array(), array(array('settings' => base64_encode(serialize(false))))) as $fieldRows) {
                StaticCache::clear();
                $this->ee->db->tables = array();
                $this->ee->db->results = array('fieldtypes' => $fieldRows);
                $this->assertSame('4.1.1', $bridge->getConfig('version'));
                $bridge->setConfig('custom', 'value');
                $this->assertSame('value', $bridge->getConfig('custom'));
            }
        }
    }

    /**
     * Current bridges insert or update only the active site's setting row.
     *
     * @return void
     */
    public function testSetConfigInsertsThenUpdates()
    {
        foreach (array_slice($this->bridges(), 1) as $bridge) {
            foreach (array(false, true) as $exists) {
                StaticCache::clear();
                $this->ee->db->tables = array('low_random_settings');
                $this->ee->db->results = array('low_random_settings' => $exists ? array(array('site_id' => 1)) : array());
                $this->ee->db->calls = array();
                $this->ee->db->where('caller_member_id', 99);
                $pending = $this->ee->db->calls;
                $cache = array('table_names' => array('caller_table'), 'caller' => 'owned');
                $this->ee->db->data_cache = $cache;
                $before = count($this->ee->db->queries);
                $bridge->setConfig('custom', 0);
                $queries = array_slice($this->ee->db->queries, $before);
                $this->assertCount(2, $queries);
                $this->assertNotSame($this->ee->db, $queries[0]);
                $this->assertNotSame($queries[0], $queries[1]);
                $writer = $queries[1];
                $expected = array($exists ? 'update' : 'insert', array('low_random_settings', array('site_id' => 1, 'custom' => 0)));
                $trace = array(
                    array('table_exists', array('low_random_settings')),
                    array('where', array('site_id', 1)), array('count_all_results', array('low_random_settings')),
                );

                if ($exists) {
                    $trace[] = array('where', array('site_id', 1));
                }

                $trace[] = $expected;
                $this->assertSame($trace, $writer->calls);
                $this->assertSame(array(), $writer->data_cache);
                $this->assertSame($pending, $this->ee->db->calls);
                $this->assertSame($cache, $this->ee->db->data_cache);
                $this->assertSame(0, $bridge->getConfig('custom'));
            }
        }
    }

    /**
     * Offset calculations retain first-page and later-page boundaries.
     *
     * @return void
     */
    public function testPaginationOffsets()
    {
        foreach ($this->bridges() as $bridge) {
            $this->assertSame(0, $bridge->getStartNum(array('current_page' => 1, 'per_page' => 25)));
            $this->assertSame(50, $bridge->getStartNum(array('current_page' => 3, 'per_page' => 25)));
        }
    }
}
