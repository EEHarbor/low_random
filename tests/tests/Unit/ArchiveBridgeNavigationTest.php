<?php

namespace Tests;

use Tests\Support\UrlFactory;
use Tests\Support\NavigationSidebar;

final class ArchiveBridgeNavigationTest extends TestCase
{
    /**
     * Initialize only the EE services consumed by real bridge navigation.
     *
     * @param string $version
     * @param string $lastSegment
     * @return object
     */
    private function bridge($version, $lastSegment)
    {
        require_once dirname(__DIR__, 2) . '/Support/NavigationFixture.php';
        $GLOBALS['low_random_services']['CP/URL'] = new UrlFactory();
        $GLOBALS['low_random_services']['CP/Sidebar'] = new NavigationSidebar();
        $this->ee->view = (object) array('cp_page_title' => 'Original');
        $this->ee->uri = new class($lastSegment) {
            private $lastSegment;

            /**
             * Retain the requested final segment without shared request globals.
             *
             * @param string $lastSegment
             * @return void
             */
            public function __construct($lastSegment)
            {
                $this->lastSegment = $lastSegment;
            }

            /**
             * Match URI's segment-array shape used by end().
             *
             * @return array
             */
            public function segment_array()
            {
                return array(1 => 'cp', 2 => 'addons', 3 => $this->lastSegment);
            }
        };
        $class = 'Low\\Random\\FluxCapacitor\\Bridge\\' . $version;
        return new $class(array('module' => 'low_random', 'module_name' => 'Low Random'));
    }

    /**
     * Each shipped implementation receives independent assertions and coverage.
     *
     * @return array
     */
    public static function versions()
    {
        return array(array('EE3'), array('EE4'), array('EE5'));
    }

    /**
     * Empty and unmatched menus preserve the caller's page title and breadcrumbs.
     *
     * @dataProvider versions
     * @param string $version
     * @return void
     */
    public function testUnmatchedMenusPreservePageState($version)
    {
        $bridge = $this->bridge($version, 'unknown');
        $this->assertNull($bridge->getNav());
        $this->assertSame(array(), $GLOBALS['low_random_services']['CP/Sidebar']->headers);
        $this->assertNull($bridge->getNav(array('license' => 'Licensing'), array(), array('unknown' => 'missing')));
        $this->assertSame('Original', $this->ee->view->cp_page_title);
        $this->assertSame(array(), $this->ee->cp->breadcrumbs);
        $this->assertFalse($GLOBALS['low_random_services']['CP/Sidebar']->headers[0]->active);
    }

    /**
     * Root aliases and direct index routes activate the index header by title.
     *
     * @dataProvider versions
     * @param string $version
     * @return void
     */
    public function testRootAndIndexActivation($version)
    {
        foreach (array('low_random', 'index') as $segment) {
            $bridge = $this->bridge($version, $segment);
            $this->assertNull($bridge->getNav(array('/' => 'Overview')));
            $header = $GLOBALS['low_random_services']['CP/Sidebar']->headers[0];
            $this->assertTrue($header->active);
            $this->assertSame('addons/settings/low_random/index', $header->url->path);
            $this->assertSame('Overview', $this->ee->view->cp_page_title);
            $this->assertCount(1, $this->ee->cp->breadcrumbs);
            $this->assertSame('Low\\Random', $this->ee->cp->breadcrumbs[0][1]);
            $this->ee->cp->breadcrumbs = array();
        }
    }

    /**
     * Internal routes, external URLs and optional buttons keep their exact values.
     *
     * @dataProvider versions
     * @param string $version
     * @return void
     */
    public function testRoutesAndButtonArguments($version)
    {
        $bridge = $this->bridge($version, 'license');
        $this->assertNull($bridge->getNav(
            array('license' => 'Licensing', 'https://example.test/help' => 'Help'),
            array('license' => array('new' => 'New', 'import' => 'Import'))
        ));
        $headers = $GLOBALS['low_random_services']['CP/Sidebar']->headers;
        $this->assertCount(2, $headers);
        $this->assertSame('Licensing', $headers[0]->title);
        $this->assertSame('addons/settings/low_random/license', $headers[0]->url->path);
        $this->assertSame('Import', $headers[0]->button['text']);
        $this->assertSame('addons/settings/low_random/import', $headers[0]->button['url']->path);
        $this->assertTrue($headers[0]->active);
        $this->assertSame('https://example.test/help', $headers[1]->url);
        $this->assertFalse($headers[1]->active);
        $this->assertNull($headers[1]->button);
        $this->assertSame('Licensing', $this->ee->view->cp_page_title);
    }

    /**
     * An active-map alias must assign the header title rather than the Header object.
     *
     * @dataProvider versions
     * @param string $version
     * @return void
     */
    public function testActiveMapPreservesStringPageTitle($version)
    {
        $bridge = $this->bridge($version, 'save_license');
        $this->assertNull($bridge->getNav(array('license' => 'Licensing'), array(), array('save_license' => 'license')));
        $this->assertTrue($GLOBALS['low_random_services']['CP/Sidebar']->headers[0]->active);
        $this->assertSame('Licensing', $this->ee->view->cp_page_title);
    }

    /**
     * Root aliases use the same normalized index key as sidebar registration.
     *
     * @dataProvider versions
     * @param string $version Bridge class suffix.
     * @return void
     */
    public function testRootAliasPreservesItsNormalizedPageTitle($version)
    {
        $bridge = $this->bridge($version, 'child');
        $this->assertNull($bridge->getNav(array('/' => 'Overview'), array(), array('child' => 'index')));
        $this->assertTrue($GLOBALS['low_random_services']['CP/Sidebar']->headers[0]->active);
        $this->assertSame('Overview', $this->ee->view->cp_page_title);
    }
    /**
     * Canonicalize mapped root routes and retain meaningful zero-valued labels.
     *
     * @dataProvider versions
     * @param string $version Bridge class suffix.
     * @return void
     */
    public function testMappedRootAliasAndZeroTitlesUseTheSameHeader($version): void
    {
        foreach (array('detail', 'low_random') as $segment) {
            foreach (array('Overview', '0') as $title) {
                $bridge = $this->bridge($version, $segment);
                $this->ee->cp->breadcrumbs = array();
                $bridge->getNav(array('/' => $title), array(), array('detail' => '/'));
                $header = $GLOBALS['low_random_services']['CP/Sidebar']->headers[0];
                $this->assertTrue($header->active);
                $this->assertSame('addons/settings/low_random/index', $header->url->path);
                $this->assertSame($title, $this->ee->view->cp_page_title);
                $this->assertCount(1, $this->ee->cp->breadcrumbs);
            }
        }
    }

}
