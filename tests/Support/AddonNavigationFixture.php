<?php

namespace Low\Random\Conduit;

final class McpNav extends \Low\Random\FluxCapacitor\Conduit\McpNav
{
    public $events = array();

    /**
     * Supply a custom add-on's navigation configuration through its extension point.
     *
     * @return array
     */
    protected function defaultItems()
    {
        return $GLOBALS['low_random_nav_options']['items'];
    }

    /**
     * Supply optional add-on buttons without replacing base generation logic.
     *
     * @return array
     */
    protected function defaultButtons()
    {
        return $GLOBALS['low_random_nav_options']['buttons'];
    }

    /**
     * Supply active-route aliases using the add-on's normal customization boundary.
     *
     * @return array
     */
    protected function defaultActiveMap()
    {
        return $GLOBALS['low_random_nav_options']['map'];
    }

    /**
     * Let a controller defer rendering while preserving base initialization.
     *
     * @return bool
     */
    public function deferGenerate()
    {
        return $GLOBALS['low_random_nav_options']['defer'];
    }

    /**
     * Observe the documented hook before default configuration is loaded.
     *
     * @return void
     */
    public function preGenerateNav()
    {
        $this->events[] = 'pre';
    }

    /**
     * Observe the documented hook after automatic generation has completed.
     *
     * @return void
     */
    public function postGenerateNav()
    {
        $this->events[] = 'post';
    }
}
