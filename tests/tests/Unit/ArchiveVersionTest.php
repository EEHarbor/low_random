<?php

namespace Tests;

use Low\Random\FluxCapacitor\Conduit\StaticCache;
use Low\Random\FluxCapacitor\Conduit\Version;

final class ArchiveVersionTest extends TestCase
{
    private $previousGet;

    /**
     * Preserve request switches while exercising the actual licensing class.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->previousGet = $_GET;
        $_GET = array();
    }

    /**
     * Restore request state even when the shipped encryption path fails.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $_GET = $this->previousGet;
        parent::tearDown();
    }

    /**
     * A first installation creates the license schema and validates its new key.
     *
     * @return void
     */
    public function testConstructorCreatesAndVerifiesSchema()
    {
        $this->ee->db->data_cache['table_names'] = array('stale');
        $version = new Version();
        $this->assertArrayNotHasKey('table_names', $this->ee->db->data_cache);
        $this->assertSame(1, $this->ee->load->forgeLoads);
        $this->assertSame(array('add_field', 'add_key', 'add_key', 'create_table'), array_column($this->ee->dbforge->calls, 0));
        $this->assertSame(array('add_key', 'license_key', true), $this->ee->dbforge->calls[1]);
        $this->assertSame(array('add_key', array('site_id', 'addon'), false), $this->ee->dbforge->calls[2]);
        $fields = $this->ee->dbforge->calls[0][1];
        $this->assertSame(array('site_id', 'addon', 'license_key', 'ignore_site'), array_keys($fields));
        $this->assertSame(array('type' => 'varchar(50)', 'null' => false, 'default' => ''), $fields['license_key']);
        $this->assertFalse($version->verifyLicenseTable());
        $this->ee->db->calls = array();
        $this->assertFalse($version->verifyLicenseTable());
        $this->assertSame(array(), $this->ee->db->calls);
    }

    /**
     * Existing installations receive only the missing ignore-site column.
     *
     * @return void
     */
    public function testExistingSchemaAddsOnlyMissingColumn()
    {
        $this->ee->db->tables = array('eeharbor_licenses');
        $this->ee->db->primaryKeys['eeharbor_licenses'] = array(array('Column_name' => 'license_key', 'Non_unique' => '0'));
        $version = new Version();
        $this->assertFalse($version->verifyLicenseTable());
        $this->assertSame(array(array('add_column', 'eeharbor_licenses', array('ignore_site' => array('type' => 'int(1)', 'unsigned' => true, 'null' => false, 'default' => '0')))), $this->ee->dbforge->calls);
    }

    /**
     * Incorrect, duplicated or missing primary keys remain explicitly corrupt.
     *
     * @return void
     */
    public function testSchemaPrimaryKeyBoundaries()
    {
        foreach (array(
            array(),
            array(array('Column_name' => 'site_id', 'Non_unique' => '0')),
            array(array('Column_name' => 'license_key', 'Non_unique' => '1')),
            array(array('Column_name' => 'license_key', 'Non_unique' => '0'), array('Column_name' => 'addon', 'Non_unique' => '0')),
        ) as $keys) {
            StaticCache::clear();
            $this->ee->db->tables = array('eeharbor_licenses');
            $this->ee->db->fields = array('eeharbor_licenses.ignore_site');
            $this->ee->db->primaryKeys['eeharbor_licenses'] = $keys;
            $version = new Version();
            $this->assertTrue($version->verifyLicenseTable());
        }
    }

    /**
     * Add-on existence is scoped to the active site and cached for both outcomes.
     *
     * @return void
     */
    public function testKeyExistenceCachesPresentAndAbsentRows()
    {
        foreach (array(array(), array(array('site_id' => 1, 'addon' => 'Low\\Random', 'license_key' => 'local-key'))) as $rows) {
            StaticCache::clear();
            $version = new Version();
            $this->ee->db->results['eeharbor_licenses'] = $rows;
            $this->ee->db->calls = array();
            $this->assertSame((bool) $rows, $version->keyExistsForAddon());
            $this->assertSame(array(
                array('where', array('site_id', 1)), array('where', array('addon', 'Low\\Random')),
                array('count_all_results', array('eeharbor_licenses')),
            ), $this->ee->db->calls);
            $this->ee->db->calls = array();
            $this->assertSame((bool) $rows, $version->keyExistsForAddon());
            $this->assertSame(array(), $this->ee->db->calls);
        }
    }

    /**
     * Saving inserts a missing key and updates an existing key with site scope.
     *
     * @return void
     */
    public function testLicenseKeyInsertAndUpdate()
    {
        foreach (array(false, true) as $exists) {
            StaticCache::clear();
            $version = new Version();
            $this->ee->db->results['eeharbor_licenses'] = $exists ? array(array('license_key' => 'old')) : array();
            $this->ee->db->calls = array();
            $this->assertNull($version->saveLicenseKey(array('license_key' => 'new', 'ignore_site' => 0)));
            $write = end($this->ee->db->calls);
            $this->assertSame(array($exists ? 'update' : 'insert', array('eeharbor_licenses', array('license_key' => 'new', 'ignore_site' => 0, 'site_id' => 1, 'addon' => 'Low\\Random'))), $write);
            $this->assertSame($exists ? 6 : 4, count($this->ee->db->calls));

            if ($exists) {
                $this->assertSame(array(
                    array('where', array('site_id', 1)), array('where', array('addon', 'Low\\Random')), $write,
                ), array_slice($this->ee->db->calls, -3));
            }
        }
    }

    /**
     * Explicit record fields are preserved instead of replaced with defaults.
     *
     * @return void
     */
    public function testExplicitLicenseRecordFields()
    {
        $version = new Version();
        $data = array('site_id' => 1, 'addon' => 'Low\\Random', 'license_key' => 'explicit', 'ignore_site' => 1);
        $this->assertNull($version->saveLicenseKey($data));
        $this->assertSame(array('insert', array('eeharbor_licenses', $data)), end($this->ee->db->calls));
        $this->ee->db->calls = array();
        $this->assertNull($version->deleteLicenseKey());
        $this->assertSame(array(
            array('where', array('site_id', 1)), array('where', array('addon', 'Low\\Random')),
            array('delete', array('eeharbor_licenses')),
        ), $this->ee->db->calls);
    }

    /**
     * License getters preserve absent rows, safe characters and stored flags.
     *
     * @return void
     */
    public function testLicenseGetters()
    {
        $version = new Version();
        $this->assertSame('', $version->getLicenseKey());
        $this->assertFalse($version->getIgnoreSite());
        $this->ee->db->results['eeharbor_licenses'] = array(array('license_key' => 'LOCAL_<key>-$12', 'ignore_site' => '1'));
        $this->assertSame('LOCALkey-12', $version->getLicenseKey());
        $this->assertSame('1', $version->getIgnoreSite());
        $this->ee->db->results['eeharbor_licenses'][0]['ignore_site'] = 0;
        $this->assertSame(0, $version->getIgnoreSite());
    }

    /**
     * SQL NULL license keys should not cause deprecated regex subject coercion.
     *
     * @return void
     */
    public function testNullLicenseKeyDoesNotEmitDiagnostics()
    {
        $version = new Version();
        $this->ee->db->results['eeharbor_licenses'] = array(array('license_key' => null, 'ignore_site' => 0));
        $this->assertSame('', $version->getLicenseKey());
    }
}
