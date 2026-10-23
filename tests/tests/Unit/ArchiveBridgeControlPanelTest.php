<?php

namespace Tests;

use Tests\Support\AlertFactory;
use Tests\Support\CacheStore;
use Tests\Support\Pagination;
use Tests\Support\RequestInput;
use Tests\Support\SessionCache;
use Tests\Support\UrlFactory;
use Tests\Support\ViewService;

final class ArchiveBridgeControlPanelTest extends TestCase
{
    /**
     * Build the separately shipped modern bridge implementations unchanged.
     *
     * @return array
     */
    private function bridges()
    {
        $result = array();

        foreach (array('EE3', 'EE4', 'EE5') as $version) {
            $class = 'Low\\Random\\FluxCapacitor\\Bridge\\' . $version;
            $result[] = new $class(array('module' => 'low_random', 'module_name' => 'Low Random'));
        }

        return $result;
    }

    /**
     * Preserve slash/index routing and separate query variables from path IDs.
     *
     * @return void
     */
    public function testUrlRoutingAndPublishIds()
    {
        foreach ($this->bridges() as $bridge) {
            $factory = new UrlFactory();
            $GLOBALS['low_random_services']['CP/URL'] = $factory;
            $this->assertSame('addons/settings/low_random', $bridge->getBaseURL()->path);
            $this->assertSame('addons/settings/low_random', $bridge->getBaseURL('/')->path);
            $this->assertSame('addons/settings/low_random/license/extra', $bridge->getBaseURL('license', '/extra')->path);
            $this->assertSame('addons/settings/low_random/index', $bridge->moduleURL()->path);
            $url = $bridge->moduleURL('license', array('page' => 2));
            $this->assertSame(array('page' => 2), $url->variables);
            $this->assertSame('publish', $bridge->cpURL('listing')->path);
            $url = $bridge->cpURL('publish', 'create', array('channel_id' => 4, 'preview' => 'yes'));
            $this->assertSame('publish/create/4', $url->path);
            $this->assertSame(array('preview' => 'yes'), $url->variables);
            $url = $bridge->cpURL('publish', 'edit', array('entry_id' => 7, 'preview' => 'no'));
            $this->assertSame('publish/edit/entry/7', $url->path);
            $this->assertSame(array('preview' => 'no'), $url->variables);
            $this->assertSame('publish/edit', $bridge->cpURL('publish', 'edit')->path);
            $this->assertSame('members/list', $bridge->cpURL('members', 'list')->path);
        }
    }

    /**
     * Views receive required global variables while preserving caller overrides.
     *
     * @return void
     */
    public function testViewLookupAndStringRendering()
    {
        foreach ($this->bridges() as $bridge) {
            $GLOBALS['low_random_services']['CP/URL'] = new UrlFactory();
            $view = new ViewService();
            $GLOBALS['low_random_services']['View'] = $view;
            $this->ee->view = (object) array('cp_page_title' => 'Current heading');
            $result = $bridge->view('license', array('custom' => 0), true);
            $this->assertSame(array(array('make', 'low_random:license')), $view->calls);
            $this->assertSame('7', $view->variables['ee_ver']);
            $this->assertSame('Current heading', $view->variables['cp_page_title']);
            $this->assertSame(0, $view->variables['custom']);
            $this->assertSame('addons/settings/low_random', $view->variables['base_url']->path);
            $this->assertSame(array('heading' => 'Current heading', 'breadcrumb' => array('/cp/addons/settings/low_random/' => 'Low Random'), 'body' => '<rendered-view>'), $result);
            $result = $bridge->view('ignored', array('base_url' => '/custom', 'cp_page_title' => 'Override'), false, '<p>inline</p>');
            $this->assertSame(array('makeFromString', '<p>inline</p>'), end($view->calls));
            $this->assertSame('/custom', $view->variables['base_url']);
            $this->assertSame('Override', $view->variables['cp_page_title']);
            $this->assertSame('Current heading', $result['heading']);
        }
    }

    /**
     * Pagination input honors per-page, page and absent/zero/null fallback order.
     *
     * @return void
     */
    public function testCurrentPageInputPriority()
    {
        foreach ($this->bridges() as $bridge) {
            $this->ee->input = new RequestInput();

            foreach (array(
                array(array(), 1), array(array('page' => '2'), '2'),
                array(array('per_page' => '3', 'page' => '2'), '3'),
                array(array('per_page' => '0', 'page' => '2'), '2'),
                array(array('per_page' => null, 'page' => 0), 1),
            ) as $vector) {
                $this->ee->input->get = $vector[0];
                $this->assertSame($vector[1], $bridge->getCurrentPage());
            }
        }
    }

    /**
     * Persistent and request cache modes preserve values and collaborator returns.
     *
     * @return void
     */
    public function testCacheReadWriteAndPersistentDelete()
    {
        foreach ($this->bridges() as $bridge) {
            $this->ee->cache = new CacheStore();
            $this->ee->session = new SessionCache();
            $this->assertFalse($bridge->cache('get', 'missing'));
            $this->assertTrue($bridge->cache('set', 'zero', 0));
            $this->assertSame(0, $bridge->cache('get', 'zero'));
            $this->assertSame($this->ee->session, $bridge->cache('set', 'request', array('value'), false));
            $this->assertSame(array('value'), $bridge->cache('get', 'request', false, false));
            $this->assertFalse($bridge->cache('get', 'missing', false, false));
            $this->assertTrue($bridge->cache('delete', 'zero'));
            $this->assertFalse($bridge->cache('get', 'zero'));
            $this->assertTrue($bridge->cache('set', 'second', 'value'));
            $this->assertTrue($bridge->cache('clear', 'second'));
            $this->assertFalse($bridge->cache('get', 'second'));
            $this->assertFalse($bridge->cache('unsupported', 'key'));
            $this->assertSame(array('delete', '/low_random/second'), $this->ee->cache->calls[count($this->ee->cache->calls) - 2]);
        }
    }

