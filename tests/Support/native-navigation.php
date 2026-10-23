<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);

$root = dirname(__DIR__, 2);
$options = json_decode($argv[1], true, 512, JSON_THROW_ON_ERROR);
$_SERVER['LOW_RANDOM_NATIVE_CASE'] = 'navigation-component/' . $options['name'];
require __DIR__ . '/coverage.php';
$installation = sys_get_temp_dir() . '/navigation-installation-' . getmypid() . '-' . bin2hex(random_bytes(5)) . '/';
mkdir($installation . 'low_random/Conduit', 0700, true);

foreach (array('addon.setup.php', 'addon.json', 'autoload.php') as $file) {
    $source = $root . '/low_random/' . $file;
    $copy = $installation . 'low_random/' . $file;
    copy($source, $copy);

    if (hash_file('sha256', $source) !== hash_file('sha256', $copy)) {
        throw new RuntimeException('Installation fixture source copy changed: ' . $file);
    }
}

copy(__DIR__ . '/AddonNavigationFixture.php', $installation . 'low_random/Conduit/McpNav.php');
define('PATH_THIRD', $installation);
define('BASEPATH', $root . '/tests/');
define('APP_VER', $options['ee']);
define('REQ', 'PAGE');
define('BASE', 'https://example.test/cp?S=0');
define('AMP', '&amp;');
require __DIR__ . '/Environment.php';
require __DIR__ . '/ControlPanelServices.php';
require __DIR__ . '/NavigationFixture.php';

$GLOBALS['low_random_ee'] = (object) array(
    'config' => new \Tests\Support\Configuration(),
    'db' => new \Tests\Support\Database(),
    'load' => new \Tests\Support\Loader(),
    'view' => (object) array('cp_page_title' => 'Original'),
);
$GLOBALS['low_random_services'] = array('CP/URL' => new \Tests\Support\UrlFactory(), 'CP/Sidebar' => new \Tests\Support\NavigationSidebar());
$GLOBALS['low_random_ee']->uri = new class($options['last']) {
    private $last;

    /**
     * Retain the requested native URI segment for the component context.
     *
     * @param string $last
     * @return void
     */
    public function __construct($last)
    {
        $this->last = $last;
    }

    /**
     * Return the same one-based segment collection provided by EE URI.
     *
     * @return array
     */
    public function segment_array()
    {
        return array(1 => 'cp', 2 => 'addons', 3 => $this->last);
    }
};
$GLOBALS['low_random_ee']->cp = new class {
    public $breadcrumbs = array();
    public $rightNav = array();

    /**
     * Preserve breadcrumb values supplied by the real navigation component.
     *
     * @param mixed $url
     * @param string $title
     * @return void
     */
    public function set_breadcrumb($url, $title)
    {
        $this->breadcrumbs[] = array($url, $title);
    }

    /**
     * Record the legacy title-to-URL navigation mapping without rendering it.
     *
     * @param array $items
     * @return void
     */
    public function set_right_nav(array $items)
    {
        $this->rightNav = $items;
    }
};

// A fixture add-on supplies its optional navigation file; all library classes
// load from the original, unchanged PHAR through this component loader.
spl_autoload_register(static function ($class) use ($root) {
    $prefix = 'Low\\Random\\FluxCapacitor\\';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $path = 'phar://' . $root . '/low_random/low_random.phar/FluxCapacitor/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (!file_exists($path)) {
        throw new RuntimeException('Unknown library class: ' . $class);
    }

    require $path;
});
$GLOBALS['low_random_nav_options'] = $options;
require $installation . 'low_random/Conduit/McpNav.php';
$navigation = $options['defaults'] ? new class extends \Low\Random\FluxCapacitor\Conduit\McpNav {} : new \Low\Random\Conduit\McpNav();
$before = array('sidebar' => $navigation->sidebar, 'items' => $navigation->nav_items, 'events' => $navigation instanceof \Low\Random\Conduit\McpNav ? $navigation->events : array());

if ($options['defer']) {
    $navigation->generateNav();
}

$navigation->setToolbarIcon();
$defaultToolbar = $GLOBALS['low_random_ee']->view->header;
$navigation->setToolbarIcon('Custom', 'https://example.test/help', 'help');
$active = $navigation->getActiveItem();
echo json_encode(array(
    'archive_sha256' => hash_file('sha256', $root . '/low_random/low_random.phar'),
    'before' => $before,
    'events' => $navigation instanceof \Low\Random\Conduit\McpNav ? $navigation->events : array(),
    'headers' => $GLOBALS['low_random_services']['CP/Sidebar']->headers,
    'items' => $navigation->nav_items,
    'active' => $active,
    'title' => $GLOBALS['low_random_ee']->view->cp_page_title,
    'breadcrumbs' => $GLOBALS['low_random_ee']->cp->breadcrumbs,
    'rightNav' => $GLOBALS['low_random_ee']->cp->rightNav,
    'urls' => array('root' => $navigation->getUrl('/'), 'external' => $navigation->getUrl('https://example.test/help')),
    'defaultToolbar' => $defaultToolbar,
    'toolbar' => $GLOBALS['low_random_ee']->view->header,
), JSON_THROW_ON_ERROR);
