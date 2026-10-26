<?php

namespace Tests\Support;

final class LegacySession
{
    public $cache = array();
    public $userdata = array();
    public $flashes = array();

    /**
     * Preserve EE Session's isset/default contract, including zero values.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function userdata($name, $default = false)
    {
        return isset($this->userdata[$name]) ? $this->userdata[$name] : $default;
    }

    /**
     * Record the exact legacy flash key and combined message.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function set_flashdata($name, $value)
    {
        $this->flashes[] = array($name, $value);
    }
}

final class LegacyViewLoader
{
    public $calls = array();

    /**
     * Observe the view boundary without rendering unrelated EE internals.
     *
     * @param string $view
     * @param array $variables
     * @param bool $return
     * @return array
     */
    public function view($view, array $variables, $return)
    {
        $result = array($view, $variables, $return);
        $this->calls[] = $result;
        return $result;
    }
}
