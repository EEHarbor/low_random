<?php

namespace Tests;

use Low\Random\FluxCapacitor\Base\Mcp;
use Tests\Support\AlertFactory;
use Tests\Support\PagerModel;
use Tests\Support\Pagination;
use Tests\Support\RedirectFunctions;
use Tests\Support\RedirectSignal;
use Tests\Support\RequestInput;
use Tests\Support\UrlFactory;
use Tests\Support\ViewService;

final class ArchiveControlPanelControllerTest extends TestCase
{
    /**
     * Wire explicit EE service boundaries and preserve the shipped constructor.
     *
     * @return Mcp
     */
    private function controller()
    {
        $GLOBALS['low_random_services']['CP/URL'] = new UrlFactory();
        $GLOBALS['low_random_services']['CP/Alert'] = new AlertFactory();
        $GLOBALS['low_random_services']['View'] = new ViewService();
        $this->ee->input = new RequestInput();
        $this->ee->functions = new RedirectFunctions();
        $this->ee->view = (object) array('cp_page_title' => 'License');
        $this->ee->db->tables = array('eeharbor_licenses');
        $this->ee->db->fields = array('eeharbor_licenses.ignore_site');
        $this->ee->db->primaryKeys['eeharbor_licenses'] = array(array('Column_name' => 'license_key', 'Non_unique' => '0'));
        return new Mcp();
    }

    /**
     * Capture native redirect termination while leaving every source diagnostic red.
     *
     * @param callable $action
     * @return RedirectSignal
     */
    private function redirected(callable $action)
    {
        try {
            $action();
        } catch (RedirectSignal $signal) {
            $this->assertSame('addons/settings/low_random/license', $signal->url->path);
            return $signal;
        }

        $this->fail('The controller did not terminate with an EE redirect.');
    }

    /**
     * Require new site and add-on predicates owned by this actual mutation.
     *
     * @param string $method
     * @return void
     */
    private function assertScopedMutation($method)
    {
        $mutations = array_filter($this->ee->db->calls, static function ($call) use ($method) {
            return $call[0] === $method && $call[1][0] === 'eeharbor_licenses';
        });
        $this->assertCount(1, $mutations);
        $index = key($mutations);
        $this->assertSame(array(
            array('where', array('site_id', 1)), array('where', array('addon', 'Low\\Random')),
        ), array_slice($this->ee->db->calls, $index - 2, 2));
    }

    /**
     * Controller metadata and the default route use actual archive behavior.
     *
     * @return void
     */
    public function testConstructorAndIndexRoute()
    {
        $controller = $this->controller();
        $this->assertSame('4.1.1', $controller->version);
        $this->assertNull($controller->nav);
        $this->redirected(static function () use ($controller) { $controller->index(); });
        $this->assertCount(1, $this->ee->functions->redirects);
    }

    /**
     * License display handles absent, enabled and ignored database records.
     *
     * @return void
     */
    public function testLicenseViewStates()
    {
        $controller = $this->controller();
        $view = $GLOBALS['low_random_services']['View'];

        foreach (array(
            array(array(), '', false),
            array(array(array('license_key' => 'KEY', 'ignore_site' => '0')), 'KEY', 0),
            array(array(array('license_key' => 'low-random-ignore-site-1', 'ignore_site' => '1')), '', 1),
        ) as $vector) {
            $this->ee->db->results['eeharbor_licenses'] = $vector[0];
            $result = $controller->license();
            $this->assertSame($vector[1], $view->variables['license_key']);
            $this->assertSame($vector[2], $view->variables['ignore_site']);
            $this->assertSame('addons/settings/low_random/save_license', $view->variables['action_url']->path);
            $this->assertSame('7', $view->variables['ee_ver']);
            $this->assertSame('<rendered-view>', $result['body']);
        }
    }

