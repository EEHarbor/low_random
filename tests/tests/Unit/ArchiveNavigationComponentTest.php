<?php

namespace Tests;

final class ArchiveNavigationComponentTest extends TestCase
{
    /**
     * Exercise original archive methods with a real optional add-on extension file.
     *
     * @dataProvider navigationContexts
     * @param array $options
     * @param array $expected
     * @return void
     */
    public function testNativeNavigationComponent(array $options, array $expected)
    {
        $result = $this->native(array(dirname(__DIR__, 2) . '/Support/native-navigation.php', json_encode($options, JSON_THROW_ON_ERROR)));
        $this->assertSame(0, $result[0], $result[1] . $result[2]);
        $this->assertSame('', $result[2]);
        $data = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(hash_file('sha256', dirname(__DIR__, 3) . '/low_random/low_random.phar'), $data['archive_sha256']);
        $this->assertSame($expected['title'], $data['title']);
        $this->assertSame($options['defaults'] ? array() : ($options['defer'] ? array('pre') : array('pre', 'post')), $data['events']);
        $this->assertSame('https://example.test/help', $data['urls']['external']);
        $this->assertSame('Custom', $data['toolbar']['title']);
        $this->assertSame(array('help' => array('href' => 'https://example.test/help', 'title' => 'Custom')), $data['toolbar']['toolbar_items']);
        $this->assertSame('Low Random', $data['defaultToolbar']['title']);

        if ($options['defer']) {
            $this->assertNull($data['before']['sidebar']);
            $this->assertNull($data['before']['items']);
            $this->assertSame(array('pre'), $data['before']['events']);
        }

        if ($options['ee'] === '2.5.5') {
            $base = 'https://example.test/cp?S=0&amp;C=addons_modules&amp;M=show_module_cp&amp;module=low_random&amp;method=';
            $this->assertSame(array(
                'Overview' => $base . 'index&amp;D=cp', 'Reports' => $base . 'reports&amp;D=cp',
                'Docs' => 'https://example.test/docs', 'License' => $base . 'license&amp;D=cp',
            ), $data['rightNav']);
            $this->assertSame($base . 'index&amp;D=cp', $data['urls']['root']);
            $this->assertSame($base . 'settings&amp;D=cp', $data['defaultToolbar']['toolbar_items']['settings']['href']);
            $this->assertSame(array(), $data['headers']);
            $this->assertNull($data['items']);
            $this->assertNull($data['active']);
            $this->assertSame(array(), $data['breadcrumbs']);
            return;
        }

        $this->assertSame(array(), $data['rightNav']);
        $this->assertSame($expected['keys'], array_keys($data['items']));
        $this->assertCount(count($expected['keys']), $data['headers']);
        $this->assertSame('addons/settings/low_random/index', $data['urls']['root']['path']);
        $this->assertSame('addons/settings/low_random/settings', $data['defaultToolbar']['toolbar_items']['settings']['href']['path']);

        foreach ($data['items'] as $method => $header) {
            $this->assertSame($method === $expected['active'], $header['active']);

            if (strpos($method, 'HEADING') === 0) {
                $this->assertNull($header['url']);
                continue;
            }

            if (strpos($method, 'https://') === 0) {
                $this->assertSame($method, $header['url']);
                continue;
            }

            $this->assertSame('addons/settings/low_random/' . $method, $header['url']['path']);
        }

        if ($expected['active'] === null) {
            $this->assertNull($data['active']);
            $this->assertSame(array(), $data['breadcrumbs']);
            return;
        }

        $this->assertSame($data['items'][$expected['active']], $data['active']);
        $this->assertCount(1, $data['breadcrumbs']);
        $this->assertSame('Low Random', $data['breadcrumbs'][0][1]);
        $this->assertSame('addons/settings/low_random/index', $data['breadcrumbs'][0][0]['path']);

        if ($options['name'] === 'direct') {
            $this->assertSame('Import', $data['items']['reports']['button']['text']);
            $this->assertSame('addons/settings/low_random/import', $data['items']['reports']['button']['url']['path']);
        }
    }