    /**
     * The nonpersistent delete mode must clear the same tier used by get/set.
     *
     * @return void
     */
    public function testRequestCacheDeleteTargetsTheRequestTier()
    {
        $bridge = $this->bridges()[0];
        $this->ee->cache = new CacheStore();
        $this->ee->session = new SessionCache();
        $bridge->cache('set', 'key', 'request-value', false);
        $bridge->cache('delete', 'key', false, false);
        $this->assertFalse($bridge->cache('get', 'key', false, false));
    }

    /**
     * Configuration fallback, explicit paths and footer writes preserve boundaries.
     *
     * @return void
     */
    public function testThemeCacheAndFooterDelegation()
    {
        foreach ($this->bridges() as $bridge) {
            $this->ee->config->items['cache_path'] = '';
            $this->assertSame(PATH_CACHE, $bridge->getCachePath());
            $this->ee->config->items['cache_path'] = '/explicit/cache/';
            $this->assertSame('/explicit/cache/', $bridge->getCachePath());
            $this->assertSame('https://example.test/themes/user/low_random/', $bridge->getAddonThemesDir());
            $bridge->javascript_to_page('window.value = 0;');
            $this->assertSame('<script type="text/javascript">window.value = 0;</script>', end($this->ee->cp->footer));
            $this->assertSame($this->ee->cp->footer, $bridge->getFooter());
        }
    }
    /**
     * Alerts retain severity, body, closing policy and final deferred delivery.
     *
     * @return void
     */
    public function testAlertSeverityAndOptionalMethods()
    {
        foreach ($this->bridges() as $bridge) {
            foreach (array('message_error' => 'asIssue', 'message_failure' => 'asIssue', 'message_notice' => 'asWarning', 'message_success' => 'asSuccess') as $type => $severity) {
                $factory = new AlertFactory();
                $GLOBALS['low_random_services']['CP/Alert'] = $factory;
                $this->assertNull($bridge->flashData($type, 'Title', 'Body', array('cannotClose', 'missingMethod')));
                $this->assertSame(strpos(get_class($bridge), 'EE3') !== false ? 'make' : 'makeInline', $factory->created[0][0]);
                $this->assertSame(array('Title'), $factory->created[0][1]);
                $this->assertSame(array(
                    array($severity, array()), array('withTitle', array('Title')), array('addToBody', array('Body')),
                    array('canClose', array()), array('cannotClose', array()), array('defer', array()),
                ), $factory->created[0][2]->calls);
            }
        }
    }

    /**
     * Pagination required values and optional overrides reach the native service.
     *
     * @return void
     */
    public function testPaginationOptions()
    {
        foreach ($this->bridges() as $bridge) {
            foreach (array(false, true) as $optional) {
                $pagination = new Pagination();
                $totals = array();
                $GLOBALS['low_random_services']['CP/Pagination'] = static function ($total) use ($pagination, &$totals) {
                    $totals[] = $total;
                    return $pagination;
                };
                $options = array('total_rows' => 21, 'per_page' => 10, 'current_page' => 2, 'base_url' => '/listing');
                $expected = array(array('perPage', array(10)), array('currentPage', array(2)));

                if ($optional) {
                    $options['page_links'] = 3;
                    $options['page_query_string'] = 'p';
                    $expected[] = array('displayPageLinks', array(3));
                    $expected[] = array('queryStringVariable', array('p'));
                }

                $expected[] = array('render', array('/listing'));
                $this->assertSame('<pagination>', $bridge->pagination($options));
                $this->assertSame(array(21), $totals);
                $this->assertSame($expected, $pagination->calls);
            }
        }
    }

    /**
     * Bridge sanitization delegates both scalar and structured inputs unchanged.
     *
     * @return void
     */
    public function testSecurityAndAddonApiDelegation()
    {
        foreach ($this->bridges() as $bridge) {
            $security = new class {
                public $received;
                public $result;

                /**
                 * Expose the sanitizer input and its explicitly configured response.
                 *
                 * @param mixed $value
                 * @return mixed
                 */
                public function clean($value)
                {
                    $this->received = $value;
                    return $this->result;
                }
            };
            $GLOBALS['low_random_services']['Security/XSS'] = $security;

            foreach (array('<p>text</p>', array('value' => '<p>text</p>')) as $input) {
                $security->result = is_array($input) ? array('value' => 'sanitized') : 'sanitized';
                $this->assertSame($security->result, $bridge->xss_clean($input));
                $this->assertSame($input, $security->received);
            }

            $this->ee->legacy_api = new class {
                public $calls = array();

                /**
                 * Record the native legacy-API name without constructing EE internals.
                 *
                 * @param string $name
                 * @return void
                 */
                public function instantiate($name)
                {
                    $this->calls[] = $name;
                }
            };
            $this->assertNull($bridge->instantiate('channel_entries'));
            $this->assertSame(array('channel_entries'), $this->ee->legacy_api->calls);
            $this->ee->addons_model = new class {
                public $installed = false;
                public $requested;

                /**
                 * Preserve the native module-model result for each lookup.
                 *
                 * @param string $name
                 * @return bool
                 */
                public function module_installed($name)
                {
                    $this->requested = $name;
                    return $this->installed;
                }
            };
            $this->assertFalse($bridge->module_installed('missing'));
            $this->assertSame('missing', $this->ee->addons_model->requested);
            $this->ee->addons_model->installed = true;
            $this->assertTrue($bridge->module_installed('installed'));
        }
    }

}