    /**
     * Valid license input is normalized and inserted before the success redirect.
     *
     * @return void
     */
    public function testSaveNewLicense()
    {
        $controller = $this->controller();
        $this->ee->input->post = array('license_key' => ' AB_123 ', 'ignore_site' => '0');
        $this->redirected(static function () use ($controller) { $controller->save_license(); });
        $this->assertContains(array('insert', array('eeharbor_licenses', array('site_id' => 1, 'addon' => 'Low\\Random', 'license_key' => 'AB123', 'ignore_site' => 0))), $this->ee->db->calls);
        $alert = $GLOBALS['low_random_services']['CP/Alert']->created[0][2];
        $this->assertSame(array('asSuccess', array()), $alert->calls[0]);
        $this->assertContains(array('withTitle', array('License Key Updated')), $alert->calls);
    }

    /**
     * The exact fifty-character key limit remains a successful save boundary.
     *
     * @return void
     */
    public function testMaximumLengthLicenseKey()
    {
        $controller = $this->controller();
        $key = str_repeat('A', 50);
        $this->ee->input->post = array('license_key' => $key);
        $this->redirected(static function () use ($controller) { $controller->save_license(); });
        $writes = array_values(array_filter($this->ee->db->calls, static function ($call) { return $call[0] === 'insert'; }));
        $this->assertSame($key, $writes[0][1][1]['license_key']);
    }

    /**
     * An existing key owned by this site and add-on updates its scoped record.
     *
     * @return void
     */
    public function testSaveExistingLicense()
    {
        $controller = $this->controller();
        $this->ee->input->post = array('license_key' => 'KEY');
        $this->ee->db->results['eeharbor_licenses'] = array(array('site_id' => 1, 'addon' => 'Low\\Random', 'license_key' => 'KEY', 'ignore_site' => 0));
        $this->redirected(static function () use ($controller) { $controller->save_license(); });
        $this->assertContains(array('update', array('eeharbor_licenses', array('site_id' => 1, 'addon' => 'Low\\Random', 'license_key' => 'KEY', 'ignore_site' => 0))), $this->ee->db->calls);
        $this->assertScopedMutation('update');
        $this->assertSame(array('asSuccess', array()), $GLOBALS['low_random_services']['CP/Alert']->created[0][2]->calls[0]);
    }

    /**
     * Ignore-site mode replaces caller key text with the site's canonical marker.
     *
     * @return void
     */
    public function testSaveIgnoredSite()
    {
        $controller = $this->controller();
        $this->ee->input->post = array('license_key' => 'discarded', 'ignore_site' => '1');
        $this->redirected(static function () use ($controller) { $controller->save_license(); });
        $this->assertContains(array('insert', array('eeharbor_licenses', array('site_id' => 1, 'addon' => 'Low\\Random', 'license_key' => 'low-random-ignore-site-1', 'ignore_site' => 1))), $this->ee->db->calls);
    }

    /**
     * Oversized keys stop with a visible error before any license DML occurs.
     *
     * @return void
     */
    public function testOversizedLicenseGuard()
    {
        $controller = $this->controller();
        $this->ee->input->post = array('license_key' => str_repeat('A', 51));
        $this->redirected(static function () use ($controller) { $controller->save_license(); });
        $this->assertSame(array(), array_values(array_filter($this->ee->db->calls, static function ($call) { return in_array($call[0], array('insert', 'update', 'delete'), true); })));
        $alert = $GLOBALS['low_random_services']['CP/Alert']->created[0][2];
        $this->assertSame(array('asIssue', array()), $alert->calls[0]);
        $this->assertContains(array('withTitle', array('License Key Too Long')), $alert->calls);
    }

    /**
     * Keys already assigned to a different MSM site do not modify licensing rows.
     *
     * @return void
     */
    public function testOtherSiteLicenseGuard()
    {
        $controller = $this->controller();
        $this->ee->input->post = array('license_key' => 'KEY');
        $this->ee->db->results['eeharbor_licenses'] = array(array('site_id' => 2, 'addon' => 'Low\\Random', 'license_key' => 'KEY'));
        $this->ee->db->results['sites'] = array(array('site_label' => 'Other site'));
        $this->redirected(static function () use ($controller) { $controller->save_license(); });
        $alert = $GLOBALS['low_random_services']['CP/Alert']->created[0][2];
        $this->assertStringContainsString('Other site', $alert->calls[1][1][0]);
        $this->assertSame(array(), array_values(array_filter($this->ee->db->calls, static function ($call) { return in_array($call[0], array('insert', 'update', 'delete'), true); })));
    }