    /**
     * Cover default hooks, aliases, headings, buttons, deferred rendering and EE2.
     *
     * @return array
     */
    public static function navigationContexts()
    {
        $base = array('name' => 'default', 'ee' => '7.5.0', 'last' => 'license', 'items' => array(), 'buttons' => array(), 'map' => array(), 'defer' => false, 'defaults' => false);
        $cases = array();
        $input = $base;
        $input['defaults'] = true;
        $cases['base defaults'] = array($input, array('keys' => array('license'), 'active' => 'license', 'title' => 'License'));
        $input = $base;
        $input['name'] = 'direct';
        $input['last'] = 'reports';
        $input['items'] = array('/' => 'Overview', 'reports' => 'Reports', 'HEADING-group' => 'Group', 'https://example.test/docs' => 'Docs');
        $input['buttons'] = array('reports' => array('new' => 'New', 'import' => 'Import'));
        $input['map'] = array('save_report' => 'reports');
        $cases['direct heading and buttons'] = array($input, array('keys' => array('index', 'reports', 'HEADING-group', 'https://example.test/docs', 'license'), 'active' => 'reports', 'title' => 'Reports'));

        foreach (array('', '/') as $alias) {
            $input = $base;
            $input['name'] = $alias === '' ? 'empty-alias' : 'slash-alias';
            $input['last'] = 'edit';
            $input['items'] = array($alias => 'Overview');
            $input['map'] = array('edit' => $alias, 'low_random' => 'license');
            $cases[$input['name']] = array($input, array('keys' => array('index', 'license'), 'active' => 'index', 'title' => 'Overview'));
        }

        $input = $base;
        $input['name'] = 'shortname';
        $input['last'] = 'low_random';
        $input['items'] = array('/' => 'Overview');
        $cases['automatic shortname alias'] = array($input, array('keys' => array('index', 'license'), 'active' => 'index', 'title' => 'Overview'));
        $input = $base;
        $input['name'] = 'deferred';
        $input['last'] = 'save_report';
        $input['items'] = array('reports' => 'Reports');
        $input['map'] = array('save_report' => 'reports');
        $input['defer'] = true;
        $cases['deferred generation'] = array($input, array('keys' => array('reports', 'license'), 'active' => 'reports', 'title' => 'Reports'));
        $input = $base;
        $input['name'] = 'unmatched';
        $input['last'] = 'unknown';
        $input['items'] = array('HEADING-group' => 'Group', 'reports' => 'Reports');
        $input['map'] = array('unrelated' => 'reports');
        $cases['unmatched request'] = array($input, array('keys' => array('HEADING-group', 'reports', 'license'), 'active' => null, 'title' => 'Original'));
        $input = $base;
        $input['name'] = 'existing-license';
        $input['items'] = array('license' => 'Custom License');
        $cases['existing license menu'] = array($input, array('keys' => array('license'), 'active' => 'license', 'title' => 'Custom License'));
        $input = $base;
        $input['name'] = 'empty-button';
        $input['last'] = 'reports';
        $input['items'] = array('reports' => 'Reports');
        $input['buttons'] = array('reports' => array());
        $cases['empty button group'] = array($input, array('keys' => array('reports', 'license'), 'active' => 'reports', 'title' => 'Reports'));
        $input = $base;
        $input['name'] = 'legacy';
        $input['ee'] = '2.5.5';
        $input['last'] = 'reports';
        $input['items'] = array('/' => 'Overview', 'reports' => 'Reports', 'https://example.test/docs' => 'Docs');
        $input['buttons'] = array('reports' => array('new' => 'New'));
        $cases['EE2 delegation'] = array($input, array('title' => 'Original'));
        return $cases;
    }
}
