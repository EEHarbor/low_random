<?php

namespace Tests;

use Low\Random\FluxCapacitor\Base\Ft;
use Tests\Support\RequestInput;

final class ArchiveFieldtypeTest extends TestCase
{
    /**
     * Build a fieldtype using the actual archive constructor and native base shape.
     *
     * @return Ft
     */
    private function fieldtype()
    {
        require_once dirname(__DIR__, 2) . '/Support/FieldtypeFixture.php';
        $this->ee->input = new RequestInput();
        $this->ee->uri = new class {
            public $path = '';

            /**
             * Return the current publish URI used by EE's legacy URL helper.
             *
             * @return string
             */
            public function uri_string()
            {
                return $this->path;
            }
        };
        return new class extends Ft {
            /**
             * Set the protected content identifier as EE's initializer would.
             *
             * @param int|null $id
             * @return void
             */
            public function initializeContent($id)
            {
                $this->content_id = $id;
            }
        };
    }

    /**
     * Fieldtype metadata and supported content types preserve the shipped contract.
     *
     * @return void
     */
    public function testFieldtypeMetadataAndContentType()
    {
        $fieldtype = $this->fieldtype();
        $this->assertSame('4.1.1', $fieldtype->version);
        $this->assertSame(array('name' => 'Low Random', 'version' => '4.1.1'), $fieldtype->info);
        $this->assertSame('low_random', $fieldtype->GlobalFieldSettings->fieldtype_name);
        $this->assertTrue($fieldtype->update('4.0.0'));
        $this->assertTrue($fieldtype->accepts_content_type('channel'));
        $this->assertFalse($fieldtype->accepts_content_type('grid'));
        $this->assertFalse($fieldtype->accepts_content_type('0'));
        $this->assertFalse($fieldtype->accepts_content_type(null));
        $this->assertFalse($fieldtype->accepts_content_type(array()));
    }

    /**
     * Explicit field settings win over request input without querying an entry.
     *
     * @return void
     */
    public function testChannelFromFieldSettingsAndRequest()
    {
        $fieldtype = $this->fieldtype();
        $fieldtype->settings = array('field_channel_id' => 7);
        $this->ee->input->post = array('channel_id' => '3');
        $this->assertSame(7, $fieldtype->getChannelId());
        $this->assertSame(array(), $GLOBALS['low_random_services']['Model']->calls);
        $fieldtype->settings = array();
        $this->assertSame('3', $fieldtype->getChannelId());
        $this->ee->input->post = array('channel_id' => null);
        $this->ee->input->get = array('channel_id' => '4');
        $this->assertSame('4', $fieldtype->getChannelId());
    }

    /**
     * Saved content resolves its channel through the real ChannelEntry query shape.
     *
     * @return void
     */
    public function testChannelFromSavedEntry()
    {
        $fieldtype = $this->fieldtype();
        $fieldtype->initializeContent(99);
        $query = $GLOBALS['low_random_services']['Model'];
        $query->rows = array((object) array('channel_id' => 8));
        $this->assertSame(8, $fieldtype->getChannelId());
        $this->assertSame(array(array('get', array('ChannelEntry')), array('filter', array('entry_id', 99)), array('first', array())), $query->calls);
    }

    /**
     * Unsaved entries can infer a channel from create routes and otherwise return false.
     *
     * @return void
     */
    public function testChannelFromCreateUriAndMissingContext()
    {
        $fieldtype = $this->fieldtype();
        $this->ee->uri->path = 'cp/publish/create/9';
        $this->assertSame(9, $fieldtype->getChannelId());
        $this->ee->uri->path = 'cp/publish/edit/entry/99';
        $this->assertFalse($fieldtype->getChannelId());
        $this->assertSame(array(), $GLOBALS['low_random_services']['Model']->calls);
    }

    /**
     * Settings adapters preserve row order, width, labels and explicit group names.
     *
     * @return void
     */
    public function testSettingsRowsAndCompatibilityAdapters()
    {
        $fieldtype = $this->fieldtype();
        $this->assertNull($fieldtype->displaySettingsRow('First', '<input>', 'Description', false));
        $this->assertNull($fieldtype->_display_settings_add_row('Second', '<select>', '', true));
        $rows = array(
            array('title' => 'First', 'desc' => 'Description', 'wide' => false, 'fields' => array(array('type' => 'html', 'content' => '<input>'))),
            array('title' => 'Second', 'desc' => '', 'wide' => true, 'fields' => array(array('type' => 'html', 'content' => '<select>'))),
        );
        $this->assertSame($rows, $fieldtype->rows);
        $this->assertSame(array('field_options_low_random' => array('label' => 'field_options', 'group' => 'low_random', 'settings' => $rows)), $fieldtype->displaySettingsReturn());
        $this->assertSame(array('custom' => array('label' => 'Custom label', 'group' => 'group', 'settings' => $rows)), $fieldtype->_package_display_settings('custom', 'group', 'Custom label'));
    }

    /**
     * Missing model rows should permit the same create-route fallback as unsaved content.
     *
     * @return void
     */
    public function testMissingEntryDoesNotDereferenceNull()
    {
        $fieldtype = $this->fieldtype();
        $fieldtype->initializeContent(99);
        $this->ee->uri->path = 'cp/publish/create/9';
        $this->assertSame(9, $fieldtype->getChannelId());
    }

    /**
     * A boolean is not a content-type name even though loose comparisons allow it.
     *
     * @return void
     */
    public function testBooleanContentTypeIsRejected()
    {
        $this->assertFalse($this->fieldtype()->accepts_content_type(true));
    }
}