    /**
     * A key assigned to another add-on is rejected without licensing writes.
     *
     * @return void
     */
    public function testOtherAddonLicenseGuard()
    {
        $controller = $this->controller();
        $this->ee->input->post = array('license_key' => 'KEY');
        $this->ee->db->results['eeharbor_licenses'] = array(array('site_id' => 1, 'addon' => 'EEHarbor\\OtherAddon', 'license_key' => 'KEY'));
        $this->redirected(static function () use ($controller) { $controller->save_license(); });
        $alert = $GLOBALS['low_random_services']['CP/Alert']->created[0][2];
        $this->assertStringContainsString('another installed add-on', $alert->calls[1][1][0]);
        $this->assertSame(array(), array_values(array_filter($this->ee->db->calls, static function ($call) { return in_array($call[0], array('insert', 'update', 'delete'), true); })));
    }

    /**
     * Clearing an ignored site's key deletes its marker and redirects immediately.
     *
     * @return void
     */
    public function testClearingIgnoredLicenseDeletesTheMarker()
    {
        $controller = $this->controller();
        $record = array('site_id' => 1, 'addon' => 'Low\\Random', 'license_key' => 'low-random-ignore-site-1', 'ignore_site' => 1);
        $this->ee->db->results['eeharbor_licenses'] = array($record);
        $this->ee->db->getQueues['eeharbor_licenses'] = array(array(), array($record));
        $this->ee->input->post = array('license_key' => '', 'ignore_site' => '0');
        $this->redirected(static function () use ($controller) { $controller->save_license(); });
        $this->assertContains(array('delete', array('eeharbor_licenses')), $this->ee->db->calls);
        $this->assertScopedMutation('delete');
        $this->assertSame(array(), array_values(array_filter($this->ee->db->calls, static function ($call) { return $call[0] === 'insert' || $call[0] === 'update'; })));
    }

    /**
     * Numeric SQL strings for the same site should not trigger the other-site guard.
     *
     * @return void
     */
    public function testNumericStringSiteIdMatchesTheCurrentSite()
    {
        $controller = $this->controller();
        $this->ee->input->post = array('license_key' => 'KEY');
        $this->ee->db->results['eeharbor_licenses'] = array(array('site_id' => '1', 'addon' => 'Low\\Random', 'license_key' => 'KEY', 'ignore_site' => 0));
        $this->redirected(static function () use ($controller) { $controller->save_license(); });
        $alert = $GLOBALS['low_random_services']['CP/Alert']->created[0][2];
        $this->assertSame(array('asSuccess', array()), $alert->calls[0]);
    }

    /**
     * Pagination counts a cloned query and modifies only the original page query.
     *
     * @return void
     */
    public function testPaginatedModelAndExplicitUrl()
    {
        $this->controller();
        $controller = new class extends Mcp {
            /**
             * Model a real controller action for automatic return-URL inference.
             *
             * @param PagerModel $model
             * @param string|null $url
             * @return PagerModel
             */
            public function listing(PagerModel $model, $url = null)
            {
                return $this->paginateModel($model, $url, 10, 3, 'p');
            }
        };
        $pagination = new Pagination();
        $totals = array();
        $GLOBALS['low_random_services']['CP/Pagination'] = static function ($total) use ($pagination, &$totals) {
            $totals[] = $total;
            return $pagination;
        };
        $model = new PagerModel();
        $model->rows = range(1, 21);
        $this->ee->input->get = array('p' => '2');
        $this->assertSame($model, $controller->listing($model, '/explicit'));
        $this->assertSame(array(array('limit', 10), array('offset', 10)), $model->calls);
        $this->assertSame(array(21), $totals);
        $this->assertSame('<pagination>', $controller->data['pagination']);
        $this->assertSame(array('render', array('/explicit')), end($pagination->calls));
        $this->ee->input->get = array();
        $this->assertSame($model, $controller->listing($model));
        $render = end($pagination->calls);
        $this->assertSame('addons/settings/low_random/listing', $render[1][0]->path);
    }
}
